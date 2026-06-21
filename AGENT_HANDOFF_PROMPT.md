# MASTER HANDOFF PROMPT - SOFTWARE LICENSE TRACKER

Ban la AI Agent tiep quan du an **Software License Tracker / He thong Quan ly License Phan mem**. Hay coi noi dung prompt nay la bo nho ban giao chinh, sau do doc code thuc te trong repository de xac minh truoc khi sua.

## 1. Muc tieu du an

Xay dung he thong quan tri license phan mem cho truong dai hoc bang **Native PHP MVC**, chay tren XAMPP. He thong quan ly:

- Khoa/phong ban va nguoi dung.
- Danh muc phan mem.
- Pool license, serial/license key va file cai dat.
- Luat cap phat theo phan mem, khoa va vai tro.
- Cap phat, kich hoat, het han va thu hoi license.
- Thong ke su dung va audit log.

San pham can co business logic chac chan, PDO an toan va UI van hanh hien dai, khong chi la CRUD demo tho.

## 2. Thu muc va moi truong chinh

Thu muc lam viec chinh:

```text
D:\Final Project Web
```

Chi dong bo thay doi sang hai noi sau:

```text
C:\xampp\htdocs\Final_Project
D:\Final_Project_Web
```

Khong dong bo sang `C:\xampp\htdocs\Final Project` co dau cach.

URL XAMPP:

```text
http://localhost/Final_Project/public/index.php?page=dashboard
```

Database:

```text
host=127.0.0.1
database=license_management_db
user=root
password=
```

## 3. Nguon su that va tai lieu cu

Thu tu uu tien khi co mau thuan:

1. Code va schema hien tai.
2. Prompt ban giao nay.
3. Quyet dinh moi nhat cua nguoi dung trong hoi thoai.
4. README va Project_Briefing.

`Project_Briefing.md` dang cu: van ghi 12 bang va phan cong thanh vien cu. Schema hien tai co 13 bang do da them `audit_logs`.

`README.md` van co duong dan XAMPP cu. Duong dan dung la `C:\xampp\htdocs\Final_Project`.

## 4. Kien truc Native PHP MVC

```text
public/index.php                 Router duy nhat
config/database.php             Ket noi PDO
core/helpers.php                Escape, CSRF, flash, validation helpers
core/Layout.php                 Layout, sidebar, dark mode, modal, toast root
app/controllers/                Dieu phoi request
app/models/                     SQL, transaction va logic gan database
app/views/                      HTML/PHP hien thi
public/assets/app.js            Tuong tac giao dien
```

Routes hien tai:

```text
dashboard    -> DashboardController
admin        -> PlatformAdminController
rules        -> AllocationRuleController
inventory    -> InventoryController
allocations  -> LicenseAllocationController
```

Luu y:

- `app/controller/DashboardController.php` chi la shim cu tro sang `app/controllers`; router khong dung thu muc so it.
- `Database` hien cache PDO trong tung object, chua phai Singleton toan ung dung.
- Tat ca form POST quan trong dung CSRF va Post/Redirect/Get voi flash message.

## 5. Schema database hien tai

Co 13 bang:

```text
departments
users
software_titles
software_assets
license_pools
allocation_rules
license_keys
license_allocations
activation_logs
expiry_notifications
revocation_logs
usage_stats
audit_logs
```

Quan he chinh:

```text
departments 1-N users
departments 1-N allocation_rules
departments 1-N usage_stats

software_titles 1-N software_assets
software_titles 1-N license_pools
software_titles 1-N allocation_rules
software_titles 1-N usage_stats

license_pools 1-N license_keys
users 1-N license_allocations
license_keys 1-N license_allocations theo lich su

license_allocations 1-N activation_logs
license_allocations 1-N expiry_notifications
license_allocations 1-N revocation_logs
```

`audit_logs` khong co FK vat ly. No dung `entity_type + entity_id` lam soft/polymorphic reference.

Rang buoc quan trong:

