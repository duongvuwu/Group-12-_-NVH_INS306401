<?php
declare(strict_types=1);

function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function app_url(string $page = 'dashboard', array $params = []): string
{
    return 'index.php?' . http_build_query(array_merge(['page' => $page], $params));
}

function redirect_to(string $page = 'dashboard', array $params = []): void
{
    header('Location: ' . app_url($page, $params));
    exit;
}

function flash_set(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect_with_flash(string $page, string $type, string $message, array $params = []): void
{
    flash_set($type, $message);
    redirect_to($page, $params);
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function require_csrf(): void
{
    $posted = $_POST['csrf_token'] ?? '';
    $stored = $_SESSION['csrf_token'] ?? '';

    if (!$posted || !$stored || !hash_equals($stored, $posted)) {
        throw new RuntimeException('Phiên biểu mẫu đã hết hạn. Vui lòng tải lại trang và thử lại.');
    }
}

function current_actor(): string
{
    return $_SESSION['admin_name'] ?? 'Platform Admin';
}

function request_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
}

function format_date(?string $value): string
{
    if (!$value) {
        return '-';
    }

    try {
        return (new DateTime($value))->format('d/m/Y');
    } catch (Exception $exception) {
        return $value;
    }
}

function format_datetime(?string $value): string
{
    if (!$value) {
        return '-';
    }

    try {
        return (new DateTime($value))->format('d/m/Y H:i');
    } catch (Exception $exception) {
        return $value;
    }
}

function role_label(string $role): string
{
    $labels = [
        'Student' => 'Sinh viên',
        'Teacher' => 'Giảng viên',
        'Admin' => 'Quản trị',
        'All' => 'Tất cả',
    ];

    return $labels[$role] ?? $role;
}

function status_label(string $status): string
{
    $labels = [
        'Active' => 'Đang dùng',
        'Expired' => 'Hết hạn',
        'Revoked' => 'Đã thu hồi',
    ];

    return $labels[$status] ?? $status;
}

function status_badge_class(string $status): string
{
    $classes = [
        'Active' => 'bg-emerald-100 text-emerald-700 ring-emerald-200 dark:bg-emerald-500/15 dark:text-emerald-200 dark:ring-emerald-400/20',
        'Expired' => 'bg-amber-100 text-amber-700 ring-amber-200 dark:bg-amber-500/15 dark:text-amber-200 dark:ring-amber-400/20',
        'Revoked' => 'bg-rose-100 text-rose-700 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-200 dark:ring-rose-400/20',
    ];

    return $classes[$status] ?? 'bg-slate-100 text-slate-700 ring-slate-200 dark:bg-slate-500/15 dark:text-slate-200 dark:ring-slate-400/20';
}

function mask_key(?string $key): string
{
    if (!$key) {
        return '-';
    }

    $plain = preg_replace('/\s+/', '', $key);
    if (strlen($plain) <= 8) {
        return str_repeat('•', max(strlen($plain), 4));
    }

    return substr($plain, 0, 4) . ' •••• •••• ' . substr($plain, -4);
}

function positive_int($value, string $fieldName): int
{
    $number = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if (!$number) {
        throw new InvalidArgumentException($fieldName . ' phải là số nguyên dương.');
    }

    return (int)$number;
}
