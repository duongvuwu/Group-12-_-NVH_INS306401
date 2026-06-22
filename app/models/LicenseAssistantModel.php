<?php
declare(strict_types=1);

class LicenseAssistantModel
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getActiveOverview(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                SUM(CASE WHEN status = 'Active' AND end_date >= NOW() THEN 1 ELSE 0 END) AS valid_active,
                SUM(CASE WHEN status = 'Active' AND end_date < NOW() THEN 1 ELSE 0 END) AS overdue_active,
                SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS status_active
             FROM license_allocations"
        );
        $stmt->execute();

        return $stmt->fetch() ?: ['valid_active' => 0, 'overdue_active' => 0, 'status_active' => 0];
    }

    public function getExpiringLicenses(int $days, int $limit = 8): array
    {
        $limit = $this->safeLimit($limit, 20);
        $count = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM license_allocations
             WHERE status = 'Active'
               AND end_date >= NOW()
               AND end_date <= DATE_ADD(NOW(), INTERVAL :days DAY)"
        );
        $count->bindValue(':days', $days, PDO::PARAM_INT);
        $count->execute();

        $stmt = $this->conn->prepare(
            "SELECT
                la.id,
                u.full_name,
                u.email,
                d.name AS department_name,
                s.name AS software_name,
                la.end_date,
                DATEDIFF(la.end_date, NOW()) AS days_left
             FROM license_allocations la
             JOIN users u ON u.id = la.user_id
             JOIN departments d ON d.id = u.department_id
             JOIN license_keys lk ON lk.id = la.key_id
             JOIN license_pools lp ON lp.id = lk.pool_id
             JOIN software_titles s ON s.id = lp.software_id
             WHERE la.status = 'Active'
               AND la.end_date >= NOW()
               AND la.end_date <= DATE_ADD(NOW(), INTERVAL :days DAY)
             ORDER BY la.end_date ASC
             LIMIT {$limit}"
        );
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return ['total' => (int)$count->fetchColumn(), 'items' => $stmt->fetchAll()];
    }

    public function getOverdueUnrevoked(int $limit = 8): array
    {
        $limit = $this->safeLimit($limit, 20);
        $count = $this->conn->prepare(
            "SELECT COUNT(*) FROM license_allocations WHERE status = 'Active' AND end_date < NOW()"
        );
        $count->execute();

        $stmt = $this->conn->prepare(
            "SELECT
                la.id,
                u.full_name,
                u.email,
                d.name AS department_name,
                s.name AS software_name,
                la.end_date,
                ABS(DATEDIFF(la.end_date, NOW())) AS overdue_days
             FROM license_allocations la
             JOIN users u ON u.id = la.user_id
             JOIN departments d ON d.id = u.department_id
             JOIN license_keys lk ON lk.id = la.key_id
             JOIN license_pools lp ON lp.id = lk.pool_id
             JOIN software_titles s ON s.id = lp.software_id
             WHERE la.status = 'Active' AND la.end_date < NOW()
             ORDER BY la.end_date ASC
             LIMIT {$limit}"
        );
        $stmt->execute();

        return ['total' => (int)$count->fetchColumn(), 'items' => $stmt->fetchAll()];
    }

    public function getTopDepartments(int $limit = 5): array
    {
        $limit = $this->safeLimit($limit, 10);
        $stmt = $this->conn->prepare(
            "SELECT
                d.id,
                d.name,
                COUNT(la.id) AS active_count,
                COUNT(DISTINCT CASE WHEN la.id IS NOT NULL THEN u.id END) AS user_count
             FROM departments d
             LEFT JOIN users u ON u.department_id = d.id
             LEFT JOIN license_allocations la
                ON la.user_id = u.id
               AND la.status = 'Active'
               AND la.end_date >= NOW()
             GROUP BY d.id, d.name
             ORDER BY active_count DESC, d.name ASC
             LIMIT {$limit}"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getLowInventory(int $threshold, int $limit = 8): array
    {
        $limit = $this->safeLimit($limit, 20);
        $stmt = $this->conn->prepare(
            "SELECT
                s.id,
                s.name,
                s.vendor,
                COUNT(DISTINCT lp.id) AS pool_count,
                COUNT(lk.id) AS total_keys,
                SUM(CASE WHEN lk.is_assigned = 0 THEN 1 ELSE 0 END) AS available_keys
             FROM software_titles s
             JOIN license_pools lp ON lp.software_id = s.id
             LEFT JOIN license_keys lk ON lk.pool_id = lp.id
             GROUP BY s.id, s.name, s.vendor
             HAVING available_keys <= :threshold
             ORDER BY available_keys ASC, total_keys DESC, s.name ASC
             LIMIT {$limit}"
        );
        $stmt->bindValue(':threshold', $threshold, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function findUsers(string $search, int $limit = 5): array
    {
        $limit = $this->safeLimit($limit, 10);
        $stmt = $this->conn->prepare(
            "SELECT u.id, u.full_name, u.email, u.role, d.name AS department_name
             FROM users u
             JOIN departments d ON d.id = u.department_id
             WHERE LOWER(u.email) = LOWER(:exact)
                OR LOWER(u.full_name) LIKE LOWER(:search_name)
                OR LOWER(u.email) LIKE LOWER(:search_email)
             ORDER BY CASE WHEN LOWER(u.email) = LOWER(:exact_order) THEN 0 ELSE 1 END, u.full_name ASC
             LIMIT {$limit}"
        );
        $stmt->execute([
            ':exact' => $search,
            ':search_name' => '%' . $search . '%',
            ':search_email' => '%' . $search . '%',
            ':exact_order' => $search,
        ]);

        return $stmt->fetchAll();
    }

    public function getUserLicenses(int $userId): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                la.id,
                la.status,
                la.start_date,
                la.end_date,
                s.name AS software_name,
                s.vendor
             FROM license_allocations la
             JOIN license_keys lk ON lk.id = la.key_id
             JOIN license_pools lp ON lp.id = lk.pool_id
             JOIN software_titles s ON s.id = lp.software_id
             WHERE la.user_id = :user_id
             ORDER BY CASE la.status WHEN 'Active' THEN 0 WHEN 'Expired' THEN 1 ELSE 2 END, la.end_date DESC
             LIMIT 20"
        );
        $stmt->execute([':user_id' => $userId]);

        return $stmt->fetchAll();
    }

    public function getRiskSummary(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT
                SUM(CASE WHEN status = 'Active' AND end_date < NOW() THEN 1 ELSE 0 END) AS overdue,
                SUM(CASE WHEN status = 'Active' AND end_date >= NOW() AND end_date <= DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) AS expiring_7_days
             FROM license_allocations"
        );
        $stmt->execute();
        $allocationRisk = $stmt->fetch() ?: ['overdue' => 0, 'expiring_7_days' => 0];

        $inventory = $this->conn->prepare(
            "SELECT COUNT(*) FROM (
                SELECT s.id
                FROM software_titles s
                JOIN license_pools lp ON lp.software_id = s.id
                LEFT JOIN license_keys lk ON lk.pool_id = lp.id
                GROUP BY s.id
                HAVING SUM(CASE WHEN lk.is_assigned = 0 THEN 1 ELSE 0 END) <= 5
             ) low_stock"
        );
        $inventory->execute();

        return [
            'overdue' => (int)$allocationRisk['overdue'],
            'expiring_7_days' => (int)$allocationRisk['expiring_7_days'],
            'low_inventory' => (int)$inventory->fetchColumn(),
        ];
    }

    private function safeLimit(int $limit, int $maximum): int
    {
        return max(1, min($limit, $maximum));
    }
}