- Email user unique va bat buoc `@vnu.edu.vn`.
- Software unique theo `(name, vendor)`.
- Rule unique theo `(software_id, department_id, target_role)`.
- License key unique toan he thong.
- `end_date > start_date`.
- FK dung `ON DELETE RESTRICT` de bao ve lich su.
- `license_pools.reusable_after_revocation` quyet dinh key co duoc tra lai kho hay khong.

## 6. Du lieu mau da chot

Schema day du nam o:

```text
database.sql
config/FinalDatabase.sql
```

Hai file nay hien giong nhau.

Mock student:

- 6 khoa/phong ban.
- Moi khoa 100 sinh vien, tong 600 sinh vien mock.
- Moi nhom 10 sinh vien: 8 ten ba chu, 2 ten bon chu.
- Email: ten viet lien khong dau + 3 chu so + `@vnu.edu.vn`.
- 600 email mock la unique.

## 7. Quy uoc SQL bat buoc

Khi co migration SQL moi:

1. Chi viet migration dang thuc hien vao root `update.sql`.
2. Truoc migration tiep theo, xoa noi dung migration cu trong `update.sql`.
3. Sau moi lan update, luu snapshot vao `config/updateN.sql`, tang N tuan tu.
4. Khong tao them cac file `database_fix_*.sql` hoac file migration roi rac.
5. Neu schema full-install thay doi, cap nhat dong bo `database.sql` va `config/FinalDatabase.sql`.
6. Truoc push, bao cao ro migration moi va file `config/updateN.sql` tuong ung.

Hien tai:

```text
update.sql == config/update1.sql
```

`update1` chua 600 sinh vien mock va chuan hoa email VNU.

Mot so file fix SQL roi rac van ton tai do duoc tao truoc quy uoc moi. Khong tiep tuc mo rong pattern do.

## 8. Workflow nghiep vu tong

```text
Platform Master Data
    -> Allocation Rules
    -> License Inventory
    -> License Allocation
    -> Activation / Expiry / Revocation
    -> Dashboard / Audit / Reporting
```

### 8.1 Platform

- Tao/xem/xoa khoa, user va software.
- User bat buoc thuoc mot khoa.
- Controller tu dong ghep duoi `@vnu.edu.vn`.
- Backend kiem tra email, role va du lieu trung.
- Chan xoa khoa/software/user khi co du lieu phu thuoc.

### 8.2 Rules

- Rule gom software, department va target role.
- Target role: Student, Teacher, Admin hoac All.
- Chan rule trung.
- Chan xoa rule neu con allocation Active phu thuoc.
- Co goi y phan mem theo ten khoa.

### 8.3 Inventory

- Tao pool voi tong so luong, ngay mua, ngay het han va reusable flag.
- Pool khoi tao `available_quantity = 0`; so trong duoc tinh tu key that.
- Nhap key theo tung dong.
- Transaction va `FOR UPDATE` khoa pool khi import key.
- Chan vuot suc chua pool; bo qua key trung theo unique constraint.
- Asset gom version, OS va download URL.

### 8.4 Allocation engine

Thu tu cap phat:

1. Nhan user, software va duration.
2. Duration phai trong 1-1095 ngay.
3. Kiem tra user ton tai.
4. Kiem tra rule theo department + role + software.
5. Chan user dang co license Active cua cung software.
6. Bat dau transaction.
7. Tim key trong thuoc pool hop le, uu tien pool sap het han nhung van du thoi han.
8. Khoa key bang `SELECT ... FOR UPDATE`.
9. Insert `license_allocations` status Active.
10. Danh dau key assigned va sync `available_quantity` tu du lieu key that.
11. Commit.
12. Controller ghi audit log va flash toast.

### 8.5 Activation, revoke va expiry

- Chi allocation Active moi duoc ghi activation.
- Revoke bat buoc co reason.
- Revoke cap nhat status, ghi `revocation_logs` va tra key neu pool reusable.
- Khi mo trang allocations, `syncExpiredAllocations()` tao expiry notifications va dong allocation qua han.
- Hien chua co cron/background worker hoac gui email that.

