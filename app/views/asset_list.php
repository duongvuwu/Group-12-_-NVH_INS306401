<?php
ob_start();
$emojiBase = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis';
$glassCard = 'bg-white/80 backdrop-blur-lg border border-white/50 shadow-lg rounded-2xl dark:bg-slate-950/60 dark:border-white/10';
$motionCard = $glassCard . ' transition-all duration-300 hover:-translate-y-1 hover:shadow-xl';
$buttonClass = 'primary-action inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition-all duration-300 hover:-translate-y-1 hover:bg-teal-600 hover:shadow-md dark:bg-teal-600 dark:text-white dark:shadow-teal-950/30 dark:hover:bg-teal-500';
$dangerButton = 'inline-flex h-9 w-9 items-center justify-center rounded-xl text-rose-600 transition-all duration-300 hover:-translate-y-1 hover:bg-rose-100 hover:shadow-md dark:text-rose-300 dark:hover:bg-rose-500/15';
$assetCount = count($assets);
$softwareWithAssets = count(array_unique(array_column($assets, 'software_id')));
?>

<section class="<?= e($glassCard) ?> relative overflow-hidden p-6">
    <div class="pointer-events-none absolute -right-14 -top-16 h-52 w-52 rounded-full bg-cyan-300/25 blur-3xl"></div>
    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.28em] text-teal-600 dark:text-teal-300">Software Delivery Hub</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-200">Download Center</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Quản lý phiên bản, hệ điều hành và link tải an toàn cho từng phần mềm đã được cấp license.</p>
        </div>
        <div class="grid grid-cols-2 gap-3 text-center">
            <div class="rounded-2xl bg-white/60 px-5 py-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $assetCount ?></p>
                <p class="text-xs text-slate-500">Assets</p>
            </div>
            <div class="rounded-2xl bg-white/60 px-5 py-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $softwareWithAssets ?></p>
                <p class="text-xs text-slate-500">Phần mềm có link</p>
            </div>
        </div>
    </div>
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-[.9fr_1.1fr]">
    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Symbols/Down%20Arrow.png') ?>" alt="">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-slate-200">Thêm tài nguyên cài đặt</h3>
                <p class="text-sm text-slate-500">Một phiên bản cho từng hệ điều hành</p>
            </div>
        </div>

        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Phần mềm</label>
        <select class="input-shell mt-2 rounded-xl" name="software_id" required>
            <?php foreach ($softwares as $software): ?>
                <option value="<?= (int)$software['id'] ?>"><?= e($software['name']) ?> · <?= e($software['vendor']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
                Phiên bản
                <input class="input-shell mt-2 rounded-xl" type="text" name="version" maxlength="80" placeholder="Ví dụ: R2026a" required>
            </label>
            <label class="text-sm font-medium text-slate-700 dark:text-slate-200">
                Hệ điều hành
                <select class="input-shell mt-2 rounded-xl" name="os_type" required>
                    <option value="Windows">Windows</option>
                    <option value="macOS">macOS</option>
                    <option value="Linux">Linux</option>
                    <option value="Web">Web</option>
                </select>
            </label>
        </div>

        <label class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Download URL</label>
        <input class="input-shell mt-2 rounded-xl" type="url" name="download_url" maxlength="500" placeholder="https://drive.google.com/..." required>

        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Thêm tài nguyên</button>
    </form>

    <article class="<?= e($motionCard) ?> p-5">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.24em] text-indigo-600 dark:text-indigo-300">OS Distribution</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-950 dark:text-slate-200">Tài nguyên theo hệ điều hành</h3>
                <p class="mt-1 text-sm text-slate-500">Biểu đồ cập nhật trực tiếp từ software_assets.</p>
            </div>
            <img class="h-12 w-12 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Laptop.png') ?>" alt="">
        </div>
        <div class="mt-5 h-72">
            <canvas id="assetOsChart"></canvas>
        </div>
    </article>
</section>

<section class="<?= e($glassCard) ?> mt-5 overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-3">
            <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Package.png') ?>" alt="">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-slate-200">Danh sách tài nguyên</h3>
                <p class="text-sm text-slate-500">Tìm kiếm, sao chép hoặc xuất toàn bộ link cài đặt.</p>
            </div>
        </div>
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input class="input-shell max-w-xs rounded-xl" data-table-filter="assets-table" placeholder="Tìm phần mềm, phiên bản, OS...">
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="export">
                <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-emerald-600/15 transition-all duration-300 hover:-translate-y-1 hover:bg-emerald-700 hover:shadow-md sm:w-auto">
                    <i data-lucide="file-down" class="h-4 w-4"></i>
                    Xuất CSV
                </button>
            </form>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="assets-table" data-page-size="10" class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
            <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                <tr>
                    <th class="px-5 py-3 text-left">Phần mềm</th>
                    <th class="px-5 py-3 text-left">Phiên bản</th>
                    <th class="px-5 py-3 text-center">OS</th>
                    <th class="px-5 py-3 text-left">Link tải</th>
                    <th class="px-5 py-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                <?php if ($assets): ?>
                    <?php foreach ($assets as $asset): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-slate-200"><?= e($asset['software_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($asset['vendor']) ?></p>
                            </td>
                            <td class="px-5 py-4 text-sm"><?= e($asset['version']) ?></td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-xl bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"><?= e($asset['os_type']) ?></span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="<?= e($asset['download_url']) ?>" target="_blank" rel="noopener" class="max-w-xs truncate text-sm font-semibold text-teal-600 hover:underline dark:text-teal-300"><?= e($asset['download_url']) ?></a>
                                    <button type="button" data-copy-text="<?= e($asset['download_url']) ?>" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-slate-200 text-slate-500 transition hover:-translate-y-1 hover:border-teal-300 hover:text-teal-600 dark:border-white/10 dark:text-slate-300" title="Sao chép link">
                                        <i data-lucide="copy" class="h-4 w-4"></i>
                                    </button>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" class="inline" data-confirm-submit data-confirm-message="Xóa tài nguyên cài đặt này?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$asset['id'] ?>">
                                    <button class="<?= e($dangerButton) ?>" type="submit" title="Xóa">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Chưa có tài nguyên cài đặt.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="border-t border-white/60 px-5 py-3 dark:border-white/10" data-table-pager="assets-table"></div>
</section>

<?php
$content = ob_get_clean();
$scripts = '<script>
window.assetOsStats = ' . json_encode($osStats, JSON_UNESCAPED_UNICODE) . ';
window.renderAssetOsChart && window.renderAssetOsChart();
</script>';

Layout::render('Download Center', 'assets', $content, [
    'subtitle' => 'Software Assets & Delivery',
    'scripts' => $scripts,
]);
