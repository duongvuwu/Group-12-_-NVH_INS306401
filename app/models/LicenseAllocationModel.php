<?php
declare(strict_types=1);

class LicenseAllocationModel
{
    private $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getUsers(): array
    {
        $query = "SELECT u.id, u.full_name, u.email, u.role, d.name AS department_name
                  FROM users u
                  JOIN departments d ON d.id = u.department_id
                  ORDER BY d.name ASC, u.full_name ASC";

        return $this->conn->query($query)->fetchAll();
    }

    public function getSoftwares(): array
    {
        $query = "SELECT
                    s.id,
                    s.name,
                    s.vendor,
                    COALESCE(SUM(lp.available_quantity), 0) AS available_quantity
                  FROM software_titles s
                  LEFT JOIN license_pools lp ON lp.software_id = s.id
                  GROUP BY s.id, s.name, s.vendor
                  ORDER BY s.name ASC";

        return $this->conn->query($query)->fetchAll();
    }

    public function getAllocations(): array
    {
        $query = "SELECT
                    la.id,
                    la.start_date,
                    la.end_date,
                    la.status,
                    la.created_at,
                    u.full_name,
                    u.email,
                    u.role,
                    d.name AS department_name,
                    s.name AS software_name,
                    s.vendor,
                    lk.key_value,
                    lp.reusable_after_revocation,
                    (SELECT COUNT(*) FROM activation_logs al WHERE al.allocation_id = la.id) AS activation_count,
                    (SELECT GROUP_CONCAT(CONCAT(sa.os_type, ' ', sa.version) ORDER BY sa.os_type SEPARATOR ', ')
                       FROM software_assets sa
                      WHERE sa.software_id = s.id) AS available_assets
                  FROM license_allocations la
                  JOIN users u ON u.id = la.user_id
                  JOIN departments d ON d.id = u.department_id
                  JOIN license_keys lk ON lk.id = la.key_id
                  JOIN license_pools lp ON lp.id = lk.pool_id
                  JOIN software_titles s ON s.id = lp.software_id
                  ORDER BY la.created_at DESC, la.id DESC";

        return $this->conn->query($query)->fetchAll();
    }

    public function requestAllocation(int $userId, int $softwareId, int $durationDays): int
    {
        if ($durationDays < 1 || $durationDays > 1095) {
            throw new InvalidArgumentException('Thời hạn cấp phát phải nằm trong khoảng 1 đến 1095 ngày.');
        }

        $user = $this->getUser($userId);
        if (!$user) {
            throw new InvalidArgumentException('Người dùng không tồn tại.');
        }

        if (!$this->isUserEligible($user, $softwareId)) {
            throw new InvalidArgumentException('Người dùng chưa đủ điều kiện theo luật cấp phát.');
        }

        if ($this->hasActiveSoftwareAllocation($userId, $softwareId)) {
            throw new InvalidArgumentException('Người dùng đang có license active cho phần mềm này.');
        }

        $start = new DateTimeImmutable('now');
        $end = $start->modify('+' . $durationDays . ' days');
        $endDate = $end->format('Y-m-d H:i:s');
        $endDateOnly = $end->format('Y-m-d');

        $this->conn->beginTransaction();
        try {
            if ($this->hasActiveSoftwareAllocation($userId, $softwareId, true)) {
                throw new InvalidArgumentException('Người dùng vừa được cấp license phần mềm này.');
            }

            $key = $this->lockAvailableKey($softwareId, $endDateOnly);
            if (!$key) {
                throw new InvalidArgumentException('Kho không còn key trống phù hợp với thời hạn yêu cầu.');
            }

            $insert = $this->conn->prepare(
                "INSERT INTO license_allocations (user_id, key_id, start_date, end_date, status)
                 VALUES (:user_id, :key_id, :start_date, :end_date, 'Active')"
            );
            $insert->execute([
                ':user_id' => $userId,
                ':key_id' => (int)$key['id'],
                ':start_date' => $start->format('Y-m-d H:i:s'),
                ':end_date' => $endDate,
            ]);
            $allocationId = (int)$this->conn->lastInsertId();

            $updateKey = $this->conn->prepare("UPDATE license_keys SET is_assigned = 1, assigned_at = NOW() WHERE id = :id");
            $updateKey->execute([':id' => (int)$key['id']]);
            $this->syncPoolAvailability((int)$key['pool_id']);

            $this->conn->commit();
            return $allocationId;
        } catch (Throwable $exception) {
            $this->conn->rollBack();
            throw $exception;
        }
    }

    public function activateAllocation(int $allocationId, ?string $ipAddress = null): void
    {
        $allocation = $this->getAllocation($allocationId);
        if (!$allocation || $allocation['status'] !== 'Active') {
            throw new InvalidArgumentException('Chỉ có thể ghi nhận kích hoạt cho license đang active.');
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO activation_logs (allocation_id, activation_time, ip_address)
             VALUES (:allocation_id, NOW(), :ip_address)"
        );
        $stmt->execute([
            ':allocation_id' => $allocationId,
            ':ip_address' => $ipAddress ?: request_ip(),
        ]);
    }