### 8.6 Audit log

- Controller ghi cac hanh dong nhay cam: create/delete master data, pool, import key, rule, allocation, activation va revoke.
- Audit duoc ghi sau thao tac chinh.
- `AuditLogModel::record()` bat PDO exception va chi `error_log`; audit loi khong rollback nghiep vu chinh.
- Day la ly do truoc day thao tac them software van thanh cong du bang `audit_logs` chua ton tai.
- `AuditLogModel::recent()` co san nhung chua co UI xem log.

## 9. UI/UX da chot

Stack:

```text
Tailwind CSS CDN
Vanilla JavaScript
Chart.js CDN
Lucide Icons
Animated Fluent Emoji assets
```

Phong cach:

- Operational SaaS, Glassmorphism, backdrop blur va micro-animation.
- Khong dung alert tho; dung toast.
- Xoa/thu hoi dung modal xac nhan.
- Dashboard co KPI cards, Chart.js va danh sach sap het han.
- Chart animation `duration: 1500`, `easing: easeOutQuart`.
- Dark/light mode luu trong localStorage.
- Responsive sidebar desktop va bottom navigation mobile.

Quy tac dark mode da chot, khong tu y don gian hoa CSS:

- Title tren cung mau trang trong dark mode.
- Toan bo chu va icon sidebar mau trang thuan.
- Button va `[role=button]` phai co chu trang tren nen toi.
- Pager, nut trang va dropdown chon trang phai doc ro trong dark mode.
- Existing CSS co override rieng de giu cac ngoai le nay.

Pagination:

- Ba bang `departments`, `users`, `software_titles` chi hien 10 dong moi trang.
- Co filter, page chips compact, Prev/Next va dropdown chon trang.
- Hien pagination la client-side: van render toan bo 600 user vao DOM roi an cac dong con lai.

## 10. Phan cong thanh vien hien tai

Quyet dinh moi nhat:

### Quy

```text
Platform + Master Data + UI
departments, users, software_titles
Dashboard, layout, dark mode, table UX
```

### Dang Duong

```text
License Inventory + Allocation Rules
license_pools, license_keys, software_assets, allocation_rules
```

### Binh Duong

```text
Allocation Engine + Lifecycle + Logs
license_allocations, activation_logs, expiry_notifications,
revocation_logs, usage_stats, audit_logs
```

Khong su dung phan cong cu trong `Project_Briefing.md` neu khong duoc nguoi dung doi lai.

## 11. Trang thai dap ung yeu cau mon hoc

Da co:

- Nhom 3 nguoi.
- 13 bang co lien ket logic.
- Native PHP MVC.
- PDO/MySQL.
- Responsive Tailwind UI.
- Validation backend, HTML required va mot so JS validation.
- CSRF.
- SQL export.
- ERD va workflow Mermaid.

Chua hoan thien:

- Chua co Update UI/day du CRUD cho moi bang.
- Chua co authentication va RBAC that; role hien chi phuc vu allocation rule.
- `current_actor()` mac dinh `Platform Admin`.
- Chua co AJAX CRUD/REST API.
- Chua co email expiry that hoac cron job.
- `usage_stats` chua co job tu tong hop.
- Chua co man hinh audit history.
- Chua co automated tests.
- Database connection chua la Singleton dung nghia.
- Chua co export PDF/Excel.

Khong duoc trinh bay cac muc nay nhu da hoan thanh.

## 12. Rui ro ky thuat dang biet

- Check active allocation lan hai dung `FOR UPDATE`, nhung khi chua co row thi van co rui ro hai request dong thoi cap cung software cho mot user. Can lock user/advisory key hoac thiet ke constraint/lock ro hon khi xu ly concurrency nghiem tuc.
- Client-side pagination voi 600 user lam HTML va DOM lon; server-side pagination la huong nang cap.
- `usage_stats` va `audit_logs` chua co view/report day du.
- External CDN/emoji can Internet; offline localhost se mat style/chart/assets.
- Khong co `.gitignore`; workspace dang co artifact va thu muc ngoai du an.

