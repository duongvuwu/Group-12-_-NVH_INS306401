<?php
ob_start();
$emojiBase = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis';
$glassCard = 'bg-white/80 backdrop-blur-lg border border-white/50 shadow-lg rounded-2xl dark:bg-slate-950/60 dark:border-white/10';
$motionCard = $glassCard . ' transition-all duration-300 hover:-translate-y-1 hover:shadow-xl';
$buttonClass = 'inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition-all duration-300 hover:-translate-y-1 hover:bg-teal-600 hover:shadow-md dark:bg-white dark:text-slate-950';
$actionButton = 'inline-flex h-9 w-9 items-center justify-center rounded-xl transition-all duration-300 hover:-translate-y-1 hover:shadow-md';
$activeCount = count(array_filter($allocations, static fn($row) => $row['status'] === 'Active'));
$expiredCount = count(array_filter($allocations, static fn($row) => $row['status'] === 'Expired'));
$revokedCount = count(array_filter($allocations, static fn($row) => $row['status'] === 'Revoked'));
?>

<section class="<?= e($glassCard) ?> relative overflow-hidden p-6">
    <div class="pointer-events-none absolute -right-12 -top-16 h-52 w-52 rounded-full bg-teal-300/25 blur-3xl"></div>
    <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.28em] text-teal-600 dark:text-teal-300">Transaction-safe Allocation</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">Cấp phát license</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Kiểm tra luật, rút key còn trống bằng transaction, ghi nhận kích hoạt và thu hồi key theo vòng đời license.</p>
        </div>
        <img class="h-24 w-24 drop-shadow-2xl transition-all duration-300 hover:-translate-y-1 hover:scale-105" src="<?= e($emojiBase . '/Objects/Key.png') ?>" alt="Key 3D">
    </div>
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-[.8fr_1.2fr]">
    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="request">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Key.png') ?>" alt="Cấp phát 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-white">Cấp phát license</h3>
                <p class="text-sm text-slate-500">Luật hợp lệ mới được rút key</p>
            </div>
        </div>

        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Người dùng</label>
        <select class="input-shell mt-2 rounded-xl" name="user_id" required>
            <?php foreach ($users as $user): ?>
                <option value="<?= (int)$user['id'] ?>"><?= e($user['full_name']) ?> · <?= e($user['department_name']) ?> · <?= e(role_label($user['role'])) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Phần mềm</label>
        <select class="input-shell mt-2 rounded-xl" name="software_id" required>
            <?php foreach ($softwares as $software): ?>
                <option value="<?= (int)$software['id'] ?>"><?= e($software['name']) ?> · <?= (int)$software['available_quantity'] ?> key trống</option>
            <?php endforeach; ?>
        </select>

        <label class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Thời hạn</label>
        <div class="mt-2 grid grid-cols-3 gap-2">
            <?php foreach ([30 => '30 ngày', 180 => '180 ngày', 365 => '365 ngày'] as $days => $label): ?>
                <label class="cursor-pointer rounded-2xl border border-white/60 bg-white/65 p-3 text-center text-sm font-semibold shadow-sm backdrop-blur transition-all duration-300 hover:-translate-y-1 hover:border-teal-400 hover:shadow-md dark:border-white/10 dark:bg-white/10">
                    <input class="sr-only peer" type="radio" name="duration_days" value="<?= $days ?>" <?= $days === 365 ? 'checked' : '' ?>>
                    <span class="peer-checked:text-teal-600 dark:peer-checked:text-teal-300"><?= e($label) ?></span>
                </label>
            <?php endforeach; ?>
        </div>

        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Cấp phát ngay</button>
    </form>

    <div class="grid gap-4 sm:grid-cols-3">
        <article class="<?= e($motionCard) ?> p-5">
            <img class="h-12 w-12 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Shield.png') ?>" alt="">
            <p class="mt-4 text-3xl font-semibold"><?= $activeCount ?></p>
            <p class="text-sm text-slate-500">Đang active</p>
        </article>
        <article class="<?= e($motionCard) ?> p-5">
            <img class="h-12 w-12 drop-shadow-xl" src="<?= e($emojiBase . '/Travel%20and%20places/Alarm%20Clock.png') ?>" alt="">
            <p class="mt-4 text-3xl font-semibold"><?= $expiredCount ?></p>
            <p class="text-sm text-slate-500">Hết hạn</p>
        </article>
        <article class="<?= e($motionCard) ?> p-5">
            <img class="h-12 w-12 drop-shadow-xl" src="<?= e($emojiBase . '/Symbols/Counterclockwise%20Arrows%20Button.png') ?>" alt="">
            <p class="mt-4 text-3xl font-semibold"><?= $revokedCount ?></p>
            <p class="text-sm text-slate-500">Đã thu hồi</p>
        </article>
        <div class="<?= e($glassCard) ?> p-5 sm:col-span-3">
            <div class="flex items-start gap-4">
                <img class="h-12 w-12 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Locked.png') ?>" alt="">
                <p class="text-sm leading-6 text-slate-600 dark:text-slate-300">Luồng cấp phát dùng prepared statements, transaction và <span class="font-mono text-xs">FOR UPDATE</span>. Khi thu hồi license reusable, key được trả về kho và số lượng còn trống được đồng bộ từ dữ liệu thật.</p>
            </div>
        </div>
    </div>
