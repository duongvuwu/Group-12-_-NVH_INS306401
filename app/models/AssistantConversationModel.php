<?php
declare(strict_types=1);

class AssistantConversationModel
{
    private PDO $conn;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
    }

    public function getOrCreate(string $token, string $language, array $metadata = []): array
    {
        $find = $this->conn->prepare(
            "SELECT id, session_token, language, started_at, last_activity_at
             FROM assistant_conversations
             WHERE session_token = :token"
        );
        $find->execute([':token' => $token]);
        $conversation = $find->fetch();

        if ($conversation) {
            $update = $this->conn->prepare(
                "UPDATE assistant_conversations
                 SET language = :language, metadata_json = :metadata
                 WHERE id = :id"
            );
            $update->execute([
                ':language' => $language,
                ':metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ':id' => $conversation['id'],
            ]);
            $conversation['language'] = $language;

            return $conversation;
        }

        $insert = $this->conn->prepare(
            "INSERT INTO assistant_conversations (session_token, language, metadata_json)
             VALUES (:token, :language, :metadata)"
        );
        $insert->execute([
            ':token' => $token,
            ':language' => $language,
            ':metadata' => json_encode($metadata, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return [
            'id' => (int)$this->conn->lastInsertId(),
            'session_token' => $token,
            'language' => $language,
            'started_at' => date('Y-m-d H:i:s'),
            'last_activity_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function addMessage(
        int $conversationId,
        string $sender,
        string $message,
        ?string $intent = null,
        ?array $response = null,
        string $status = 'Success',
        ?int $durationMs = null
    ): int {
        $stmt = $this->conn->prepare(
            "INSERT INTO assistant_messages
                (conversation_id, sender, intent, message_text, response_json, status, duration_ms)
             VALUES
                (:conversation_id, :sender, :intent, :message_text, :response_json, :status, :duration_ms)"
        );
        $stmt->execute([
            ':conversation_id' => $conversationId,
            ':sender' => $sender,
            ':intent' => $intent,
            ':message_text' => $message,
            ':response_json' => $response === null
                ? null
                : json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':status' => $status,
            ':duration_ms' => $durationMs,
        ]);

        $touch = $this->conn->prepare(
            "UPDATE assistant_conversations SET last_activity_at = CURRENT_TIMESTAMP WHERE id = :id"
        );
        $touch->execute([':id' => $conversationId]);

        return (int)$this->conn->lastInsertId();
    }

    public function getHistory(int $conversationId, int $limit = 40): array
    {
        $limit = max(1, min($limit, 100));
        $stmt = $this->conn->prepare(
            "SELECT id, sender, intent, message_text, response_json, status, duration_ms, created_at
             FROM (
                SELECT id, sender, intent, message_text, response_json, status, duration_ms, created_at
                FROM assistant_messages
                WHERE conversation_id = :conversation_id
                ORDER BY id DESC
                LIMIT {$limit}
             ) recent
             ORDER BY id ASC"
        );
        $stmt->execute([':conversation_id' => $conversationId]);
        $messages = $stmt->fetchAll();

        foreach ($messages as &$message) {
            $message['response'] = $message['response_json']
                ? json_decode($message['response_json'], true)
                : null;
            unset($message['response_json']);
        }
        unset($message);

        return $messages;
    }
}
