<?php
declare(strict_types=1);

class Layout
{
    public static function render(string $title, string $activePage, string $content, array $options = []): void
    {
        $flash = flash_get();
        $subtitle = $options['subtitle'] ?? 'Software License Tracker';
        $bodyClass = $options['bodyClass'] ?? '';
        $extraScripts = $options['scripts'] ?? '';
        $emojiBase = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis';
        $shield3d = $emojiBase . '/Objects/Shield.png';
        $appJsPath = BASE_PATH . '/public/assets/app.js';
        $appJsVersion = is_file($appJsPath) ? (string)filemtime($appJsPath) : (string)time();
        $navItems = [
            ['page' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'layout-dashboard'],
            ['page' => 'admin', 'label' => 'Nền tảng', 'icon' => 'building-2'],
            ['page' => 'rules', 'label' => 'Luật cấp phát', 'icon' => 'workflow'],
            ['page' => 'inventory', 'label' => 'Kho license', 'icon' => 'boxes'],
            ['page' => 'allocations', 'label' => 'Cấp phát', 'icon' => 'key-round'],
        ];
        ?>
<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> | License Management</title>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        glow: '0 24px 80px rgba(20, 184, 166, .18)'
                    }
                }
            }
        };
        if (localStorage.getItem('license-theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root { color-scheme: light; }
        html, body { max-width: 100%; overflow-x: hidden; }
        .dark { color-scheme: dark; }
        body { background: #f8fafc; }
        .dark body { background: #020617; }
        .glass-panel {
            background: rgba(255, 255, 255, .80);
            border: 1px solid rgba(255, 255, 255, .50);
            backdrop-filter: blur(20px);
            box-shadow: 0 18px 50px rgba(15, 23, 42, .08);
        }
        .dark .glass-panel {
            background: rgba(15, 23, 42, .62);
            border-color: rgba(255, 255, 255, .10);
        }
        .dark .app-title {
            color: #fff !important;
        }
        .dark .app-content .text-white,
        .dark .app-content .dark\:text-white {
            color: #020617 !important;
        }
        .dark aside nav a,
        .dark aside nav a svg,
        .dark aside nav a i,
        .dark aside .dark-sidebar-text {
            color: #fff !important;
            stroke: #fff !important;
        }
        .dark aside nav a.bg-slate-950 {
            background: rgba(2, 6, 23, .92) !important;
            color: #fff !important;
        }
        .dark aside nav a:hover {
            color: #fff !important;
        }
        .dark .app-content button,
        .dark .app-content button *,
        .dark .app-content [role="button"],
        .dark .app-content [role="button"] * {
            color: #fff !important;
        }
        .dark .app-content button:not([data-page-target]):not([data-confirm-cancel]),
        .dark .app-content [role="button"] {
            background-color: #020617 !important;
            border-color: rgba(255, 255, 255, .14) !important;
        }
        .dark button[data-page-target] {
            background: rgba(15, 23, 42, .88) !important;
            border-color: rgba(255, 255, 255, .18) !important;
            color: #fff !important;
        }
        .dark .app-content button[data-page-target],
        .dark .app-content button[data-page-target] *,
        .dark .app-content select[data-page-target] {
            color: #fff !important;
        }
        .dark button[data-page-target][aria-current="page"] {
            background: #020617 !important;
            border-color: rgba(255, 255, 255, .28) !important;
            color: #fff !important;
            box-shadow: 0 12px 28px rgba(2, 6, 23, .45) !important;
        }
        .dark button[data-page-target]:disabled {
            background: rgba(15, 23, 42, .45) !important;
            color: #cbd5e1 !important;
            opacity: .65 !important;
        }
        .dark select[data-page-target] {
            background-color: #020617 !important;
            border-color: rgba(255, 255, 255, .18) !important;
            color: #fff !important;
        }
        .dark select[data-page-target] option {
            background-color: #020617 !important;
            color: #fff !important;
        }
        .dark [data-table-pager],
        .dark [data-table-pager] p,
        .dark [data-table-pager] span {
            color: #fff !important;
        }
        .input-shell {
            width: 100%;
            border-radius: .75rem;
            border: 1px solid rgba(148, 163, 184, .35);
            background: rgba(255, 255, 255, .86);
            padding: .7rem .9rem;
            color: #0f172a;
            outline: none;
            transition: border-color .2s ease, box-shadow .2s ease, background .2s ease;
        }
        .input-shell:focus {
            border-color: rgba(20, 184, 166, .78);
            box-shadow: 0 0 0 4px rgba(20, 184, 166, .13);
        }
        .dark .input-shell {
            background: rgba(15, 23, 42, .78);
            border-color: rgba(148, 163, 184, .24);
            color: #e5e7eb;
        }
        .table-row {
            transition: all .3s ease;
        }
        .table-row:hover {
            background: rgba(20, 184, 166, .06);
            transform: translateY(-.25rem);
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }
        .dark .table-row:hover {
            background: rgba(45, 212, 191, .08);
        }
    </style>
</head>
<body class="min-h-screen bg-slate-50 pb-24 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100 lg:pb-0 <?= e($bodyClass) ?>">
    <div class="pointer-events-none fixed -left-28 -top-28 -z-10 h-96 w-96 rounded-full bg-gradient-to-br from-teal-300 via-cyan-200 to-transparent opacity-40 blur-3xl dark:from-teal-500 dark:via-cyan-500"></div>
    <div class="pointer-events-none fixed right-[-8rem] top-24 -z-10 h-[28rem] w-[28rem] rounded-full bg-gradient-to-br from-indigo-300 via-violet-200 to-transparent opacity-40 blur-3xl dark:from-indigo-500 dark:via-violet-500"></div>
    <div class="pointer-events-none fixed bottom-[-10rem] left-1/3 -z-10 h-[26rem] w-[26rem] rounded-full bg-gradient-to-br from-emerald-200 via-sky-200 to-transparent opacity-35 blur-3xl dark:from-emerald-500 dark:via-sky-500"></div>
    <div class="mx-auto flex min-h-screen w-full max-w-[1500px] gap-5 px-4 py-4 sm:px-6 lg:px-8">
        <aside class="glass-panel sticky top-4 hidden h-[calc(100vh-2rem)] w-72 shrink-0 flex-col rounded-2xl p-4 shadow-glow lg:flex">
            <a href="<?= e(app_url('dashboard')) ?>" class="mb-6 flex items-center gap-3 rounded-2xl px-2 py-1 transition-all duration-300 hover:-translate-y-1 hover:shadow-md">
                <span class="grid h-12 w-12 place-items-center rounded-2xl bg-white/70 shadow-lg ring-1 ring-white/70 backdrop-blur dark:bg-white/10 dark:ring-white/10">
                    <img src="<?= e($shield3d) ?>" alt="LicenseOS Shield" class="h-9 w-9 object-contain drop-shadow-xl">
                </span>
                <span>
                    <span class="dark-sidebar-text block text-sm font-semibold uppercase tracking-[.24em] text-teal-600 dark:text-white">LicenseOS</span>
                    <span class="dark-sidebar-text block text-xs text-slate-500 dark:text-white">SaaS Control Center</span>
                </span>
            </a>

            <nav class="space-y-2">
                <?php foreach ($navItems as $item): ?>
                    <?php $isActive = $activePage === $item['page']; ?>
                    <a href="<?= e(app_url($item['page'])) ?>"
                       class="group flex items-center gap-3 rounded-2xl px-3 py-3 text-sm font-medium transition-all duration-300 hover:-translate-y-1 hover:shadow-md <?= $isActive ? 'bg-slate-950 text-white shadow-lg shadow-slate-950/15 dark:bg-slate-950 dark:text-white' : 'text-slate-600 hover:bg-white/70 hover:text-slate-950 dark:text-white dark:hover:bg-white/10 dark:hover:text-white' ?>">
                        <i data-lucide="<?= e($item['icon']) ?>" class="h-5 w-5"></i>
                        <?= e($item['label']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="dark-sidebar-text mt-auto rounded-2xl border border-teal-500/20 bg-teal-500/10 p-4 text-sm text-slate-600 shadow-lg backdrop-blur dark:text-white">
                <div class="dark-sidebar-text mb-2 flex items-center gap-2 font-semibold text-teal-700 dark:text-white">
                    <i data-lucide="sparkles" class="h-4 w-4"></i>
                    Smart guard
                </div>
                <p class="dark-sidebar-text">Transaction cấp key, prepared statements và audit log đang bảo vệ các thao tác nhạy cảm.</p>
            </div>
        </aside>

        <main class="min-w-0 flex-1">
            <header class="glass-panel sticky top-4 z-30 mb-5 flex items-center justify-between rounded-2xl px-4 py-3 shadow-lg">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[.24em] text-teal-600 dark:text-teal-300"><?= e($subtitle) ?></p>
                    <h1 class="app-title mt-1 truncate text-xl font-semibold text-slate-950 dark:text-white sm:text-2xl"><?= e($title) ?></h1>
                </div>
                <div class="flex items-center gap-2">
                    <a href="<?= e(app_url('dashboard')) ?>" class="grid h-10 w-10 place-items-center rounded-xl text-slate-600 transition-all duration-300 hover:-translate-y-1 hover:bg-slate-900 hover:text-white hover:shadow-md dark:text-slate-300 dark:hover:bg-white dark:hover:text-slate-950 lg:hidden" title="Dashboard">
                        <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                    </a>
                    <button type="button" data-theme-toggle class="grid h-10 w-10 place-items-center rounded-xl text-slate-600 transition-all duration-300 hover:-translate-y-1 hover:bg-slate-900 hover:text-white hover:shadow-md dark:text-slate-300 dark:hover:bg-white dark:hover:text-slate-950" title="Đổi giao diện sáng/tối">
                        <i data-lucide="moon" class="h-5 w-5 dark:hidden"></i>
                        <i data-lucide="sun" class="hidden h-5 w-5 dark:block"></i>
                    </button>
                </div>
            </header>

            <div class="app-content">
                <?= $content ?>
            </div>
        </main>
    </div>

    <nav class="glass-panel fixed inset-x-0 bottom-0 z-40 grid grid-cols-5 rounded-t-2xl p-1.5 shadow-xl lg:hidden">
        <?php foreach ($navItems as $item): ?>
            <?php $isActive = $activePage === $item['page']; ?>
            <a href="<?= e(app_url($item['page'])) ?>" class="flex min-w-0 flex-col items-center gap-1 rounded-xl px-1 py-2 text-[10px] font-semibold transition-all duration-300 hover:-translate-y-1 hover:shadow-md <?= $isActive ? 'bg-slate-950 text-white dark:bg-slate-950 dark:text-white' : 'text-slate-500 hover:bg-white/70 hover:text-slate-950 dark:text-white dark:hover:bg-white/10' ?>">
                <i data-lucide="<?= e($item['icon']) ?>" class="h-4 w-4"></i>
                <span class="max-w-full truncate"><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <div id="toast-root" class="fixed right-4 top-4 z-50 space-y-3"></div>
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/50 p-4 backdrop-blur-sm">
        <div class="w-full max-w-md rounded-2xl border border-white/50 bg-white/90 p-5 shadow-2xl backdrop-blur-xl dark:border-white/10 dark:bg-slate-900/90">
            <div class="flex items-start gap-3">
                <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-200">
                    <i data-lucide="triangle-alert" class="h-5 w-5"></i>
                </span>
                <div class="min-w-0">
                    <h2 id="confirm-title" class="text-lg font-semibold text-slate-950 dark:text-white">Xác nhận thao tác</h2>
                    <p id="confirm-body" class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-300"></p>
                </div>
            </div>
            <label id="confirm-reason-wrap" class="mt-4 hidden text-sm font-medium text-slate-700 dark:text-slate-200">
                Lý do thu hồi
                <textarea id="confirm-reason" class="input-shell mt-2 min-h-24" placeholder="Ví dụ: Hết nhu cầu sử dụng, chuyển khoa, vi phạm chính sách..."></textarea>
            </label>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" data-confirm-cancel class="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-600 transition-all duration-300 hover:-translate-y-1 hover:bg-slate-100 hover:shadow-md dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Hủy</button>
                <button type="button" data-confirm-accept class="rounded-xl bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-rose-600/20 transition-all duration-300 hover:-translate-y-1 hover:bg-rose-700 hover:shadow-md">Xác nhận</button>
            </div>
        </div>
    </div>

    <script>
        window.__FLASH__ = <?= json_encode($flash, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="assets/app.js?v=<?= e($appJsVersion) ?>"></script>
    <?= $extraScripts ?>
</body>
</html>
        <?php
    }
}
