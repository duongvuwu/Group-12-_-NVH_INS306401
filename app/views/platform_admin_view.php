<?php
ob_start();
$emojiBase = 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/master/Emojis';
$glassCard = 'bg-white/80 backdrop-blur-lg border border-white/50 shadow-lg rounded-2xl dark:bg-slate-950/60 dark:border-white/10';
$motionCard = $glassCard . ' transition-all duration-300 hover:-translate-y-1 hover:shadow-xl';
$buttonClass = 'primary-action inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-slate-950/15 transition-all duration-300 hover:-translate-y-1 hover:bg-teal-600 hover:shadow-md dark:bg-teal-600 dark:text-white dark:shadow-teal-950/30 dark:hover:bg-teal-500';
$dangerButton = 'inline-flex h-9 w-9 items-center justify-center rounded-xl text-rose-600 transition-all duration-300 hover:-translate-y-1 hover:bg-rose-100 hover:shadow-md dark:text-rose-300 dark:hover:bg-rose-500/15';
$departmentCount = count($data['departments']);
$userCount = count($data['users']);
$softwareCount = count($data['softwares']);
?>

<section class="<?= e($glassCard) ?> relative overflow-hidden p-6">
    <div class="pointer-events-none absolute -right-14 -top-16 h-52 w-52 rounded-full bg-teal-300/25 blur-3xl"></div>
    <div class="relative flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.28em] text-teal-600 dark:text-teal-300">Master Data Control</p>
            <h2 class="mt-2 text-3xl font-semibold text-slate-950 dark:text-slate-200">Nền tảng vận hành</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Quản lý khoa/phòng ban, người dùng và danh mục phần mềm trước khi bật luật cấp phát license.</p>
        </div>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $departmentCount ?></p>
                <p class="text-xs text-slate-500">Khoa</p>
            </div>
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $userCount ?></p>
                <p class="text-xs text-slate-500">Users</p>
            </div>
            <div class="rounded-2xl bg-white/60 p-4 shadow-sm backdrop-blur dark:bg-white/10">
                <p class="text-2xl font-semibold"><?= $softwareCount ?></p>
                <p class="text-xs text-slate-500">Apps</p>
            </div>
        </div>
    </div>
</section>

