<?php
declare(strict_types=1);

class PlatformAdminModel
{
    private $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getAllDepartments(): array
    {
        $query = "SELECT
                    d.id,
                    d.name,
                    d.description,
                    d.created_at,
                    COUNT(DISTINCT u.id) AS user_count,
                    COUNT(DISTINCT ar.id) AS rule_count
                  FROM departments d
                  LEFT JOIN users u ON u.department_id = d.id
                  LEFT JOIN allocation_rules ar ON ar.department_id = d.id
                  GROUP BY d.id, d.name, d.description, d.created_at
                  ORDER BY d.id DESC";

        return $this->conn->query($query)->fetchAll();
    }

    public function addDepartment(string $name, ?string $description): int
    {
        $name = trim($name);
        $description = trim((string)$description);

        if ($name === '') {
            throw new InvalidArgumentException('Tên khoa/phòng ban không được để trống.');
        }

        if ($this->departmentNameExists($name)) {
            throw new InvalidArgumentException('Khoa/phòng ban này đã tồn tại.');
        }

        $stmt = $this->conn->prepare("INSERT INTO departments (name, description) VALUES (:name, :description)");
        $stmt->execute([
            ':name' => $name,
            ':description' => $description !== '' ? $description : null,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function deleteDepartment(int $id): void
    {
        $usage = $this->countDepartmentDependencies($id);
        $totalUsage = array_sum($usage);

        if ($totalUsage > 0) {
            throw new InvalidArgumentException('Không thể xóa khoa đang có người dùng, luật cấp phát hoặc thống kê liên quan.');
        }

        $stmt = $this->conn->prepare("DELETE FROM departments WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function getAllUsers(): array
    {
        $query = "SELECT
                    u.id,
                    u.full_name,
                    u.email,
                    u.role,
                    u.created_at,
                    d.name AS department_name,
                    COUNT(la.id) AS allocation_count
                  FROM users u
                  JOIN departments d ON d.id = u.department_id
                  LEFT JOIN license_allocations la ON la.user_id = u.id
                  GROUP BY u.id, u.full_name, u.email, u.role, u.created_at, d.name
                  ORDER BY u.id DESC";

        return $this->conn->query($query)->fetchAll();
    }

    public function addUser(int $departmentId, string $name, string $email, string $role): int
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new InvalidArgumentException('Họ tên người dùng không được để trống.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Email không hợp lệ.');
        }

        if (!preg_match('/^[a-z0-9._-]+@vnu\.edu\.vn$/', $email)) {
            throw new InvalidArgumentException('Email phải sử dụng tên miền @vnu.edu.vn.');
        }

        if (!in_array($role, ['Student', 'Teacher', 'Admin'], true)) {
            throw new InvalidArgumentException('Vai trò người dùng không hợp lệ.');
        }

        if (!$this->departmentExists($departmentId)) {
            throw new InvalidArgumentException('Khoa/phòng ban được chọn không tồn tại.');
        }

        if ($this->emailExists($email)) {
            throw new InvalidArgumentException('Email này đã tồn tại trong hệ thống.');
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO users (department_id, full_name, email, role)
             VALUES (:department_id, :full_name, :email, :role)"
        );
        $stmt->execute([
            ':department_id' => $departmentId,
            ':full_name' => $name,
            ':email' => $email,
            ':role' => $role,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function deleteUser(int $id): void
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM license_allocations WHERE user_id = :id");
        $stmt->execute([':id' => $id]);

        if ((int)$stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException('Không thể xóa người dùng đã có lịch sử cấp phát license.');
        }

        $delete = $this->conn->prepare("DELETE FROM users WHERE id = :id");
        $delete->execute([':id' => $id]);
    }

    public function getAllSoftwares(): array
    {
        $query = "SELECT
                    s.id,
                    s.name,
                    s.vendor,
                    s.created_at,
                    (SELECT COUNT(*) FROM license_pools lp WHERE lp.software_id = s.id) AS pool_count,
                    (SELECT COALESCE(SUM(lp.total_quantity), 0) FROM license_pools lp WHERE lp.software_id = s.id) AS total_quantity,
                    (SELECT COALESCE(SUM(lp.available_quantity), 0) FROM license_pools lp WHERE lp.software_id = s.id) AS available_quantity,
                    (SELECT COUNT(*) FROM allocation_rules ar WHERE ar.software_id = s.id) AS rule_count,
                    (SELECT COUNT(*) FROM software_assets sa WHERE sa.software_id = s.id) AS asset_count
                  FROM software_titles s
                  ORDER BY s.id DESC";

        return $this->conn->query($query)->fetchAll();
    }

    public function addSoftware(string $name, string $vendor): int
    {
        $name = trim($name);
        $vendor = trim($vendor);

        if ($name === '' || $vendor === '') {
            throw new InvalidArgumentException('Tên phần mềm và nhà phát hành không được để trống.');
        }

        if ($this->softwareExists($name, $vendor)) {
            throw new InvalidArgumentException('Phần mềm này đã tồn tại với cùng nhà phát hành.');
        }

        $stmt = $this->conn->prepare("INSERT INTO software_titles (name, vendor) VALUES (:name, :vendor)");
        $stmt->execute([':name' => $name, ':vendor' => $vendor]);

        return (int)$this->conn->lastInsertId();
    }

    public function deleteSoftware(int $id): void
    {
        $usage = $this->countSoftwareDependencies($id);
        $totalUsage = array_sum($usage);

        if ($totalUsage > 0) {
            throw new InvalidArgumentException('Không thể xóa phần mềm đang có pool, asset, luật hoặc thống kê liên quan.');
        }

        $stmt = $this->conn->prepare("DELETE FROM software_titles WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function departmentNameExists(string $name): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM departments WHERE LOWER(name) = LOWER(:name)");
        $stmt->execute([':name' => $name]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function departmentExists(int $id): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM departments WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function emailExists(string $email): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function softwareExists(string $name, string $vendor): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) FROM software_titles WHERE LOWER(name) = LOWER(:name) AND LOWER(vendor) = LOWER(:vendor)"
        );
        $stmt->execute([':name' => $name, ':vendor' => $vendor]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function countDepartmentDependencies(int $id): array
    {
        return [
            'users' => $this->countBy("SELECT COUNT(*) FROM users WHERE department_id = :id", $id),
            'rules' => $this->countBy("SELECT COUNT(*) FROM allocation_rules WHERE department_id = :id", $id),
            'stats' => $this->countBy("SELECT COUNT(*) FROM usage_stats WHERE department_id = :id", $id),
        ];
    }

    private function countSoftwareDependencies(int $id): array
    {
        return [
            'pools' => $this->countBy("SELECT COUNT(*) FROM license_pools WHERE software_id = :id", $id),
            'rules' => $this->countBy("SELECT COUNT(*) FROM allocation_rules WHERE software_id = :id", $id),
            'assets' => $this->countBy("SELECT COUNT(*) FROM software_assets WHERE software_id = :id", $id),
            'stats' => $this->countBy("SELECT COUNT(*) FROM usage_stats WHERE software_id = :id", $id),
        ];
    }

    private function countBy(string $query, int $id): int
    {
        $stmt = $this->conn->prepare($query);
        $stmt->execute([':id' => $id]);

        return (int)$stmt->fetchColumn();
    }
}
