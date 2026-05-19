<?php
ob_start();

$emojiBase = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis';
$inventoryLabels = array_map(static fn($item) => $item['name'], $inventoryChart);
$inventoryUsed = array_map(static fn($item) => (int)($item['used_keys'] ?? 0), $inventoryChart);
$inventoryAvailable = array_map(static fn($item) => (int)($item['available_keys'] ?? 0), $inventoryChart);
$departmentLabels = array_map(static fn($item) => $item['department_name'], $departmentUsage);
$departmentValues = array_map(static fn($item) => (int)$item['active_count'], $departmentUsage);

$cards = [
    [
        'label' => 'Khoa/Phòng ban',
        'value' => (int)($stats['departments'] ?? 0),
        'hint' => 'Đơn vị đang được quản lý',
        'asset' => $emojiBase . '/Travel%20and%20places/School.png',
        'accent' => 'from-teal-500/20 to-cyan-500/10',
    ],
    [
        'label' => 'Tổng số Key',
        'value' => (int)($stats['total_keys'] ?? 0),
        'hint' => 'Serial đã nhập vào kho',
        'asset' => $emojiBase . '/Objects/Key.png',
        'accent' => 'from-amber-500/20 to-orange-500/10',
    ],
    [
        'label' => 'Key còn trống',
        'value' => (int)($stats['available_keys'] ?? 0),
        'hint' => 'Sẵn sàng cấp phát',
        'asset' => $emojiBase . '/Objects/Open%20Mailbox%20with%20Raised%20Flag.png',
        'accent' => 'from-emerald-500/20 to-lime-500/10',
    ],
    [
        'label' => 'License active',
        'value' => (int)($stats['active_allocations'] ?? 0),
        'hint' => 'Đang gắn cho người dùng',
        'asset' => $emojiBase . '/Objects/Shield.png',
        'accent' => 'from-indigo-500/20 to-sky-500/10',
    ],
    [
        'label' => 'Phần mềm',
        'value' => (int)($stats['softwares'] ?? 0),
        'hint' => 'Danh mục phần mềm bản quyền',
        'asset' => $emojiBase . '/Objects/Laptop.png',
        'accent' => 'from-violet-500/20 to-fuchsia-500/10',
    ],
    [
        'label' => 'Tỷ lệ sử dụng',
        'value' => e(($stats['usage_rate'] ?? 0) . '%'),
        'hint' => 'Key đã cấp trên tổng key',
        'asset' => $emojiBase . '/Objects/Bar%20Chart.png',
        'accent' => 'from-rose-500/20 to-pink-500/10',
    ],
];
?>

<section class="relative overflow-hidden rounded-lg border border-white/40 bg-white/65 p-6 shadow-sm backdrop-blur-2xl dark:border-white/10 dark:bg-slate-950/50">
    <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-teal-300/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 left-1/3 h-56 w-56 rounded-full bg-indigo-300/25 blur-3xl"></div>
    <div class="relative grid gap-6 lg:grid-cols-[1.15fr_.85fr] lg:items-center">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.28em] text-teal-600 dark:text-teal-300">Next-gen SaaS Operations</p>
            <h2 class="mt-3 text-3xl font-semibold tracking-normal text-slate-950 dark:text-white sm:text-4xl">Dashboard vận hành License</h2>
            <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">
                Theo dõi tồn kho key, license đang dùng, nhu cầu theo khoa và cảnh báo hết hạn trong một màn hình điều phối gọn, mượt, đúng chuẩn XAMPP localhost.
            </p>
        </div>
        <div class="relative mx-auto h-40 w-40 lg:h-52 lg:w-52">
            <div class="absolute inset-0 rounded-full bg-gradient-to-br from-teal-400/40 via-indigo-400/30 to-white/30 blur-2xl"></div>
            <img class="relative h-full w-full drop-shadow-2xl transition-all duration-500 hover:-translate-y-2 hover:scale-105" src="<?= e($emojiBase . '/Objects/Bar%20Chart.png') ?>" alt="Biểu đồ 3D">
        </div>
    </div>
</section>

