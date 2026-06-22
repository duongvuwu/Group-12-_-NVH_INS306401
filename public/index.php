<?php
declare(strict_types=1);

session_start();

define('BASE_PATH', dirname(__DIR__));

require_once BASE_PATH . '/core/helpers.php';
require_once BASE_PATH . '/core/Layout.php';
require_once BASE_PATH . '/config/database.php';

spl_autoload_register(static function (string $class): void {
    $locations = [
        BASE_PATH . '/app/controllers/' . $class . '.php',
        BASE_PATH . '/app/models/' . $class . '.php',
        BASE_PATH . '/core/' . $class . '.php',
    ];

    foreach ($locations as $file) {
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});

$routes = [
    'dashboard' => DashboardController::class,
    'admin' => PlatformAdminController::class,
    'rules' => AllocationRuleController::class,
    'inventory' => InventoryController::class,
    'allocations' => LicenseAllocationController::class,
    
    // Đã thêm 2 module của bro vào Router
    'assets' => AssetController::class,
    'stats' => AssetController::class,
];

$page = $_GET['page'] ?? 'dashboard';

if (!isset($routes[$page])) {
    http_response_code(404);
    Layout::render('Không tìm thấy trang', 'dashboard', '
        <section class="glass-panel rounded-lg p-8 text-center">
            <p class="text-sm font-semibold uppercase tracking-[.2em] text-rose-500">404</p>
            <h2 class="mt-3 text-2xl font-semibold">Trang yêu cầu không tồn tại.</h2>
            <a href="' . e(app_url('dashboard')) . '" class="mt-6 inline-flex items-center rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-teal-600 dark:bg-white dark:text-slate-950">Về Dashboard</a>
        </section>
    ');
    exit;
}

try {
    $controllerClass = $routes[$page];
    $controller = new $controllerClass();
    $controller->index();
} catch (Throwable $exception) {
    http_response_code(500);
    $message = $exception instanceof RuntimeException || $exception instanceof InvalidArgumentException
        ? $exception->getMessage()
        : 'Đã có lỗi hệ thống. Vui lòng kiểm tra log máy chủ.';

    Layout::render('Hệ thống cần kiểm tra', $page, '
        <section class="glass-panel rounded-lg p-8">
            <div class="flex items-start gap-4">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-lg bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-200">
                    <i data-lucide="circle-alert" class="h-6 w-6"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[.2em] text-rose-500">Runtime guard</p>
                    <h2 class="mt-2 text-2xl font-semibold text-slate-950 dark:text-white">Không thể hoàn tất yêu cầu</h2>
                    <p class="mt-3 max-w-2xl text-slate-600 dark:text-slate-300">' . e($message) . '</p>
                </div>
            </div>
        </section>
    ');
}