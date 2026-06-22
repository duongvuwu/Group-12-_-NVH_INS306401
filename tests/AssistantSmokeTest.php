<?php
declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));

require BASE_PATH . '/core/helpers.php';
require BASE_PATH . '/config/database.php';
require BASE_PATH . '/app/services/AssistantIntentResolver.php';
require BASE_PATH . '/app/models/LicenseAssistantModel.php';
require BASE_PATH . '/app/models/AssistantConversationModel.php';
require BASE_PATH . '/app/services/LicenseAssistantService.php';

function assertSameValue($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . ': expected ' . var_export($expected, true) . ', got ' . var_export($actual, true));
    }
}

$db = (new Database())->getConnection();
$resolver = new AssistantIntentResolver();
$service = new LicenseAssistantService(new LicenseAssistantModel($db), $resolver);

$intentCases = [
    'Hiện có bao nhiêu license đang active?' => 'active_license_count',
    'License nào hết hạn trong 14 ngày?' => 'expiring_licenses',
    'Có license quá hạn mà chưa thu hồi không?' => 'overdue_unrevoked',
    'Khoa nào sử dụng nhiều license nhất?' => 'top_departments',
    'Phần mềm nào sắp hết key?' => 'low_inventory',
    'Kiểm tra rủi ro hệ thống' => 'risk_summary',
    'Một câu hoàn toàn không liên quan' => 'no_match',
];

foreach ($intentCases as $question => $expectedIntent) {
    $resolved = $resolver->resolve($question);
    assertSameValue($expectedIntent, $resolved['intent'], 'Intent mismatch for ' . $question);
    $response = $service->answer($question, 'vi');
    assertSameValue($expectedIntent, $response['intent'], 'Service intent mismatch for ' . $question);
}

$expiredResponse = $service->answer('Hiện license quá hạn', 'vi');
$expectedExpired = (int)$db->query("SELECT COUNT(*) FROM license_allocations WHERE status = 'Expired'")->fetchColumn();
$expiredMetric = array_values(array_filter(
    $expiredResponse['metrics'],
    static fn(array $metric): bool => $metric['label'] === 'Đã hết hạn'
));
assertSameValue(1, count($expiredMetric), 'Expired metric missing');
assertSameValue($expectedExpired, (int)$expiredMetric[0]['value'], 'Expired license count mismatch');

$followUp = $resolver->resolve('30 ngày', ['intent' => 'expiring_licenses']);
assertSameValue('expiring_licenses', $followUp['intent'], 'Follow-up intent mismatch');
assertSameValue(30, $followUp['params']['days'], 'Follow-up day mismatch');

$englishCases = [
    'How many licenses are active?' => 'active_license_count',
    'Which department uses the most licenses?' => 'top_departments',
    'Which software is low on keys?' => 'low_inventory',
    'licenses for student@example.com' => 'user_licenses',
];
foreach ($englishCases as $question => $expectedIntent) {
    assertSameValue($expectedIntent, $resolver->resolve($question)['intent'], 'English intent mismatch');
}

$email = (string)$db->query('SELECT email FROM users ORDER BY id ASC LIMIT 1')->fetchColumn();
if ($email !== '') {
    $response = $service->answer('License của ' . $email, 'vi');
    assertSameValue('user_licenses', $response['intent'], 'User lookup intent mismatch');
}

$db->beginTransaction();
try {
    $conversationModel = new AssistantConversationModel($db);
    $conversation = $conversationModel->getOrCreate(bin2hex(random_bytes(16)), 'vi', ['test' => true]);
    $conversationModel->addMessage((int)$conversation['id'], 'User', 'Test message');
    $conversationModel->addMessage(
        (int)$conversation['id'],
        'Assistant',
        'Test response',
        'help',
        $service->welcome('vi'),
        'Success',
        1
    );
    $history = $conversationModel->getHistory((int)$conversation['id']);
    assertSameValue(2, count($history), 'Conversation history count mismatch');
} finally {
    $db->rollBack();
}

echo "Assistant smoke test passed.\n";