<section class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
    <?php foreach ($cards as $card): ?>
        <article class="group relative overflow-hidden rounded-lg border border-white/45 bg-white/70 p-5 shadow-sm backdrop-blur-2xl transition-all duration-300 hover:-translate-y-1 hover:scale-105 hover:shadow-xl dark:border-white/10 dark:bg-slate-950/55">
            <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-br <?= e($card['accent']) ?>"></div>
            <div class="relative flex items-center justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-500 dark:text-slate-400"><?= e($card['label']) ?></p>
                    <p class="mt-2 text-4xl font-semibold text-slate-950 dark:text-white"><?= e($card['value']) ?></p>
                    <p class="mt-2 text-xs text-slate-500 dark:text-slate-400"><?= e($card['hint']) ?></p>
                </div>
                <div class="grid h-16 w-16 shrink-0 place-items-center rounded-lg bg-white/65 shadow-sm ring-1 ring-white/70 backdrop-blur-xl transition-all duration-300 group-hover:rotate-3 group-hover:scale-110 dark:bg-white/10 dark:ring-white/10">
                    <img class="h-12 w-12 object-contain drop-shadow-lg" src="<?= e($card['asset']) ?>" alt="<?= e($card['label']) ?>">
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-[1.45fr_.95fr]">
    <article class="rounded-lg border border-white/45 bg-white/70 p-5 shadow-sm backdrop-blur-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-slate-950/55">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.24em] text-teal-600 dark:text-teal-300">Smooth Charts</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">Tồn kho theo phần mềm</h3>
            </div>
            <img class="h-12 w-12 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Key.png') ?>" alt="Key 3D">
        </div>
        <div class="mt-5 h-80">
            <canvas id="inventoryChart"></canvas>
        </div>
    </article>

    <article class="rounded-lg border border-white/45 bg-white/70 p-5 shadow-sm backdrop-blur-2xl transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-white/10 dark:bg-slate-950/55">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.24em] text-indigo-600 dark:text-indigo-300">Department Demand</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">Nhu cầu theo khoa</h3>
            </div>
            <img class="h-12 w-12 drop-shadow-lg" src="<?= e($emojiBase . '/Travel%20and%20places/School.png') ?>" alt="Khoa 3D">
        </div>
        <div class="mt-5 h-80">
            <canvas id="departmentChart"></canvas>
        </div>
    </article>
</section>

<section class="mt-5 rounded-lg border border-white/45 bg-white/70 shadow-sm backdrop-blur-2xl transition-all duration-300 hover:shadow-xl dark:border-white/10 dark:bg-slate-950/55">
    <div class="flex flex-col gap-3 border-b border-slate-200/70 px-5 py-4 dark:border-slate-700/60 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.24em] text-amber-600 dark:text-amber-300">Lifecycle Watch</p>
            <h3 class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">License sắp hết hạn trong 14 ngày</h3>
        </div>
        <img class="h-11 w-11 drop-shadow-lg" src="<?= e($emojiBase . '/Travel%20and%20places/Alarm%20Clock.png') ?>" alt="Cảnh báo 3D">
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
            <thead class="bg-white/35 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40 dark:text-slate-400">
                <tr>
                    <th class="px-5 py-3 text-left">Người dùng</th>
                    <th class="px-5 py-3 text-left">Phần mềm</th>
                    <th class="px-5 py-3 text-left">Khoa</th>
                    <th class="px-5 py-3 text-right">Còn lại</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                <?php if ($expiringAllocations): ?>
                    <?php foreach ($expiringAllocations as $row): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4 font-medium text-slate-900 dark:text-white"><?= e($row['full_name']) ?></td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300"><?= e($row['software_name']) ?></td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300"><?= e($row['department_name']) ?></td>
                            <td class="px-5 py-4 text-right">
                                <span class="rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700 dark:bg-amber-500/15 dark:text-amber-200"><?= (int)$row['days_left'] ?> ngày</span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-sm text-slate-500">Không có license sắp hết hạn.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php
$content = ob_get_clean();
$scripts = '<script>
window.dashboardCharts = {
    inventory: {
        labels: ' . json_encode($inventoryLabels, JSON_UNESCAPED_UNICODE) . ',
        used: ' . json_encode($inventoryUsed) . ',
        available: ' . json_encode($inventoryAvailable) . '
    },
    departments: {
        labels: ' . json_encode($departmentLabels, JSON_UNESCAPED_UNICODE) . ',
        values: ' . json_encode($departmentValues) . '
    }
};
window.renderDashboardCharts && window.renderDashboardCharts();
</script>';

Layout::render('Dashboard vận hành', 'dashboard', $content, [
    'subtitle' => 'Tổng quan SaaS license',
    'scripts' => $scripts,
]);
