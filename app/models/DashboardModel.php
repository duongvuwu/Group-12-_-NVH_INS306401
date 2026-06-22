<?php
declare(strict_types=1);

class DashboardModel
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function countDepartments(): int
    {
        return $this->countTable('departments');
    }

    public function countTotalKeys(): int
    {
        return $this->countTable('license_keys');
    }

    public function countUsedKeys(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM license_keys WHERE is_assigned = :assigned");
        $stmt->execute([':assigned' => 1]);

        return (int)$stmt->fetchColumn();
    }

    public function countAvailableKeys(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM license_keys WHERE is_assigned = :assigned");
        $stmt->execute([':assigned' => 0]);

        return (int)$stmt->fetchColumn();
    }

    public function countActiveLicenses(): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM license_allocations WHERE status = :status");
        $stmt->execute([':status' => 'Active']);

        return (int)$stmt->fetchColumn();
    }

    public function countSoftwareTitles(): int
    {
        return $this->countTable('software_titles');
    }

    public function countUsers(): int
    {
        return $this->countTable('users');
    }

    public function getStats(): array
    {
        $totalKeys = $this->countTotalKeys();
        $usedKeys = $this->countUsedKeys();

        return [
            'departments' => $this->countDepartments(),
            'users' => $this->countUsers(),
            'softwares' => $this->countSoftwareTitles(),
            'total_keys' => $totalKeys,
            'used_keys' => $usedKeys,
            'available_keys' => $this->countAvailableKeys(),
            'active_allocations' => $this->countActiveLicenses(),
            'usage_rate' => $totalKeys > 0 ? round(($usedKeys / $totalKeys) * 100, 1) : 0,
        ];
    }

    public function getInventoryBySoftware(): array
    {
        $query = "SELECT
                    s.name,
                    s.vendor,
                    COUNT(lk.id) AS total_keys,
                    SUM(CASE WHEN lk.is_assigned = 1 THEN 1 ELSE 0 END) AS used_keys,
                    SUM(CASE WHEN lk.is_assigned = 0 THEN 1 ELSE 0 END) AS available_keys
                  FROM software_titles s
                  LEFT JOIN license_pools lp ON lp.software_id = s.id
                  LEFT JOIN license_keys lk ON lk.pool_id = lp.id
                  GROUP BY s.id, s.name, s.vendor
                  ORDER BY total_keys DESC, s.name ASC
                  LIMIT 8";

        return $this->conn->query($query)->fetchAll();
    }

    public function getDepartmentUsage(): array
    {
        $query = "SELECT
                    d.name AS department_name,
                    COUNT(la.id) AS active_count
                  FROM departments d
                  LEFT JOIN users u ON u.department_id = d.id
                  LEFT JOIN license_allocations la ON la.user_id = u.id AND la.status = 'Active'
                  GROUP BY d.id, d.name
                  ORDER BY active_count DESC, d.name ASC
                  LIMIT 6";

        return $this->conn->query($query)->fetchAll();
    }

    public function getExpiringAllocations(int $days = 14): array
    {
        $query = "SELECT
                    la.end_date,
                    u.full_name,
                    d.name AS department_name,
                    st.name AS software_name,
                    DATEDIFF(la.end_date, NOW()) AS days_left
                  FROM license_allocations la
                  JOIN users u ON u.id = la.user_id
                  JOIN departments d ON d.id = u.department_id
                  JOIN license_keys lk ON lk.id = la.key_id
                  JOIN license_pools lp ON lp.id = lk.pool_id
                  JOIN software_titles st ON st.id = lp.software_id
                  WHERE la.status = 'Active'
                    AND la.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY)
                  ORDER BY la.end_date ASC
                  LIMIT 5";

        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTopSoftware(int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));
        $query = "SELECT
                    s.id,
                    s.name,
                    s.vendor,
                    COUNT(la.id) AS allocation_count,
                    SUM(CASE WHEN la.status = 'Active' THEN 1 ELSE 0 END) AS active_count
                  FROM software_titles s
                  LEFT JOIN license_pools lp ON lp.software_id = s.id
                  LEFT JOIN license_keys lk ON lk.pool_id = lp.id
                  LEFT JOIN license_allocations la ON la.key_id = lk.id
                  GROUP BY s.id, s.name, s.vendor
                  ORDER BY allocation_count DESC, active_count DESC, s.name ASC
                  LIMIT {$limit}";

        return $this->conn->query($query)->fetchAll();
    }

    public function getTopDepartments(int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));
        $query = "SELECT
                    d.id,
                    d.name,
                    COUNT(la.id) AS allocation_count,
                    SUM(CASE WHEN la.status = 'Active' THEN 1 ELSE 0 END) AS active_count
                  FROM departments d
                  LEFT JOIN users u ON u.department_id = d.id
                  LEFT JOIN license_allocations la ON la.user_id = u.id
                  GROUP BY d.id, d.name
                  ORDER BY allocation_count DESC, active_count DESC, d.name ASC
                  LIMIT {$limit}";

        return $this->conn->query($query)->fetchAll();
    }

    public function getUnusedSoftware(int $limit = 5): array
    {
        $limit = max(1, min($limit, 10));
        $query = "SELECT s.id, s.name, s.vendor
                  FROM software_titles s
                  WHERE NOT EXISTS (
                      SELECT 1
                      FROM license_pools lp
                      JOIN license_keys lk ON lk.pool_id = lp.id
                      JOIN license_allocations la ON la.key_id = lk.id
                      WHERE lp.software_id = s.id
                  )
                  ORDER BY s.name ASC
                  LIMIT {$limit}";

        return $this->conn->query($query)->fetchAll();
    }

    private function countTable(string $table): int
    {
        $allowedTables = ['departments', 'users', 'software_titles', 'license_keys'];
        if (!in_array($table, $allowedTables, true)) {
            throw new InvalidArgumentException('Bang thong ke khong hop le.');
        }

        return (int)$this->conn->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
    }
}