<section class="mt-5 grid gap-5 xl:grid-cols-3">
    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_dept">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Travel%20and%20places/School.png') ?>" alt="Khoa 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-slate-200">Khoa/phòng ban</h3>
                <p class="text-sm text-slate-500">Nền phân quyền theo đơn vị</p>
            </div>
        </div>
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Tên khoa</label>
        <input class="input-shell mt-2 rounded-xl" type="text" name="name" placeholder="Ví dụ: Khoa Công nghệ thông tin" required>
        <label class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Mô tả</label>
        <input class="input-shell mt-2 rounded-xl" type="text" name="description" placeholder="Ghi chú ngắn">
        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Thêm khoa</button>
    </form>

    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_user">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/People/Busts%20in%20Silhouette.png') ?>" alt="Người dùng 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-slate-200">Người dùng</h3>
                <p class="text-sm text-slate-500">Sinh viên, giảng viên, quản trị</p>
            </div>
        </div>
        <input class="input-shell rounded-xl" type="text" name="full_name" placeholder="Họ tên" required>
        <div class="mt-3 flex items-center overflow-hidden rounded-xl border border-slate-300/70 bg-white/80 shadow-sm transition-all duration-300 focus-within:border-teal-400 focus-within:ring-4 focus-within:ring-teal-100 dark:border-white/10 dark:bg-white/10 dark:focus-within:ring-teal-400/10">
            <input class="min-w-0 flex-1 bg-transparent px-4 py-3 text-sm text-slate-900 outline-none placeholder:text-slate-400 dark:text-slate-100" type="text" name="email_prefix" placeholder="nguyenvana123" pattern="[A-Za-z0-9._-]+" autocomplete="off" required>
            <span class="shrink-0 border-l border-slate-200/80 px-4 py-3 text-sm font-semibold text-teal-700 dark:border-white/10 dark:text-teal-200">@vnu.edu.vn</span>
        </div>
        <select class="input-shell mt-3 rounded-xl" name="dept_id" required>
            <?php foreach ($data['departments'] as $department): ?>
                <option value="<?= (int)$department['id'] ?>"><?= e($department['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="input-shell mt-3 rounded-xl" name="role" required>
            <option value="Student">Sinh viên</option>
            <option value="Teacher">Giảng viên</option>
            <option value="Admin">Quản trị</option>
        </select>
        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Thêm người dùng</button>
    </form>

    <form method="POST" class="<?= e($motionCard) ?> p-5">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add_software">
        <div class="mb-5 flex items-center gap-3">
            <img class="h-14 w-14 drop-shadow-xl" src="<?= e($emojiBase . '/Objects/Laptop.png') ?>" alt="Phần mềm 3D">
            <div>
                <h3 class="font-semibold text-slate-950 dark:text-slate-200">Phần mềm</h3>
                <p class="text-sm text-slate-500">Danh mục tài sản bản quyền</p>
            </div>
        </div>
        <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Tên phần mềm</label>
        <input class="input-shell mt-2 rounded-xl" type="text" name="name" placeholder="Ví dụ: MATLAB" required>
        <label class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Nhà phát hành</label>
        <input class="input-shell mt-2 rounded-xl" type="text" name="vendor" placeholder="Ví dụ: MathWorks" required>
        <button class="<?= e($buttonClass) ?> mt-5 w-full" type="submit">Thêm phần mềm</button>
    </form>
</section>

<section class="mt-5 grid gap-5 2xl:grid-cols-2">
    <div class="<?= e($glassCard) ?> overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Travel%20and%20places/School.png') ?>" alt="">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-slate-200">Danh sách khoa/phòng ban</h3>
                    <p class="text-sm text-slate-500">Chặn xóa nếu còn dữ liệu phụ thuộc.</p>
                </div>
            </div>
            <input class="input-shell max-w-xs rounded-xl" data-table-filter="departments-table" placeholder="Lọc khoa...">
        </div>
        <div class="overflow-x-auto">
            <table id="departments-table" data-page-size="10" class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
                <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                    <tr>
                        <th class="px-5 py-3 text-left">Khoa</th>
                        <th class="px-5 py-3 text-center">Users</th>
                        <th class="px-5 py-3 text-center">Rules</th>
                        <th class="px-5 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                    <?php foreach ($data['departments'] as $department): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-slate-200"><?= e($department['name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($department['description'] ?: 'Chưa có mô tả') ?></p>
                            </td>
                            <td class="px-5 py-4 text-center text-sm"><?= (int)$department['user_count'] ?></td>
                            <td class="px-5 py-4 text-center text-sm"><?= (int)$department['rule_count'] ?></td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" class="inline" data-confirm-submit data-confirm-message="Xóa khoa/phòng ban này? Thao tác chỉ thành công nếu chưa có dữ liệu phụ thuộc.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_dept">
                                    <input type="hidden" name="id" value="<?= (int)$department['id'] ?>">
                                    <button class="<?= e($dangerButton) ?>" type="submit" title="Xóa">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/60 px-5 py-3 dark:border-white/10" data-table-pager="departments-table"></div>
    </div>

    <div class="<?= e($glassCard) ?> overflow-hidden">
        <div class="flex flex-col gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/People/Busts%20in%20Silhouette.png') ?>" alt="">
                <div>
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-slate-200">Người dùng</h3>
                    <p class="text-sm text-slate-500">Email duy nhất, phân quyền theo khoa.</p>
                </div>
            </div>
    <div class="flex items-center gap-2">
         <form method="POST">
               <?= csrf_field() ?>
               <input type="hidden" name="action" value="export_users">

               <button
                   type="submit"
                   class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700">
                   Export CSV
         </button>
    </form>

    <input
        class="input-shell max-w-xs rounded-xl"
        data-table-filter="users-table"
        placeholder="Lọc người dùng...">
</div>
        </div>
        <div class="overflow-x-auto">
            <table id="users-table" data-page-size="10" class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
                <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                    <tr>
                        <th class="px-5 py-3 text-left">Người dùng</th>
                        <th class="px-5 py-3 text-left">Khoa</th>
                        <th class="px-5 py-3 text-center">Vai trò</th>
                        <th class="px-5 py-3 text-center">License</th>
                        <th class="px-5 py-3 text-right">Hành động</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                    <?php foreach ($data['users'] as $user): ?>
                        <tr class="table-row">
                            <td class="px-5 py-4">
                                <p class="font-medium text-slate-900 dark:text-slate-200"><?= e($user['full_name']) ?></p>
                                <p class="text-xs text-slate-500"><?= e($user['email']) ?></p>
                            </td>
                            <td class="px-5 py-4 text-sm"><?= e($user['department_name']) ?></td>
                            <td class="px-5 py-4 text-center text-sm"><?= e(role_label($user['role'])) ?></td>
                            <td class="px-5 py-4 text-center text-sm"><?= (int)$user['allocation_count'] ?></td>
                            <td class="px-5 py-4 text-right">
                                <form method="POST" class="inline" data-confirm-submit data-confirm-message="Xóa người dùng này? Người dùng đã có lịch sử cấp phát sẽ được hệ thống chặn xóa.">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_user">
                                    <input type="hidden" name="id" value="<?= (int)$user['id'] ?>">
                                    <button class="<?= e($dangerButton) ?>" type="submit" title="Xóa">✕</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/60 px-5 py-3 dark:border-white/10" data-table-pager="users-table"></div>
    </div>
</section>

<section class="<?= e($glassCard) ?> mt-5 overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-white/60 px-5 py-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-3">
            <img class="h-10 w-10 drop-shadow-lg" src="<?= e($emojiBase . '/Objects/Laptop.png') ?>" alt="">
            <div>
                <h3 class="text-lg font-semibold text-slate-950 dark:text-slate-200">Danh mục phần mềm</h3>
                <p class="text-sm text-slate-500">Không xóa phần mềm đã có dữ liệu phụ thuộc.</p>
            </div>
        </div>
        <input class="input-shell max-w-xs rounded-xl" data-table-filter="softwares-table" placeholder="Lọc phần mềm...">
    </div>
    <div class="overflow-x-auto">
        <table id="softwares-table" data-page-size="10" class="min-w-full divide-y divide-slate-200/80 dark:divide-slate-700">
            <thead class="bg-white/40 text-xs uppercase tracking-wide text-slate-500 dark:bg-slate-900/40">
                <tr>
                    <th class="px-5 py-3 text-left">Phần mềm</th>
                    <th class="px-5 py-3 text-center">Pool</th>
                    <th class="px-5 py-3 text-center">Tổng</th>
                    <th class="px-5 py-3 text-center">Trống</th>
                    <th class="px-5 py-3 text-center">Rules</th>
                    <th class="px-5 py-3 text-center">Assets</th>
                    <th class="px-5 py-3 text-right">Hành động</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80 dark:divide-slate-800">
                <?php foreach ($data['softwares'] as $software): ?>
                    <tr class="table-row">
                        <td class="px-5 py-4">
                            <p class="font-medium text-slate-900 dark:text-slate-200"><?= e($software['name']) ?></p>
                            <p class="text-xs text-slate-500"><?= e($software['vendor']) ?></p>
                        </td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$software['pool_count'] ?></td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$software['total_quantity'] ?></td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$software['available_quantity'] ?></td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$software['rule_count'] ?></td>
                        <td class="px-5 py-4 text-center text-sm"><?= (int)$software['asset_count'] ?></td>
                        <td class="px-5 py-4 text-right">
                            <form method="POST" class="inline" data-confirm-submit data-confirm-message="Xóa phần mềm này? Hệ thống sẽ chặn nếu phần mềm đang có dữ liệu phụ thuộc.">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_software">
                                <input type="hidden" name="id" value="<?= (int)$software['id'] ?>">
                                <button class="<?= e($dangerButton) ?>" type="submit" title="Xóa">✕</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="border-t border-white/60 px-5 py-3 dark:border-white/10" data-table-pager="softwares-table"></div>
</section>
<?php
$content = ob_get_clean();
Layout::render('Quản trị nền tảng', 'admin', $content, ['subtitle' => 'Master Data & Identity']);
