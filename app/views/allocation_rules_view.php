<?php
ob_start();
$emojiBase = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis';
$glassCard = 'bg-white/80 backdrop-blur-lg border border-white/50 shadow-lg rounded-2xl dark:bg-slate-950/60 dark:border-white/10';
$motionCard = $glassCard . ' transition-all duration-300 hover:-translate-y-1 hover:shadow-xl';
$buttonClass = 'inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition-all duration-300 hover:-translate-y-1 hover:bg-teal-600 hover:shadow-md dark:bg-slate-950 dark:text-white dark:hover:bg-teal-600';
$dangerButton = 'inline-flex h-9 w-9 items-center justify-center rounded-xl text-rose-600 transition-all duration-300 hover:-translate-y-1 hover:bg-rose-100 hover:shadow-md dark:text-rose-300 dark:hover:bg-rose-500/15';
$suggestionPayload = [];
foreach ($suggestionsByDepartment as $departmentId => $items) {
    $suggestionPayload[$departmentId] = array_map(static function ($item) {
        return [
            'id' => (int)$item['id'],
            'name' => $item['name'],
            'vendor' => $item['vendor'],
        ];
    }, $items);
}
?>

<section class="<?= e($glassCard) ?> relative overflow-hidden p-6">
    <div class="pointer-events-none absolute -right-12 -top-16 h-52 w-52 rounded-full bg-indigo-300/25 blur-3xl"></div>
    <div class="relative flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.28em] text-indigo-600 dark:text-indigo-300">Allocation Policy</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-white">Luật cấp phát thông minh</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Định nghĩa ai được dùng phần mềm nào theo khoa và vai trò; backend chặn trùng luật và chặn xóa luật đang có license active.</p>
        </div>
        <img class="h-24 w-24 drop-shadow-2xl transition-all duration-300 hover:-translate-y-1 hover:scale-105" src="<?= e($emojiBase . '/Objects/Clipboard.png') ?>" alt="Luật cấp phát 3D">
    </div>
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-[.85fr_1.15fr]">
    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Locked.png') ?>" alt="Policy 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-white">Tạo luật cấp phát</h3>
                <p class="text-sm text-slate-500">Phần mềm + khoa + vai trò là duy nhất</p>
            </div>
        </div>

        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Khoa/phòng ban</label>
        <select class="input-shell mt-2 rounded-xl" name="department_id" data-rule-department required>
            <?php foreach ($departments as $department): ?>
                <option value="<?= (int)$department['id'] ?>"><?= e($department['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Phần mềm</label>
        <select class="input-shell mt-2 rounded-xl" name="software_id" data-rule-software required>
            <?php foreach ($softwares as $software): ?>
                <option value="<?= (int)$software['id'] ?>"><?= e($software['name']) ?> · <?= e($software['vendor']) ?> · <?= (int)$software['available_quantity'] ?> key trống</option>
            <?php endforeach; ?>
        </select>

        <label class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Đối tượng</label>
        <select class="input-shell mt-2 rounded-xl" name="target_role" required>
            <option value="Student">Sinh viên</option>
            <option value="Teacher">Giảng viên</option>
            <option value="Admin">Quản trị</option>
            <option value="All">Tất cả</option>
        </select>

        <div class="mt-5 rounded-2xl border border-dashed border-teal-300 bg-teal-50/70 p-4 shadow-sm backdrop-blur dark:border-teal-500/30 dark:bg-teal-500/10">
            <div class="flex items-center gap-2 text-sm font-semibold text-teal-800 dark:text-teal-100">
                <img class="h-7 w-7" src="<?= e($emojiBase . '/Activities/Sparkles.png') ?>" alt="">
                Gợi ý tự động theo tên khoa
            </div>
            <div class="mt-3 flex flex-wrap gap-2" data-rule-suggestions>
                <span class="text-sm text-slate-500">Chọn khoa để xem gợi ý.</span>
            </div>
        </div>

        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Thêm luật cấp phát</button>
    </form>

    <div class="<?= e($motionCard) ?> p-5">
        <div class="flex items-start gap-4">
            <img class="h-16 w-16 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Shield.png') ?>" alt="Guard 3D">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[.24em] text-teal-600 dark:text-teal-300">Backend Guard</p>
                <h3 class="mt-1 text-lg font-semibold text-slate-950 dark:text-white">Luật đẹp nhưng dữ liệu phải chắc</h3>
                <p class="mt-2 text-sm leading-6 text-slate-600 dark:text-slate-300">Model dùng prepared statements, kiểm tra trùng tổ hợp, và không cho xóa luật khi vẫn còn allocation active phụ thuộc. Luồng cấp phát thực tế dùng transaction + khóa dòng key trống.</p>
            </div>
        </div>
        <div class="mt-5 grid gap-3 sm:grid-cols-3">
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-3xl font-semibold"><?= count($rules) ?></p>
                <p class="mt-1 text-sm text-slate-500">Luật hiện có</p>
            </div>
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-3xl font-semibold"><?= count($departments) ?></p>
                <p class="mt-1 text-sm text-slate-500">Khoa/phòng ban</p>
            </div>
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-3xl font-semibold"><?= count($softwares) ?></p>
                <p class="mt-1 text-sm text-slate-500">Phần mềm</p>
            </div>
        </div>
    </div>
</section>

<section class="<?= e($glassCard) ?> mt-5 overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Clipboard.png') ?>" alt="">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-white">Danh sách luật cấp phát</h3>
                <p class="text-sm text-slate-500">Quét nhanh quan hệ phần mềm - khoa - vai trò.</p>
            </div>
        </div>
        <input class="input-shell max-w-xs rounded-xl" data-table-filter="rules-table" placeholder="Lọc luật...">
    </div>
    <div class="overflow-x-auto">
        <table id="rules-table" class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
            <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                <tr>
                    <th class="px-5 py-3 text-left">Phần mềm</th>
                    <th class="px-5 py-3 text-left">Khoa/phòng ban</th>
                    <th class="px-5 py-3 text-center">Đối tượng</th>
                    <th class="px-5 py-3 text-center">Active</th>
                    <th class="px-5 py-3 text-left">Ngày tạo</th>
                    <th class="px-5 py-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                <?php if ($rules): ?>
                    <?php foreach ($rules as $rule): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-white"><?= e($rule['software_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($rule['vendor']) ?></p>
                            </td>
                            <td class="px-5 py-4 text-sm"><?= e($rule['department_name']) ?></td>
                            <td class="px-5 py-4 text-center">
                                <span class="rounded-xl bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-200"><?= e(role_label($rule['target_role'])) ?></span>
                            </td>
                            <td class="px-5 py-4 text-center text-sm"><?= (int)$rule['active_allocations'] ?></td>
                            <td class="px-5 py-4 text-sm text-slate-500"><?= format_datetime($rule['created_at']) ?></td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" class="inline" data-confirm-submit data-confirm-message="Xóa luật cấp phát này? Hệ thống sẽ chặn nếu còn license active phụ thuộc.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= (int)$rule['id'] ?>">
                                    <button class="<?= e($dangerButton) ?>" type="submit" title="Xóa luật">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-slate-500">Chưa có luật cấp phát.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php
$content = ob_get_clean();
$scripts = '<script>window.ruleSuggestions = ' . json_encode($suggestionPayload, JSON_UNESCAPED_UNICODE) . '; window.initRuleSuggestions && window.initRuleSuggestions();</script>';
Layout::render('Luật cấp phát', 'rules', $content, [
    'subtitle' => 'Allocation Rules Engine',
    'scripts' => $scripts,
]);
