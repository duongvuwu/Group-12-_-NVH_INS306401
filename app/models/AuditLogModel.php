<?php
declare(strict_types=1);

class AuditLogModel
{
    private $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function record(string $action, string $entityType, ?int $entityId = null, array $context = []): void
    {
        try {
            $query = "INSERT INTO audit_logs (actor, action, entity_type, entity_id, context_json, ip_address)
                      VALUES (:actor, :action, :entity_type, :entity_id, :context_json, :ip_address)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':actor' => current_actor(),
                ':action' => $action,
                ':entity_type' => $entityType,
                ':entity_id' => $entityId,
                ':context_json' => json_encode($context, JSON_UNESCAPED_UNICODE),
                ':ip_address' => request_ip(),
            ]);
        } catch (PDOException $exception) {
            error_log('[AuditLog] ' . $exception->getMessage());
        }
    }

    public function recent(int $limit = 8): array
    {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM audit_logs ORDER BY created_at DESC, id DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        } catch (PDOException $exception) {
            error_log('[AuditLog] ' . $exception->getMessage());
            return [];
        }
    }
}
