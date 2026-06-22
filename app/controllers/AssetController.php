<?php
declare(strict_types=1);

class AssetController
{
    private AssetModel $model;
    private AuditLogModel $audit;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->model = new AssetModel($db);
        $this->audit = new AuditLogModel($db);
    }

    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePost();
        }

        $assets = $this->model->getAllAssets();
        $softwares = $this->model->getSoftwareOptions();
        $osStats = $this->model->getOsStats();

        require BASE_PATH . '/app/views/asset_list.php';
    }

    private function handlePost(): void
    {
        try {
            require_csrf();
            $action = $_POST['action'] ?? '';

            if ($action === 'add') {
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
                redirect_with_flash('assets', 'success', 'Đã thêm tài nguyên cài đặt.');
            }

            if ($action === 'delete') {
                $id = positive_int($_POST['id'] ?? null, 'ID asset');
                $this->model->deleteAsset($id);
                $this->audit->record('delete_software_asset', 'software_asset', $id);
                redirect_with_flash('assets', 'success', 'Đã xóa tài nguyên cài đặt.');
            }

            if ($action === 'export') {
                $this->exportCsv();
            }

            redirect_with_flash('assets', 'error', 'Thao tác không hợp lệ.');
        } catch (Throwable $exception) {
            redirect_with_flash('assets', 'error', $exception->getMessage());
        }
    }

    private function exportCsv(): void
    {
        $assets = $this->model->getAllAssets();
        $this->audit->record('export_software_assets', 'software_asset', null, ['count' => count($assets)]);

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="software-assets.csv"');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        $output = fopen('php://output', 'wb');
        if ($output === false) {
            throw new RuntimeException('Không thể tạo file CSV.');
        }

        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, ['ID', 'Software', 'Vendor', 'Version', 'OS', 'Download URL', 'Created At']);

        foreach ($assets as $asset) {
            fputcsv($output, [
                $asset['id'],
                $this->csvCell($asset['software_name']),
                $this->csvCell($asset['vendor']),
                $this->csvCell($asset['version']),
                $asset['os_type'],
                $this->csvCell($asset['download_url']),
                $asset['created_at'],
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