## 13. Git va filesystem hygiene

- Worktree co the dang dirty. Khong revert thay doi cua nguoi dung.
- Chi sua file lien quan yeu cau.
- Thu muc `LInk Viet hoa` va cac tai lieu legal exam khong thuoc project license; bo qua va tuyet doi khong commit cac file lon/key nhay cam trong do.
- Khong dung `git reset --hard` hoac lenh huy thay doi neu chua duoc yeu cau ro.
- Sau moi dot thay doi, dua ra commit message de xuat.
- Chi commit khi nguoi dung yeu cau.

## 14. Quy tac code

- MVC nghiem ngat: View khong truy van database.
- Controller dieu phoi request, Model xu ly persistence/transaction.
- Tat ca input SQL phai dung PDO prepared statements.
- Giu CSRF cho moi POST.
- Escape output bang `e()`.
- Khong xoa du lieu co dependency.
- Khong cap key trung, khong cap active trung, khong cho ngay het han vo ly.
- Uu tien pattern va helper dang co, khong refactor lan man.
- Dung `apply_patch` khi sua file thu cong.
- Dung `rg` khi tim code.
- Khong sua file ngoai pham vi neu khong can.

## 15. Protocol khi nhan task moi

Truoc khi code:

1. Doc file lien quan va xac minh worktree.
2. Brainstorm ngan 1-2 diem UI/backend co gia tri.
3. Liet ke file se sua.
4. Neu SQL thay doi, tuan thu protocol `update.sql + config/updateN.sql`.

Khi trien khai:

1. Sua truc tiep trong `D:\Final Project Web`.
2. Giu scope gon.
3. Dong bo chi sang `C:\xampp\htdocs\Final_Project` va `D:\Final_Project_Web`.
4. Khong dong bo vao bat ky thu muc XAMPP nao khac.

Sau khi code:

1. Chay PHP lint bang `C:\xampp\php\php.exe -l` cho file PHP lien quan.
2. Chay `node --check public\assets\app.js` neu sua JS.
3. Kiem tra localhost neu Apache/MySQL dang chay.
4. Kiem tra Git diff va khong stage file ngoai du an.
5. Bao cao file da sua, test da chay, phan chua test duoc.
6. De xuat commit message cu the.

## 16. File can doc dau tien

Khi bat dau mot session moi, doc theo thu tu:

```text
AGENT_HANDOFF_PROMPT.md
public/index.php
config/database.php
core/helpers.php
core/Layout.php
app/controllers/*.php
app/models/*.php
app/views/*.php
public/assets/app.js
database.sql
update.sql
config/update*.sql
LICENSEOS_ERD_DRAWIO.mmd
LICENSEOS_FLOWCHART_DRAWIO.mmd
```

Sau khi doc, hay tom tat lai hieu biet va chi ra bat ky mau thuan nao giua prompt, code, schema va yeu cau moi nhat. Khong bat dau refactor lon chi vi thay code co the dep hon.

## 17. Trang thai kiem tra gan nhat

Tai lan audit gan nhat:

- 21 file PHP lint thanh cong.
- `public/assets/app.js` qua `node --check`.
- Code chinh giua workspace, XAMPP va `D:\Final_Project_Web` dang dong bo.
- Apache khong chay tai thoi diem audit nen localhost chua duoc verify runtime.
- `D:\Final_Project_Web` khong co root `database.sql`, nhung co `config/FinalDatabase.sql` dung ban schema.
- Worktree co thay doi chua commit va nhieu file untracked; phai kiem tra lai truoc moi task.

---

Bay gio hay doc repository, xac minh trang thai hien tai va tiep tuc theo yeu cau moi nhat cua nguoi dung. Neu code thuc te da thay doi sau prompt nay, uu tien code va bao ro sai lech thay vi am tham gia dinh.
