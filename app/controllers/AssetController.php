<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/AssetModel.php';

class AssetController {
    private $model;

    public function __construct() {
        $this->model = new AssetModel();
    }

    public function index() {
        $page = $_GET['page'] ?? 'assets';
        $action = $_GET['action'] ?? 'view';

        if ($page === 'stats') {
            $this->stats();
            return;
        }

        if ($action === 'add') {
            $this->add();
            return;
        }
        if ($action === 'delete') {
            $this->delete();
            return;
        }
        // Gọi hàm xuất Excel nếu URL yêu cầu
        if ($action === 'export') {
            $this->exportCsv();
            return;
        }

        $this->viewAssets();
    }

    private function viewAssets() {
        $assets = $this->model->getAllAssets();
        $stats = $this->model->getOsStats(); // Thêm dòng này để lấy data vẽ biểu đồ
        require_once __DIR__ . '/../views/asset_list.php';
    }

    private function stats() {
        $stats = $this->model->getOsStats();
        require_once __DIR__ . '/../views/stats.php';
    }

    private function add() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $software_id = $_POST['software_id'] ?? '';
            $os_type = $_POST['os_type'] ?? '';
            $download_link = $_POST['download_link'] ?? '';

            if ($this->model->addAsset($software_id, $os_type, $download_link)) {
                echo json_encode(['status' => 'success', 'message' => 'Đã thêm link cài đặt thành công!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Lỗi khi lưu vào Database!']);
            }
            exit;
        }
    }

    private function delete() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            if ($this->model->deleteAsset($id)) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error']);
            }
            exit;
        }
    }

    // TÍNH NĂNG MỚI: XUẤT FILE EXCEL (CSV)
    private function exportCsv() {
        $assets = $this->model->getAllAssets();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=Danh_Sach_Link_Tai.csv');
        
        $output = fopen('php://output', 'w');
        // Thêm BOM để Excel đọc tiếng Việt không bị lỗi font
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cột tiêu đề
        fputcsv($output, ['ID', 'Tên phần mềm', 'Hệ điều hành', 'Link tải']);
        
        // Đổ dữ liệu
        foreach ($assets as $a) {
            fputcsv($output, [$a['id'], $a['title'], $a['os_type'], $a['download_link']]);
        }
        
        fclose($output);
        exit;
    }
}
?>