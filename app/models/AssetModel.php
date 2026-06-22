<?php
declare(strict_types=1);

class AssetModel {
    private $db;

    public function __construct() {
        $this->db = (new Database())->getConnection();
    }

    public function getAllAssets() {
        // Đã sửa 's.title' thành 's.name' cho chuẩn với Database của nhóm trưởng
        $sql = "SELECT a.*, s.name FROM software_assets a 
                JOIN software_titles s ON a.software_id = s.id 
                ORDER BY a.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addAsset($software_id, $os_type, $download_link) {
        $sql = "INSERT INTO software_assets (software_id, os_type, download_link) 
                VALUES (:software_id, :os_type, :download_link)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':software_id' => $software_id,
            ':os_type' => $os_type,
            ':download_link' => $download_link
        ]);
    }

    public function deleteAsset($id) {
        $sql = "DELETE FROM software_assets WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    public function getOsStats() {
        $sql = "SELECT os_type, COUNT(*) as total FROM software_assets GROUP BY os_type";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>