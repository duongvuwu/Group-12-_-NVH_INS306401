<?php
declare(strict_types=1);

class LicenseAllocationController
{
    private $model;
    private $audit;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->model = new LicenseAllocationModel($db);
        $this->audit = new AuditLogModel($db);
    }

    public function index(): void
    {
        $this->model->syncExpiredAllocations();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        $users = $this->model->getUsers();
        $softwares = $this->model->getSoftwares();
        $allocations = $this->model->getAllocations();

        require BASE_PATH . '/app/views/license_allocations_view.php';
    }

    private function handlePost(): void
    {
        try {
            require_csrf();
            $action = $_POST['action'] ?? '';

            if ($action === 'request') {
                $id = $this->model->requestAllocation(
                    positive_int($_POST['user_id'] ?? null, 'Người dùng'),
                    positive_int($_POST['software_id'] ?? null, 'Phần mềm'),
                    positive_int($_POST['duration_days'] ?? null, 'Thời hạn')
                );
                $this->audit->record('allocate_license_key', 'license_allocation', $id, [
                    'user_id' => $_POST['user_id'] ?? null,
                    'software_id' => $_POST['software_id'] ?? null,
                ]);
                redirect_with_flash('allocations', 'success', 'Đã cấp phát license thành công.');
            }

            if ($action === 'activate') {
                $id = positive_int($_POST['id'] ?? null, 'ID cấp phát');
                $this->model->activateAllocation($id, $_POST['ip_address'] ?? null);
                $this->audit->record('record_activation', 'license_allocation', $id);
                redirect_with_flash('allocations', 'success', 'Đã ghi nhận kích hoạt license.');
            }

            if ($action === 'revoke') {
                $id = positive_int($_POST['id'] ?? null, 'ID cấp phát');
                $this->model->revokeAllocation($id, $_POST['reason'] ?? '');
                $this->audit->record('revoke_license', 'license_allocation', $id, [
                    'reason' => $_POST['reason'] ?? '',
                ]);
                redirect_with_flash('allocations', 'success', 'Đã thu hồi license.');
            }

            redirect_with_flash('allocations', 'error', 'Thao tác không hợp lệ.');
        } catch (Throwable $exception) {
            redirect_with_flash('allocations', 'error', $exception->getMessage());
        }
    }
}
