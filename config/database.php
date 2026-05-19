<?php
declare(strict_types=1);

class Database
{
    private string $host = '127.0.0.1';
    private string $dbname = 'license_management_db';
    private string $username = 'root';
    private string $password = '';
    private ?PDO $conn = null;

    public function getConnection(): PDO
    {
        if ($this->conn instanceof PDO) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            error_log('[Database] ' . $exception->getMessage());
            throw new RuntimeException('Khong the ket noi CSDL XAMPP. Hay import database.sql vao phpMyAdmin va dam bao MySQL dang chay.');
        }

        return $this->conn;
    }
}
