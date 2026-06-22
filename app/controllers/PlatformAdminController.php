<?php
declare(strict_types=1);

class PlatformAdminController
{
    private $model;
    private $audit;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->model = new PlatformAdminModel($db);
        $this->audit = new AuditLogModel($db);
    }

    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($_POST['action'] ?? '') === 'user_detail') {
                $this->handleUserDetailRequest();
                return;
            }

            $this->handlePost();
        }

        $data = [
            'departments' => $this->model->getAllDepartments(),
            'users' => $this->model->getAllUsers(),
            'softwares' => $this->model->getAllSoftwares(),
        ];

        require BASE_PATH . '/app/views/platform_admin_view.php';
    }

    private function handlePost(): void
    {
        try {
            require_csrf();
            $action = $_POST['action'] ?? '';

            if ($action === 'export_users') {
                $this->exportUsersCsv();
            }

            if ($action === 'add_dept') {
                $id = $this->model->addDepartment($_POST['name'] ?? '', $_POST['description'] ?? null);
                $this->audit->record('create_department', 'department', $id, ['name' => $_POST['name'] ?? '']);
                redirect_with_flash('admin', 'success', 'Đã thêm khoa/phòng ban mới.');
            }

            if ($action === 'delete_dept') {
                $id = positive_int($_POST['id'] ?? null, 'ID khoa');
                $this->model->deleteDepartment($id);
                $this->audit->record('delete_department', 'department', $id);
                redirect_with_flash('admin', 'success', 'Đã xóa khoa/phòng ban.');
            }

            if ($action === 'add_user') {
                $email = $this->buildVnuEmail($_POST['email_prefix'] ?? ($_POST['email'] ?? ''));
                $id = $this->model->addUser(
                    positive_int($_POST['dept_id'] ?? null, 'Khoa/phòng ban'),
                    $_POST['full_name'] ?? '',
                    $email,
                    $_POST['role'] ?? ''
                );
                $this->audit->record('create_user', 'user', $id, ['email' => $email]);
                redirect_with_flash('admin', 'success', 'Đã thêm người dùng mới.');
            }

            if ($action === 'delete_user') {
                $id = positive_int($_POST['id'] ?? null, 'ID người dùng');
                $this->model->deleteUser($id);
                $this->audit->record('delete_user', 'user', $id);
                redirect_with_flash('admin', 'success', 'Đã xóa người dùng.');
            }

            if ($action === 'add_software') {
                $id = $this->model->addSoftware($_POST['name'] ?? '', $_POST['vendor'] ?? '');
                $this->audit->record('create_software', 'software_title', $id, ['name' => $_POST['name'] ?? '']);
                redirect_with_flash('admin', 'success', 'Đã thêm phần mềm mới.');
            }

            if ($action === 'delete_software') {
                $id = positive_int($_POST['id'] ?? null, 'ID phần mềm');
                $this->model->deleteSoftware($id);
                $this->audit->record('delete_software', 'software_title', $id);
                redirect_with_flash('admin', 'success', 'Đã xóa phần mềm.');
            }

            redirect_with_flash('admin', 'error', 'Thao tác không hợp lệ.');
        } catch (Throwable $exception) {
            redirect_with_flash('admin', 'error', $exception->getMessage());
        }
    }

    private function buildVnuEmail(string $value): string
    {
        $raw = strtolower(trim($value));
        if ($raw === '') {
            return '';
        }

        $localPart = explode('@', $raw, 2)[0];
        $localPart = preg_replace('/[^a-z0-9._-]/', '', $localPart) ?? '';

        return $localPart !== '' ? $localPart . '@vnu.edu.vn' : '';
    }

    private function handleUserDetailRequest(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            require_csrf();
            $userId = positive_int($_POST['id'] ?? null, 'ID người dùng');
            $user = $this->model->getUserDetail($userId);

            if ($user === null) {
                http_response_code(404);
                echo json_encode(['ok' => false, 'message' => 'Người dùng không tồn tại.'], JSON_UNESCAPED_UNICODE);
                return;
            }

            echo json_encode([
                'ok' => true,
                'user' => $user,
                'licenses' => $this->model->getUserLicenses($userId),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } catch (Throwable $exception) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => $exception->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    private function exportUsersCsv(): void
    {
        $users = $this->model->getAllUsers();
        $this->audit->record('export_users', 'user', null, ['count' => count($users)]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="licenseos-users.csv"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            throw new RuntimeException('Không thể tạo file CSV.');
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Full name', 'Email', 'Department', 'Role', 'Licenses', 'Created at']);

        foreach ($users as $user) {
            fputcsv($output, [
                $user['id'],
                $this->csvCell((string)$user['full_name']),
                $this->csvCell((string)$user['email']),
                $this->csvCell((string)$user['department_name']),
                $user['role'],
                $user['allocation_count'],
                $user['created_at'],
            ]);
        }

        fclose($output);
        exit;
    }

    private function csvCell(string $value): string
    {
        return preg_match('/^[=+\-@]/', $value) ? "'" . $value : $value;
    }
}