    public function revokeAllocation(int $allocationId, string $reason): void
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('Lý do thu hồi không được để trống.');
        }

        $this->conn->beginTransaction();
        try {
            $allocation = $this->lockAllocation($allocationId);
            if (!$allocation || $allocation['status'] !== 'Active') {
                throw new InvalidArgumentException('Chỉ có thể thu hồi license đang active.');
            }

            $update = $this->conn->prepare("UPDATE license_allocations SET status = 'Revoked' WHERE id = :id");
            $update->execute([':id' => $allocationId]);

            $log = $this->conn->prepare(
                "INSERT INTO revocation_logs (allocation_id, revocation_time, reason)
                 VALUES (:allocation_id, NOW(), :reason)"
            );
            $log->execute([':allocation_id' => $allocationId, ':reason' => $reason]);

            if ((int)$allocation['reusable_after_revocation'] === 1) {
                $this->releaseKey((int)$allocation['key_id'], (int)$allocation['pool_id']);
            }

            $this->conn->commit();
        } catch (Throwable $exception) {
            $this->conn->rollBack();
            throw $exception;
        }
    }

    public function syncExpiredAllocations(): int
    {
        $this->createExpiryNotifications();

        $this->conn->beginTransaction();
        try {
            $query = "SELECT la.id, la.key_id, lk.pool_id, lp.reusable_after_revocation
                      FROM license_allocations la
                      JOIN license_keys lk ON lk.id = la.key_id
                      JOIN license_pools lp ON lp.id = lk.pool_id
                      WHERE la.status = 'Active' AND la.end_date < NOW()
                      FOR UPDATE";
            $rows = $this->conn->query($query)->fetchAll();

            $expire = $this->conn->prepare("UPDATE license_allocations SET status = 'Expired' WHERE id = :id");
            $revoke = $this->conn->prepare(
                "INSERT INTO revocation_logs (allocation_id, revocation_time, reason)
                 VALUES (:allocation_id, NOW(), 'Auto expired')"
            );

            foreach ($rows as $row) {
                $expire->execute([':id' => (int)$row['id']]);
                $revoke->execute([':allocation_id' => (int)$row['id']]);

                if ((int)$row['reusable_after_revocation'] === 1) {
                    $this->releaseKey((int)$row['key_id'], (int)$row['pool_id']);
                }
            }

            $this->conn->commit();
            return count($rows);
        } catch (Throwable $exception) {
            $this->conn->rollBack();
            throw $exception;
        }
    }

    private function getUser(int $id): ?array
    {
        $stmt = $this->conn->prepare(
            "SELECT u.*, d.name AS department_name
             FROM users u
             JOIN departments d ON d.id = u.department_id
             WHERE u.id = :id"
        );
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    private function isUserEligible(array $user, int $softwareId): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM allocation_rules
             WHERE software_id = :software_id
               AND department_id = :department_id
               AND (target_role = 'All' OR target_role = :role)"
        );
        $stmt->execute([
            ':software_id' => $softwareId,
            ':department_id' => (int)$user['department_id'],
            ':role' => $user['role'],
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function hasActiveSoftwareAllocation(int $userId, int $softwareId, bool $forUpdate = false): bool
    {
        $query = "SELECT la.id
                  FROM license_allocations la
                  JOIN license_keys lk ON lk.id = la.key_id
                  JOIN license_pools lp ON lp.id = lk.pool_id
                  WHERE la.user_id = :user_id
                    AND lp.software_id = :software_id
                    AND la.status = 'Active'
                  LIMIT 1" . ($forUpdate ? ' FOR UPDATE' : '');

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':user_id' => $userId, ':software_id' => $softwareId]);

        return (bool)$stmt->fetchColumn();
    }

    private function lockAvailableKey(int $softwareId, string $minimumExpiryDate): ?array
    {
        $query = "SELECT lk.id, lk.pool_id, lk.key_value, lp.expires_at
                  FROM license_keys lk
                  JOIN license_pools lp ON lp.id = lk.pool_id
                  WHERE lp.software_id = :software_id
                    AND lk.is_assigned = 0
                    AND (lp.expires_at IS NULL OR lp.expires_at >= :minimum_expiry)
                  ORDER BY CASE WHEN lp.expires_at IS NULL THEN 1 ELSE 0 END ASC, lp.expires_at ASC, lk.id ASC
                  LIMIT 1
                  FOR UPDATE";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([
            ':software_id' => $softwareId,
            ':minimum_expiry' => $minimumExpiryDate,
        ]);
        $key = $stmt->fetch();

        return $key ?: null;
    }

    private function getAllocation(int $allocationId): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM license_allocations WHERE id = :id");
        $stmt->execute([':id' => $allocationId]);
        $allocation = $stmt->fetch();

        return $allocation ?: null;
    }

    private function lockAllocation(int $allocationId): ?array
    {
        $query = "SELECT la.*, lk.pool_id, lp.reusable_after_revocation
                  FROM license_allocations la
                  JOIN license_keys lk ON lk.id = la.key_id
                  JOIN license_pools lp ON lp.id = lk.pool_id
                  WHERE la.id = :id
                  FOR UPDATE";

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $allocationId]);
        $allocation = $stmt->fetch();

        return $allocation ?: null;
    }

    private function releaseKey(int $keyId, int $poolId): void
    {
        $stmt = $this->conn->prepare("UPDATE license_keys SET is_assigned = 0, assigned_at = NULL WHERE id = :id");
        $stmt->execute([':id' => $keyId]);
        $this->syncPoolAvailability($poolId);
    }

    private function syncPoolAvailability(int $poolId): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE license_pools lp
             SET available_quantity = (
                SELECT COUNT(*) FROM license_keys lk WHERE lk.pool_id = lp.id AND lk.is_assigned = 0
             )
             WHERE lp.id = :id"
        );
        $stmt->execute([':id' => $poolId]);
    }

    private function createExpiryNotifications(): void
    {
        $query = "INSERT IGNORE INTO expiry_notifications (allocation_id, sent_time, alert_type)
                  SELECT la.id, NOW(),
                         CASE
                            WHEN DATEDIFF(la.end_date, NOW()) <= 1 THEN '1_day'
                            ELSE '7_days'
                         END AS alert_type
                  FROM license_allocations la
                  WHERE la.status = 'Active'
                    AND la.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)";
        $this->conn->exec($query);
    }
}
