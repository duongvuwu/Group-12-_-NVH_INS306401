<?php
declare(strict_types=1);

class AssetModel
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getAllAssets(): array
    {
        $query = "SELECT
                    sa.id,
                    sa.software_id,
                    sa.version,
                    sa.os_type,
                    sa.download_url,
                    sa.created_at,
                    s.name AS software_name,
                    s.vendor
                  FROM software_assets sa
                  JOIN software_titles s ON s.id = sa.software_id
                  ORDER BY sa.created_at DESC, sa.id DESC";

        return $this->conn->query($query)->fetchAll();
    }

    public function getSoftwareOptions(): array
    {
        return $this->conn
            ->query("SELECT id, name, vendor FROM software_titles ORDER BY name ASC, vendor ASC")
            ->fetchAll();
    }

    public function getOsStats(): array
    {
        $stmt = $this->conn->prepare(
            "SELECT os_type, COUNT(*) AS total
             FROM software_assets
             GROUP BY os_type
             ORDER BY total DESC, os_type ASC"
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function addAsset(int $softwareId, string $version, string $osType, string $downloadUrl): int
    {
        $version = trim($version);
        $downloadUrl = trim($downloadUrl);

        if (!$this->softwareExists($softwareId)) {
            throw new InvalidArgumentException('Phần mềm được chọn không tồn tại.');
        }

        if ($version === '' || mb_strlen($version) > 80) {
            throw new InvalidArgumentException('Phiên bản phần mềm phải có từ 1 đến 80 ký tự.');
        }

        if (!in_array($osType, ['Windows', 'macOS', 'Linux', 'Web'], true)) {
            throw new InvalidArgumentException('Hệ điều hành không hợp lệ.');
        }

        $urlScheme = strtolower((string)parse_url($downloadUrl, PHP_URL_SCHEME));
        if (!filter_var($downloadUrl, FILTER_VALIDATE_URL)
            || !in_array($urlScheme, ['http', 'https'], true)
            || mb_strlen($downloadUrl) > 500) {
            throw new InvalidArgumentException('Link tải phải là URL hợp lệ và không vượt quá 500 ký tự.');
        }

        if ($this->assetExists($softwareId, $version, $osType)) {
            throw new InvalidArgumentException('Asset cho phần mềm, phiên bản và hệ điều hành này đã tồn tại.');
        }

        $stmt = $this->conn->prepare(
            "INSERT INTO software_assets (software_id, version, os_type, download_url)
             VALUES (:software_id, :version, :os_type, :download_url)"
        );
        $stmt->execute([
            ':software_id' => $softwareId,
            ':version' => $version,
            ':os_type' => $osType,
            ':download_url' => $downloadUrl,
        ]);

        return (int)$this->conn->lastInsertId();
    }

    public function deleteAsset(int $id): void
    {
        $exists = $this->conn->prepare("SELECT COUNT(*) FROM software_assets WHERE id = :id");
        $exists->execute([':id' => $id]);

        if ((int)$exists->fetchColumn() === 0) {
            throw new InvalidArgumentException('Asset không tồn tại hoặc đã bị xóa.');
        }

        $stmt = $this->conn->prepare("DELETE FROM software_assets WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    private function softwareExists(int $id): bool
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM software_titles WHERE id = :id");
        $stmt->execute([':id' => $id]);

        return (int)$stmt->fetchColumn() > 0;
    }

    private function assetExists(int $softwareId, string $version, string $osType): bool
    {
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*)
             FROM software_assets
             WHERE software_id = :software_id
               AND LOWER(version) = LOWER(:version)
               AND os_type = :os_type"
        );
        $stmt->execute([
            ':software_id' => $softwareId,
            ':version' => $version,
            ':os_type' => $osType,
        ]);

        return (int)$stmt->fetchColumn() > 0;
    }
}
