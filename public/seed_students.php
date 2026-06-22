<?php
// public/seed_students.php
declare(strict_types=1);

// 1. CẤU HÌNH KẾT NỐI DATABASE (ĐÃ SET CỔNG 3307 CHO MÁY BRO)
class SeedDatabase
{
    private string $host = '127.0.0.1';
    private string $dbname = 'license_management_db';
    private string $username = 'root';
    private string $password = ''; // Điền mật khẩu nếu MySQL của bro có cài đặt
    private ?PDO $conn = null;

    public function getConnection(): PDO
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        // Khai báo chuẩn cổng 3307 để chọc thẳng vào XAMPP nội bộ
        $dsn = "mysql:host={$this->host};port=3307;dbname={$this->dbname};charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            die("<h3 style='color: red;'>❌ Lỗi kết nối CSDL: " . $exception->getMessage() . "</h3>");
        }

        return $this->conn;
    }
}

// 2. LOGIC SINH DỮ LIỆU TỰ ĐỘNG 100 SINH VIÊN
try {
    $db = (new SeedDatabase())->getConnection();
    
    // Kho dữ liệu họ và tên tiếng Việt phổ biến
    $ho = ['Nguyễn', 'Trần', 'Lê', 'Phạm', 'Hoàng', 'Huỳnh', 'Phan', 'Vũ', 'Võ', 'Đặng', 'Bùi', 'Đỗ', 'Hồ', 'Ngô'];
    $dem = ['Văn', 'Thị', 'Minh', 'Ngọc', 'Hữu', 'Thanh', 'Đức', 'Thu', 'Xuân', 'Hải'];
    $ten = ['Anh', 'Bảo', 'Cường', 'Dung', 'Hải', 'Khoa', 'Linh', 'Nam', 'Phong', 'Trang', 'Tùng', 'Yến'];

    echo "<h2>⏳ Đang tiến hành gieo hạt 100 sinh viên ảo vào hệ thống...</h2>";

    $count = 0;
    for ($i = 1; $i <= 100; $i++) {
        // Sinh tên ngẫu nhiên
        $h = $ho[array_rand($ho)];
        $d = $dem[array_rand($dem)];
        $t = $ten[array_rand($ten)];
        $full_name = "$h $d $t";

        // Chuyển tên thành dạng không dấu để tạo email chuyên nghiệp và độc nhất
        $email_prefix = strtolower(removeAccents($t . '.' . $h . $d)) . str_pad((string)$i, 3, "0", STR_PAD_LEFT);
        $email = $email_prefix . "@student.edu.vn";
        
        // Random thuộc về 1 trong 3 khoa (ID từ 1 đến 3)
        $department_id = rand(1, 3);
        $role = 'Student';

        // Câu lệnh SQL thêm dữ liệu vào bảng users của đồ án
        $sql = "INSERT INTO users (department_id, full_name, email, role) 
                VALUES (:department_id, :full_name, :email, :role)";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':department_id' => $department_id,
            ':full_name' => $full_name,
            ':email' => $email,
            ':role' => $role
        ]);
        $count++;
    }

    echo "<h3 style='color: green;'>✅ Thành công! Đã bơm thành công {$count} sinh viên mẫu vào bảng users.</h3>";
    echo "<p><a href='index.php?page=assets'>➡️ Quay lại trang Download Center</a></p>";

} catch (PDOException $e) {
    if ($e->getCode() === '23000') {
        echo "<h3 style='color: red;'>❌ Lỗi: Email đã tồn tại. Có vẻ bảng users đã có dữ liệu rồi, bro hãy dọn sạch bảng trước khi chạy lại nhé!</h3>";
    } else {
        echo "<h3 style='color: red;'>❌ Lỗi Database: " . $e->getMessage() . "</h3>";
    }
} catch (Exception $e) {
    echo "<h3 style='color: red;'>❌ Lỗi Hệ thống: " . $e->getMessage() . "</h3>";
}

// Hàm phụ trợ xóa dấu tiếng Việt
function removeAccents($str) {
    $accents = array(
        'a' => 'á|à|ả|ã|ạ|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ', 'd' => 'đ', 'e' => 'é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ',
        'i' => 'í|ì|ỉ|ĩ|ị', 'o' => 'ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ', 'u' => 'ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự',
        'y' => 'ý|ỳ|ỷ|ỹ|ỵ', 'A' => 'Á|À|Ả|Ã|Ạ|Ă|Ắ|Ằ|Ẳ|Ẵ|Ặ|Â|Ấ|Ầ|Ẩ|Ẫ|Ậ', 'D' => 'Đ', 'E' => 'É|È|Ẻ|Ẽ|Ẹ|Ê|Ế|Ề|Ể|Ễ|Ệ',
        'I' => 'Í|Ì|Ỉ|Ĩ|Ị', 'O' => 'Ó|Ò|Ỏ|Õ|Ọ|Ô|Ố|Ồ|Ổ|Ỗ|Ộ|Ơ|Ớ|Ờ|Ở|Ỡ|Ợ', 'U' => 'Ú|Ù|Ủ|Ũ|Ụ|Ư|Ứ|Ừ|Ử|Ữ|Ự',
        'Y' => 'Ý|Ỳ|Ỷ|Ỹ|Ỵ'
    );
    foreach ($accents as $nonAccent => $accent) {
        $str = preg_replace("/($accent)/i", $nonAccent, $str);
    }
    return str_replace(' ', '', $str);
}
?>