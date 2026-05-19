<?php
ob_start();
$emojiBase = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis';
$glassCard = 'bg-white/80 backdrop-blur-lg border border-white/50 shadow-lg rounded-2xl dark:bg-slate-950/60 dark:border-white/10';
$motionCard = $glassCard . ' transition-all duration-300 hover:-translate-y-1 hover:shadow-xl';
$buttonClass = 'inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition-all duration-300 hover:-translate-y-1 hover:bg-teal-600 hover:shadow-md dark:bg-white dark:text-slate-950';
$dangerButton = 'inline-flex h-9 w-9 items-center justify-center rounded-xl text-rose-600 transition-all duration-300 hover:-translate-y-1 hover:bg-rose-100 hover:shadow-md dark:text-rose-300 dark:hover:bg-rose-500/15';
$poolCount = count($pools);
$assetCount = count($assets);
$freeKeys = array_sum(array_map(static fn($item) => (int)$item['free_key_count'], $overview));
?>

<section class="<?= e($glassCard) ?> relative overflow-hidden p-6">
    <div class="pointer-events-none absolute -right-12 -top-16 h-52 w-52 rounded-full bg-emerald-300/25 blur-3xl"></div>
    <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.28em] text-emerald-600 dark:text-emerald-300">Inventory & Download Center</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">Kho license vận hành</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Tạo pool, nhập key chi tiết và quản lý link cài đặt theo hệ điều hành trong cùng một luồng kiểm soát.</p>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $poolCount ?></p>
                <p class="text-xs text-slate-500">Pool</p>
            </div>
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $freeKeys ?></p>
                <p class="text-xs text-slate-500">Key trống</p>
            </div>
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $assetCount ?></p>
                <p class="text-xs text-slate-500">Assets</p>
            </div>
        </div>
    </div>
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-3">
    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_pool">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Package.png') ?>" alt="Pool 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-white">Tạo pool license</h3>
                <p class="text-sm text-slate-500">Kho tổng trước khi nhập key</p>
            </div>
        </div>
        <select class="input-shell rounded-xl" name="software_id" required>
            <?php foreach ($softwares as $software): ?>
                <option value="<?= (int)$software['id'] ?>"><?= e($software['name']) ?> · <?= e($software['vendor']) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="input-shell mt-3 rounded-xl" type="number" name="total_quantity" min="1" placeholder="Tổng số license mua" required>
        <label class="mt-3 block text-sm font-medium text-slate-700 dark:text-slate-200">Ngày mua</label>
        <input class="input-shell mt-2 rounded-xl" type="date" name="purchase_date" value="<?= e(date('Y-m-d')) ?>" required>
        <label class="mt-3 block text-sm font-medium text-slate-700 dark:text-slate-200">Ngày hết hạn</label>
        <input class="input-shell mt-2 rounded-xl" type="date" name="expires_at">
        <label class="mt-4 flex items-center gap-3 rounded-2xl bg-white/50 p-3 text-sm font-medium text-slate-700 shadow-sm backdrop-blur dark:bg-white/10 dark:text-slate-200">
            <input type="checkbox" name="reusable_after_revocation" checked class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
            Cho phép trả key về kho khi thu hồi
        </label>
        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Tạo pool</button>
    </form>

    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_keys">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Key.png') ?>" alt="Key 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-white">Nhập key chi tiết</h3>
                <p class="text-sm text-slate-500">Mỗi dòng là một serial</p>
            </div>
        </div>
        <select class="input-shell rounded-xl" name="pool_id" required>
            <?php foreach ($pools as $pool): ?>
                <?php $remaining = max(0, (int)$pool['total_quantity'] - (int)$pool['key_count']); ?>
                <option value="<?= (int)$pool['id'] ?>">#<?= (int)$pool['id'] ?> · <?= e($pool['software_name']) ?> · còn chỗ <?= $remaining ?></option>
            <?php endforeach; ?>
        </select>
        <textarea class="input-shell mt-3 min-h-40 rounded-xl" name="keys" placeholder="MATLAB-2026-AAAA-BBBB&#10;MATLAB-2026-CCCC-DDDD" required></textarea>
        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Nhập key</button>
    </form>

    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_asset">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Symbols/Down%20Arrow.png') ?>" alt="Download 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-white">Link cài đặt</h3>
                <p class="text-sm text-slate-500">Download center theo OS</p>
            </div>
        </div>
        <select class="input-shell rounded-xl" name="software_id" required>
            <?php foreach ($softwares as $software): ?>
                <option value="<?= (int)$software['id'] ?>"><?= e($software['name']) ?> · <?= e($software['vendor']) ?></option>
            <?php endforeach; ?>
        </select>
        <input class="input-shell mt-3 rounded-xl" type="text" name="version" placeholder="Phiên bản, ví dụ R2026a" required>
        <select class="input-shell mt-3 rounded-xl" name="os_type" required>
            <option value="Windows">Windows</option>
            <option value="macOS">macOS</option>
            <option value="Linux">Linux</option>
            <option value="Web">Web</option>
        </select>
        <input class="input-shell mt-3 rounded-xl" type="url" name="download_url" placeholder="https://..." required>
        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Thêm link</button>
    </form>
