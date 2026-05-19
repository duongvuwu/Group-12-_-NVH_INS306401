<?php
declare(strict_types=1);

class InventoryController
{
    private $model;
    private $audit;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->model = new InventoryModel($db);
        $this->audit = new AuditLogModel($db);
    }

    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        $softwares = $this->model->getSoftwaresForSelect();
        $overview = $this->model->getInventoryOverview();
        $pools = $this->model->getPools();
        $assets = $this->model->getAssets();

        require BASE_PATH . '/app/views/inventory_view.php';
    }

    private function handlePost(): void
    {
        try {
            require_csrf();
            $action = $_POST['action'] ?? '';

            if ($action === 'add_pool') {
                $id = $this->model->addPool(
                    positive_int($_POST['software_id'] ?? null, 'Phần mềm'),
                    positive_int($_POST['total_quantity'] ?? null, 'Số lượng'),
                    $_POST['purchase_date'] ?? '',
                    $_POST['expires_at'] ?? null,
                    isset($_POST['reusable_after_revocation'])
                );
                $this->audit->record('create_license_pool', 'license_pool', $id, [
                    'software_id' => $_POST['software_id'] ?? null,
                    'total_quantity' => $_POST['total_quantity'] ?? null,
                ]);
                redirect_with_flash('inventory', 'success', 'Đã tạo pool license mới. Hãy nhập key chi tiết cho pool này.');
            }

            if ($action === 'add_keys') {
                $poolId = positive_int($_POST['pool_id'] ?? null, 'Pool');
                $count = $this->model->addKeys($poolId, $_POST['keys'] ?? '');
                $this->audit->record('import_license_keys', 'license_pool', $poolId, ['count' => $count]);
                redirect_with_flash('inventory', 'success', 'Đã nhập ' . $count . ' key mới vào kho.');
            }

            if ($action === 'add_asset') {
                $id = $this->model->addAsset(
                    positive_int($_POST['software_id'] ?? null, 'Phần mềm'),
                    $_POST['version'] ?? '',
                    $_POST['os_type'] ?? '',
                    $_POST['download_url'] ?? ''
                );
                $this->audit->record('create_software_asset', 'software_asset', $id, [
                    'software_id' => $_POST['software_id'] ?? null,
                    'os_type' => $_POST['os_type'] ?? null,
                ]);
                redirect_with_flash('inventory', 'success', 'Đã thêm link cài đặt phần mềm.');
            }

            if ($action === 'delete_asset') {
                $id = positive_int($_POST['id'] ?? null, 'ID asset');
                $this->model->deleteAsset($id);
                $this->audit->record('delete_software_asset', 'software_asset', $id);
                redirect_with_flash('inventory', 'success', 'Đã xóa link cài đặt.');
            }

            redirect_with_flash('inventory', 'error', 'Thao tác không hợp lệ.');
        } catch (Throwable $exception) {
            redirect_with_flash('inventory', 'error', $exception->getMessage());
        }
    }
}
