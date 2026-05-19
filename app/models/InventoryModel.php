<?php
declare(strict_types=1);

class InventoryModel
{
    private $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getSoftwaresForSelect(): array
    {
        return $this->conn->query("SELECT id, name, vendor FROM software_titles ORDER BY name ASC")->fetchAll();
    }

    public function getInventoryOverview(): array
    {
        $query = "SELECT
                    s.id,
                    s.name,
                    s.vendor,
                    (SELECT COALESCE(SUM(lp.total_quantity), 0) FROM license_pools lp WHERE lp.software_id = s.id) AS total_quantity,
                    (SELECT COALESCE(SUM(lp.available_quantity), 0) FROM license_pools lp WHERE lp.software_id = s.id) AS available_quantity,
                    (SELECT COUNT(*) FROM license_pools lp WHERE lp.software_id = s.id) AS pool_count,
                    (SELECT COUNT(*)
                       FROM license_keys lk
                       JOIN license_pools lp ON lp.id = lk.pool_id
                      WHERE lp.software_id = s.id) AS key_count,
                    (SELECT COUNT(*)
                       FROM license_keys lk
                       JOIN license_pools lp ON lp.id = lk.pool_id
                      WHERE lp.software_id = s.id AND lk.is_assigned = 0) AS free_key_count,
                    (SELECT COUNT(*) FROM software_assets sa WHERE sa.software_id = s.id) AS asset_count
                  FROM software_titles s
                  ORDER BY s.name ASC";

        return $this->conn->query($query)->fetchAll();
    }

    public function getPools(): array
    {
        $query = "SELECT
                    lp.*,
                    s.name AS software_name,
                    s.vendor,
                    COUNT(lk.id) AS key_count,
                    COUNT(CASE WHEN lk.is_assigned = 0 THEN 1 END) AS free_key_count
                  FROM license_pools lp
                  JOIN software_titles s ON s.id = lp.software_id
                  LEFT JOIN license_keys lk ON lk.pool_id = lp.id
                  GROUP BY lp.id, s.name, s.vendor
                  ORDER BY lp.purchase_date DESC, lp.id DESC";

        return $this->conn->query($query)->fetchAll();
    }

    public function getAssets(): array
    {
        $query = "SELECT
                    sa.*,
                    s.name AS software_name,
                    s.vendor
                  FROM software_assets sa
                  JOIN software_titles s ON s.id = sa.software_id
                  ORDER BY s.name ASC, sa.os_type ASC, sa.version DESC";

        return $this->conn->query($query)->fetchAll();
    }

    public function addPool(int $softwareId, int $totalQuantity, string $purchaseDate, ?string $expiresAt, bool $reusableAfterRevocation): int
    {
        if ($totalQuantity < 1) {
            throw new InvalidArgumentException('Số lượng license phải lớn hơn 0.');
        }

        $purchase = $this->validDate($purchaseDate, 'Ngày mua');
        $expiry = $expiresAt ? $this->validDate($expiresAt, 'Ngày hết hạn') : null;

        if ($expiry && strtotime($expiry) < strtotime($purchase)) {
            throw new InvalidArgumentException('Ngày hết hạn không được sớm hơn ngày mua.');
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO license_pools (software_id, total_quantity, available_quantity, purchase_date, expires_at, reusable_after_revocation)
             VALUES (:software_id, :total_quantity, 0, :purchase_date, :expires_at, :reusable_after_revocation)"
        );
        $stmt->execute([
            ':software_id' => $softwareId,
            ':total_quantity' => $totalQuantity,
            ':purchase_date' => $purchase,
            ':expires_at' => $expiry,
            ':reusable_after_revocation' => $reusableAfterRevocation ? 1 : 0,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function addKeys(int $poolId, string $rawKeys): int
    {
        $keys = array_values(array_unique(array_filter(array_map('trim', preg_split('/\R+/', $rawKeys)))));

        if (!$keys) {
            throw new InvalidArgumentException('Danh sách key không được để trống.');
        }

        $pool = $this->getPoolForUpdate($poolId, false);
        if (!$pool) {
            throw new InvalidArgumentException('Pool license không tồn tại.');
        }

        $currentCount = (int)$pool['key_count'];
        $capacity = (int)$pool['total_quantity'] - $currentCount;

        if (count($keys) > $capacity) {
            throw new InvalidArgumentException('Số key nhập vượt quá sức chứa còn lại của pool.');
        }

        $inserted = 0;

        $this->conn->beginTransaction();
        try {
            $pool = $this->getPoolForUpdate($poolId, true);
            $currentCount = (int)$pool['key_count'];
            $capacity = (int)$pool['total_quantity'] - $currentCount;

            if (count($keys) > $capacity) {
                throw new InvalidArgumentException('Pool vừa thay đổi, số key nhập vượt quá sức chứa còn lại.');
            }

            $insert = $this->conn->prepare(
                "INSERT INTO license_keys (pool_id, key_value, is_assigned)
                 VALUES (:pool_id, :key_value, 0)"
            );

            foreach ($keys as $key) {
                try {
                    $insert->execute([':pool_id' => $poolId, ':key_value' => $key]);
                    $inserted++;
                } catch (PDOException $exception) {
                    if ($exception->getCode() !== '23000') {
                        throw $exception;
                    }
                }
            }

            $this->syncPoolAvailability($poolId);
            $this->conn->commit();
        } catch (Throwable $exception) {
            $this->conn->rollBack();
            throw $exception;
        }

        if ($inserted === 0) {
            throw new InvalidArgumentException('Không có key mới được thêm. Có thể tất cả key đã tồn tại.');
        }

        return $inserted;
    }

    public function addAsset(int $softwareId, string $version, string $osType, string $downloadUrl): int
    {
        $version = trim($version);
        $downloadUrl = trim($downloadUrl);

        if ($version === '') {
            throw new InvalidArgumentException('Phiên bản phần mềm không được để trống.');
        }

        if (!in_array($osType, ['Windows', 'macOS', 'Linux', 'Web'], true)) {
            throw new InvalidArgumentException('Hệ điều hành không hợp lệ.');
        }

        if (!filter_var($downloadUrl, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Link tải phải là URL hợp lệ.');
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO software_assets (software_id, version, os_type, download_url)
             VALUES (:software_id, :version, :os_type, :download_url)"
        );
        $stmt->execute([
            ':software_id' => $softwareId,
            ':version' => $version,
            ':os_type' => $osType,
            ':download_url' => $downloadUrl,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function deleteAsset(int $id): void
    {
        $stmt = $this->conn->prepare("DELETE FROM software_assets WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function getPoolForUpdate(int $poolId, bool $forUpdate): ?array
    {
        $query = "SELECT * FROM license_pools WHERE id = :id" . ($forUpdate ? ' FOR UPDATE' : '');

        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $poolId]);
        $pool = $stmt->fetch();

        if (!$pool) {
            return null;
        }

        $count = $this->conn->prepare("SELECT COUNT(*) FROM license_keys WHERE pool_id = :id");
        $count->execute([':id' => $poolId]);
        $pool['key_count'] = (int)$count->fetchColumn();

        return $pool;
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

    private function validDate(string $value, string $field): string
    {
        $date = DateTime::createFromFormat('Y-m-d', $value);
        $errors = DateTime::getLastErrors();

        if (!$date || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))) {
            throw new InvalidArgumentException($field . ' không hợp lệ.');
        }

        return $date->format('Y-m-d');
    }
}