</section>

<section class="<?= e($glassCard) ?> mt-5 overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Bar%20Chart.png') ?>" alt="">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Tổng quan tồn kho</h3>
                <p class="text-sm text-slate-500">Tổng hợp pool, key và link cài đặt theo phần mềm.</p>
            </div>
        </div>
        <input class="input-shell max-w-xs rounded-xl" data-table-filter="inventory-table" placeholder="Lọc tồn kho...">
    </div>
    <div class="overflow-x-auto">
        <table id="inventory-table" class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
            <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                <tr>
                    <th class="px-5 py-3 text-left">Phần mềm</th>
                    <th class="px-5 py-3 text-center">Pool</th>
                    <th class="px-5 py-3 text-center">Tổng license</th>
                    <th class="px-5 py-3 text-center">Key đã nhập</th>
                    <th class="px-5 py-3 text-center">Key trống</th>
                    <th class="px-5 py-3 text-center">Assets</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                <?php foreach ($overview as $item): ?>
                    <?php $total = max(1, (int)$item['total_quantity']); $percent = min(100, round(((int)$item['free_key_count'] / $total) * 100)); ?>
                    <tr class="table-row">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900 dark:text-white"><?= e($item['name']) ?></p>
                            <p class="text-xs text-slate-500"><?= e($item['vendor']) ?></p>
                        </td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$item['pool_count'] ?></td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$item['total_quantity'] ?></td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$item['key_count'] ?></td>
                        <td class="px-5 py-4 text-center">
                            <div class="mx-auto h-2 w-28 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                                <div class="h-full rounded-full bg-teal-500 transition-all duration-700" style="width: <?= $percent ?>%"></div>
                            </div>
                            <span class="mt-1 block text-xs text-slate-500"><?= (int)$item['free_key_count'] ?> key</span>
                        </td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$item['asset_count'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-2">
    <div class="<?= e($glassCard) ?> overflow-hidden">
        <div class="flex items-center gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10">
            <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Package.png') ?>" alt="">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Pool license</h3>
                <p class="text-sm text-slate-500">Pool ghi số lượng tổng; key thật nằm ở license_keys.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
                <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                    <tr>
                        <th class="px-5 py-3 text-left">Pool</th>
                        <th class="px-5 py-3 text-center">Key</th>
                        <th class="px-5 py-3 text-center">Còn</th>
                        <th class="px-5 py-3 text-left">Hết hạn</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                    <?php foreach ($pools as $pool): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-white">#<?= (int)$pool['id'] ?> · <?= e($pool['software_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($pool['vendor']) ?> · mua <?= format_date($pool['purchase_date']) ?></p>
                            </td>
                            <td class="px-5 py-4 text-center text-sm"><?= (int)$pool['key_count'] ?>/<?= (int)$pool['total_quantity'] ?></td>
                            <td class="px-5 py-4 text-center text-sm"><?= (int)$pool['free_key_count'] ?></td>
                            <td class="px-5 py-4 text-sm text-slate-500"><?= format_date($pool['expires_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="<?= e($glassCard) ?> overflow-hidden">
        <div class="flex items-center gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10">
            <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Symbols/Down%20Arrow.png') ?>" alt="">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Software assets</h3>
                <p class="text-sm text-slate-500">Link tải hiển thị khi người dùng nhận license.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
                <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                    <tr>
                        <th class="px-5 py-3 text-left">Asset</th>
                        <th class="px-5 py-3 text-center">OS</th>
                        <th class="px-5 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                    <?php foreach ($assets as $asset): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-white"><?= e($asset['software_name']) ?> <?= e($asset['version']) ?></p>
                                <a class="text-xs font-semibold text-teal-600 transition hover:text-teal-700 hover:underline dark:text-teal-300" href="<?= e($asset['download_url']) ?>" target="_blank" rel="noopener">Mở link tải</a>
                            </td>
                            <td class="px-5 py-4 text-center text-sm"><?= e($asset['os_type']) ?></td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" class="inline" data-confirm-submit data-confirm-message="Xóa link cài đặt này?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_asset">
                                    <input type="hidden" name="id" value="<?= (int)$asset['id'] ?>">
                                    <button class="<?= e($dangerButton) ?>" type="submit" title="Xóa link">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php
$content = ob_get_clean();
Layout::render('Kho license', 'inventory', $content, ['subtitle' => 'Inventory & Download Center']);