</section>

<section class="<?= e($glassCard) ?> mt-5 overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Clipboard.png') ?>" alt="">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Lịch sử cấp phát</h3>
                <p class="text-sm text-slate-500">Theo dõi key, kích hoạt, hết hạn và thu hồi.</p>
            </div>
        </div>
        <input class="input-shell max-w-xs rounded-xl" data-table-filter="allocations-table" placeholder="Lọc cấp phát...">
    </div>
    <div class="overflow-x-auto">
        <table id="allocations-table" class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
            <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                <tr>
                    <th class="px-5 py-3 text-left">Người dùng</th>
                    <th class="px-5 py-3 text-left">Phần mềm</th>
                    <th class="px-5 py-3 text-left">Key</th>
                    <th class="px-5 py-3 text-left">Thời hạn</th>
                    <th class="px-5 py-3 text-center">Trạng thái</th>
                    <th class="px-5 py-3 text-center">Kích hoạt</th>
                    <th class="px-5 py-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                <?php if ($allocations): ?>
                    <?php foreach ($allocations as $allocation): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-white"><?= e($allocation['full_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($allocation['department_name']) ?> · <?= e(role_label($allocation['role'])) ?></p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-white"><?= e($allocation['software_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($allocation['available_assets'] ?: 'Chưa có asset') ?></p>
                            </td>
                            <td class="px-5 py-4 font-mono text-sm text-slate-600 dark:text-slate-300"><?= e(mask_key($allocation['key_value'])) ?></td>
                            <td class="px-5 py-4 text-sm text-slate-600 dark:text-slate-300"><?= format_date($allocation['start_date']) ?> → <?= format_date($allocation['end_date']) ?></td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-xl px-2.5 py-1 text-xs font-semibold ring-1 <?= e(status_badge_class($allocation['status'])) ?>"><?= e(status_label($allocation['status'])) ?></span>
                            </td>
                            <td class="px-5 py-4 text-center text-sm"><?= (int)$allocation['activation_count'] ?></td>
                            <td class="px-5 py-4 text-right">
                                <?php if ($allocation['status'] === 'Active'): ?>
                                    <form method="POST" class="inline" data-confirm-submit data-confirm-message="Ghi nhận một lượt kích hoạt cho license này?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="activate">
                                        <input type="hidden" name="id" value="<?= (int)$allocation['id'] ?>">
                                        <button class="<?= e($actionButton) ?> text-teal-600 hover:bg-teal-100 dark:text-teal-300 dark:hover:bg-teal-500/15" type="submit" title="Ghi nhận kích hoạt">●</button>
                                    </form>
                                    <form method="POST" class="inline" data-confirm-submit data-confirm-reason data-confirm-message="Thu hồi license này? Nếu pool cho phép tái sử dụng, key sẽ được trả lại kho.">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="revoke">
                                        <input type="hidden" name="id" value="<?= (int)$allocation['id'] ?>">
                                        <button class="<?= e($actionButton) ?> text-rose-600 hover:bg-rose-100 dark:text-rose-300 dark:hover:bg-rose-500/15" type="submit" title="Thu hồi">↺</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-xs text-slate-400">Đã đóng</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-sm text-slate-500">Chưa có giao dịch cấp phát.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
$content = ob_get_clean();
Layout::render('Cấp phát license', 'allocations', $content, ['subtitle' => 'Transaction-safe Allocation']);
