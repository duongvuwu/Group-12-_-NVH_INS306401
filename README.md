# Software License Tracker

Hệ thống quản lý license phần mềm dành cho môi trường trường đại học. Dự án hỗ trợ quản lý kho license, luật cấp phát theo khoa/phòng ban, cấp key phần mềm cho người dùng và theo dõi vòng đời sử dụng license.

## Tổng Quan

Project được xây dựng bằng PHP thuần theo mô hình MVC tự triển khai, dùng PDO để kết nối MySQL. Ứng dụng tập trung vào xử lý nghiệp vụ backend, truy vấn dữ liệu quan hệ bằng SQL/JOIN và giao diện quản trị gọn gàng để theo dõi tài nguyên license.

## Chức Năng Chính

- Dashboard tổng quan tình trạng license và dữ liệu vận hành.
- Quản lý nền tảng: khoa/phòng ban, người dùng, phần mềm và dữ liệu danh mục.
- Quản lý kho license, pool license và serial/license key.
- Thiết lập luật cấp phát license theo phần mềm, khoa và vai trò người dùng.
- Cấp phát license key, theo dõi trạng thái Active/Expired/Revoked.
- Ghi nhận log kích hoạt, cảnh báo hết hạn, thu hồi và thống kê sử dụng.

## Công Nghệ Sử Dụng

- PHP thuần
- MySQL/MariaDB
- PDO
- HTML/CSS
- JavaScript thuần
- Tailwind CSS qua CDN
- Lucide Icons qua CDN
- XAMPP hoặc môi trường PHP/MySQL tương đương

## Cấu Trúc Thư Mục

```text
.
├── app/
│   ├── controllers/        # Xử lý request và business logic
│   ├── models/             # Truy vấn dữ liệu
│   └── views/              # Giao diện hiển thị
├── config/
│   ├── database.php        # Cấu hình kết nối PDO
│   └── FinalDatabase.sql   # File SQL tham khảo
├── core/
│   ├── helpers.php         # Hàm tiện ích
│   └── Layout.php          # Layout dùng chung
├── public/
│   ├── index.php           # Router chính của ứng dụng
│   └── assets/
│       └── app.js          # JavaScript giao diện
├── database.sql            # Schema và dữ liệu mẫu
└── README.md
```

## Cơ Sở Dữ Liệu

Database mặc định:

```text
license_management_db
```

Hệ thống hiện có các nhóm bảng chính:

- Nền tảng và kho: `departments`, `users`, `software_titles`, `license_pools`
- Cấp phát license: `allocation_rules`, `license_keys`, `license_allocations`, `activation_logs`
- Vòng đời và thống kê: `software_assets`, `expiry_notifications`, `revocation_logs`, `usage_stats`
- Nhật ký hệ thống: `audit_logs`

## Cài Đặt Và Chạy Dự Án

1. Cài XAMPP hoặc môi trường có PHP và MySQL/MariaDB.

2. Đặt thư mục project vào thư mục web server, ví dụ:

```text
C:\xampp\htdocs\Final Project Web
```

3. Khởi động Apache và MySQL trong XAMPP.

4. Import database:

- Mở phpMyAdmin.
- Tạo/import từ file `database.sql`.
- Database sẽ được tạo với tên `license_management_db`.

5. Kiểm tra cấu hình kết nối tại `config/database.php`:

```php
private string $host = '127.0.0.1';
private string $dbname = 'license_management_db';
private string $username = 'root';
private string $password = '';
```

6. Truy cập ứng dụng:

```text
http://localhost/Final%20Project%20Web/public/
```

Nếu dùng PHP built-in server:

```bash
php -S localhost:8000 -t public
```

Sau đó mở:

```text
http://localhost:8000
```

## Các Trang Chính

Ứng dụng dùng router qua tham số `page`:

```text
/public/index.php?page=dashboard
/public/index.php?page=admin
/public/index.php?page=rules
/public/index.php?page=inventory
/public/index.php?page=allocations
```

Mặc định khi không truyền `page`, hệ thống mở trang `dashboard`.

## Quy Ước Phát Triển

- Controller đặt trong `app/controllers`.
- Model đặt trong `app/models` và chỉ nên chứa logic truy vấn dữ liệu.
- View đặt trong `app/views` và nhận dữ liệu từ controller.
- Router tập trung tại `public/index.php`.
- Không sử dụng framework lớn như Laravel hoặc CodeIgniter.
- Ưu tiên PDO prepared statements khi truy vấn database.
- Các nghiệp vụ quan trọng nên ghi lại trong `audit_logs`.

## Tài Liệu Đi Kèm

- `Project_Briefing.md`: mô tả mục tiêu, kiến trúc và tiến độ dự án.
- `database.sql`: schema chính và dữ liệu mẫu.
- `database_fix_reusable_after_revocation.sql`: script chỉnh sửa liên quan tới khả năng tái sử dụng key sau thu hồi.
- `Flow chart of dev Application.png`: sơ đồ luồng phát triển/ứng dụng.
- `12 Database.docx`: tài liệu mô tả database.

## Ghi Chú

Nếu ứng dụng báo lỗi kết nối CSDL, cần kiểm tra MySQL đã chạy, database đã được import và thông tin trong `config/database.php` trùng với cấu hình máy local.
