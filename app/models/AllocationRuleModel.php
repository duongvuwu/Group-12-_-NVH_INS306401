<?php
declare(strict_types=1);

class AllocationRuleModel
{
    private $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getAllRules(): array
    {
        $query = "SELECT
                    ar.id,
                    ar.software_id,
                    ar.department_id,
                    ar.target_role,
                    ar.created_at,
                    s.name AS software_name,
                    s.vendor,
                    d.name AS department_name,
                    COUNT(la.id) AS active_allocations
                  FROM allocation_rules ar
                  JOIN software_titles s ON s.id = ar.software_id
                  JOIN departments d ON d.id = ar.department_id
                  LEFT JOIN users u
                    ON u.department_id = ar.department_id
                   AND (ar.target_role = 'All' OR ar.target_role = u.role)
                  LEFT JOIN license_allocations la
                    ON la.user_id = u.id
                   AND la.status = 'Active'
                   AND EXISTS (
                        SELECT 1
                        FROM license_keys lk
                        JOIN license_pools lp ON lp.id = lk.pool_id
                        WHERE lk.id = la.key_id AND lp.software_id = ar.software_id
                   )
                  GROUP BY ar.id, ar.software_id, ar.department_id, ar.target_role, ar.created_at, s.name, s.vendor, d.name
                  ORDER BY ar.created_at DESC, ar.id DESC";

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

    public function getDepartments(): array
    {
        return $this->conn->query("SELECT id, name FROM departments ORDER BY name ASC")->fetchAll();
    }

    public function addRule(int $softwareId, int $departmentId, string $targetRole): int
    {
        if (!in_array($targetRole, ['Student', 'Teacher', 'Admin', 'All'], true)) {
            throw new InvalidArgumentException('Đối tượng áp dụng không hợp lệ.');
        }

        if ($this->ruleExists($softwareId, $departmentId, $targetRole)) {
            throw new InvalidArgumentException('Luật cấp phát này đã tồn tại.');
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO allocation_rules (software_id, department_id, target_role)
             VALUES (:software_id, :department_id, :target_role)"
        );
        $stmt->execute([
            ':software_id' => $softwareId,
            ':department_id' => $departmentId,
            ':target_role' => $targetRole,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function deleteRule(int $id): void
    {
        $stmt = $this->conn->prepare("SELECT software_id, department_id, target_role FROM allocation_rules WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $rule = $stmt->fetch();

        if (!$rule) {
            throw new InvalidArgumentException('Luật cấp phát không tồn tại.');
        }

        $active = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM license_allocations la
             JOIN users u ON u.id = la.user_id
             JOIN license_keys lk ON lk.id = la.key_id
             JOIN license_pools lp ON lp.id = lk.pool_id
             WHERE la.status = 'Active'
               AND lp.software_id = :software_id
               AND u.department_id = :department_id
               AND (:target_role = 'All' OR u.role = :target_role)"
        );
        $active->execute([
            ':software_id' => (int)$rule['software_id'],
            ':department_id' => (int)$rule['department_id'],
            ':target_role' => $rule['target_role'],
        ]);

        if ((int)$active->fetchColumn() > 0) {
            throw new InvalidArgumentException('Không thể xóa luật đang có license active phụ thuộc.');
        }

        $delete = $this->conn->prepare("DELETE FROM allocation_rules WHERE id = :id");
        $delete->execute([':id' => $id]);
    }

    public function suggestSoftwareForDepartment(string $departmentName): array
    {
        $lower = function_exists('mb_strtolower')
            ? mb_strtolower($departmentName, 'UTF-8')
            : strtolower($departmentName);
        $has = static function (string $needle) use ($lower): bool {
            return strpos($lower, $needle) !== false;
        };
        $suggestions = [];

        if ($has('công nghệ') || $has('cntt') || $has('it')) {
            $suggestions = ['JetBrains All Products', 'Visual Studio', 'GitHub Copilot'];
        } elseif ($has('kiến trúc') || $has('xây dựng')) {
            $suggestions = ['AutoCAD', 'Revit', 'SketchUp'];
        } elseif ($has('kinh tế') || $has('kế toán') || $has('tài chính')) {
            $suggestions = ['MISA AMIS', 'MISA SME', 'Microsoft 365'];
        } elseif ($has('thiết kế') || $has('truyền thông')) {
            $suggestions = ['Adobe Creative Cloud', 'Figma', 'Canva Pro'];
        }

        if (!$suggestions) {
            $suggestions = ['Microsoft 365', 'Zoom Education'];
        }

        $placeholders = implode(',', array_fill(0, count($suggestions), '?'));
        $stmt = $this->conn->prepare(
            "SELECT id, name, vendor FROM software_titles WHERE name IN ($placeholders) ORDER BY name ASC"
        );
        $stmt->execute($suggestions);

        return $stmt->fetchAll();
    }

    private function ruleExists(int $softwareId, int $departmentId, string $targetRole): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM allocation_rules
             WHERE software_id = :software_id
               AND department_id = :department_id
               AND target_role = :target_role"
        );
        $stmt->execute([
            ':software_id' => $softwareId,
            ':department_id' => $departmentId,
            ':target_role' => $targetRole,
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }
}
