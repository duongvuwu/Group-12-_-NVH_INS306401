<?php
declare(strict_types=1);

class LicenseAssistantController
{
    private AssistantConversationModel $conversations;
    private LicenseAssistantService $service;

    public function __construct()
    {
        $db = (new Database())->getConnection();
        $this->conversations = new AssistantConversationModel($db);
        $this->service = new LicenseAssistantService(
            new LicenseAssistantModel($db),
            new AssistantIntentResolver()
        );
    }

    public function index(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['ok' => false, 'message' => 'Method not allowed.'], 405);
            return;
        }

        try {
            require_csrf();
            $action = $_POST['action'] ?? 'ask';
            $language = ($_POST['language'] ?? 'vi') === 'en' ? 'en' : 'vi';

            if ($action === 'history') {
                $conversation = $this->conversation($language);
                $this->json([
                    'ok' => true,
                    'messages' => $this->conversations->getHistory((int)$conversation['id']),
                    'welcome' => $this->service->welcome($language),
                ]);
                return;
            }

            if ($action === 'reset') {
                unset($_SESSION['assistant_conversation_token'], $_SESSION['assistant_context']);
                $conversation = $this->conversation($language);
                $this->json([
                    'ok' => true,
                    'messages' => [],
                    'welcome' => $this->service->welcome($language),
                    'conversation_id' => (int)$conversation['id'],
                ]);
                return;
            }

            if ($action !== 'ask') {
                throw new InvalidArgumentException('Thao tác trợ lý không hợp lệ.');
            }

            $this->enforceRateLimit();
            $question = $this->cleanQuestion((string)($_POST['question'] ?? ''));
            $conversation = $this->conversation($language);
            $conversationId = (int)$conversation['id'];
            $this->conversations->addMessage($conversationId, 'User', $this->redactForHistory($question));

            $startedAt = hrtime(true);
            $response = $this->service->answer(
                $question,
                $language,
                is_array($_SESSION['assistant_context'] ?? null) ? $_SESSION['assistant_context'] : []
            );
            $durationMs = max(0, (int)round((hrtime(true) - $startedAt) / 1_000_000));

            $this->conversations->addMessage(
                $conversationId,
                'Assistant',
                $response['summary'],
                $response['intent'],
                $response,
                $response['status'],
                $durationMs
            );
            $_SESSION['assistant_context'] = ['intent' => $response['intent']];

            $this->json([
                'ok' => true,
                'message' => $response,
                'duration_ms' => $durationMs,
            ]);
        } catch (InvalidArgumentException | RuntimeException $exception) {
            $this->json(['ok' => false, 'message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            error_log('[LicenseAssistant] ' . $exception->getMessage());
            $this->json([
                'ok' => false,
                'message' => 'Trợ lý tạm thời không thể truy vấn dữ liệu. Vui lòng thử lại.',
            ], 500);
        }
    }

    private function conversation(string $language): array
    {
        $token = $_SESSION['assistant_conversation_token'] ?? '';
        if (!is_string($token) || !preg_match('/^[a-f0-9]{32}$/', $token)) {
            $token = bin2hex(random_bytes(16));
            $_SESSION['assistant_conversation_token'] = $token;
        }

        return $this->conversations->getOrCreate($token, $language, [
            'actor' => current_actor(),
            'source' => 'web_widget',
        ]);
    }

    private function cleanQuestion(string $question): string
    {
        $question = trim(preg_replace('/\s+/u', ' ', strip_tags($question)) ?? '');
        if ($question === '') {
            throw new InvalidArgumentException('Vui lòng nhập câu hỏi cho trợ lý.');
        }

        if (mb_strlen($question, 'UTF-8') > 500) {
            throw new InvalidArgumentException('Câu hỏi không được vượt quá 500 ký tự.');
        }

        return $question;
    }

    private function enforceRateLimit(): void
    {
        $now = time();
        $requests = array_values(array_filter(
            is_array($_SESSION['assistant_rate_limit'] ?? null) ? $_SESSION['assistant_rate_limit'] : [],
            static fn($timestamp): bool => is_int($timestamp) && $timestamp > $now - 60
        ));

        if (count($requests) >= 20) {
            throw new RuntimeException('Bạn đang gửi câu hỏi quá nhanh. Vui lòng chờ một phút.');
        }

        $requests[] = $now;
        $_SESSION['assistant_rate_limit'] = $requests;
    }

    private function redactForHistory(string $question): string
    {
        return preg_replace(
            '/\b(?:[A-Z0-9]{4,8}-){2,}[A-Z0-9]{4,8}\b/i',
            '[REDACTED-LICENSE-KEY]',
            $question
        ) ?? $question;
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
