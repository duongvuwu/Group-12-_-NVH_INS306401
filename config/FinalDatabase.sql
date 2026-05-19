CREATE DATABASE IF NOT EXISTS license_management_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE license_management_db;

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS audit_logs;
DROP TABLE IF EXISTS usage_stats;
DROP TABLE IF EXISTS revocation_logs;
DROP TABLE IF EXISTS expiry_notifications;
DROP TABLE IF EXISTS activation_logs;
DROP TABLE IF EXISTS license_allocations;
DROP TABLE IF EXISTS license_keys;
DROP TABLE IF EXISTS allocation_rules;
DROP TABLE IF EXISTS license_pools;
DROP TABLE IF EXISTS software_assets;
DROP TABLE IF EXISTS software_titles;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_id INT NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    role ENUM('Student', 'Teacher', 'Admin') NOT NULL DEFAULT 'Student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_department_role (department_id, role),
    CONSTRAINT fk_users_department
      FOREIGN KEY (department_id) REFERENCES departments(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE software_titles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(160) NOT NULL,
    vendor VARCHAR(160) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_software_vendor (name, vendor)
) ENGINE=InnoDB;

CREATE TABLE software_assets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    software_id INT NOT NULL,
    version VARCHAR(80) NOT NULL,
    os_type ENUM('Windows', 'macOS', 'Linux', 'Web') NOT NULL,
    download_url VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_assets_software_os (software_id, os_type),
    CONSTRAINT fk_assets_software
      FOREIGN KEY (software_id) REFERENCES software_titles(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE license_pools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    software_id INT NOT NULL,
    total_quantity INT NOT NULL,
    available_quantity INT NOT NULL DEFAULT 0,
    purchase_date DATE NOT NULL,
    expires_at DATE NULL,
    reusable_after_revocation TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_pools_software_available (software_id, available_quantity),
    CONSTRAINT chk_pool_quantity CHECK (total_quantity >= 0 AND available_quantity >= 0),
    CONSTRAINT fk_pools_software
      FOREIGN KEY (software_id) REFERENCES software_titles(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE allocation_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    software_id INT NOT NULL,
    department_id INT NOT NULL,
    target_role ENUM('Student', 'Teacher', 'Admin', 'All') NOT NULL DEFAULT 'All',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_rule_scope (software_id, department_id, target_role),
    INDEX idx_rules_department_role (department_id, target_role),
    CONSTRAINT fk_rules_software
      FOREIGN KEY (software_id) REFERENCES software_titles(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_rules_department
      FOREIGN KEY (department_id) REFERENCES departments(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE license_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pool_id INT NOT NULL,
    key_value VARCHAR(255) NOT NULL UNIQUE,
    is_assigned TINYINT(1) NOT NULL DEFAULT 0,
    assigned_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_keys_pool_assigned (pool_id, is_assigned),
    CONSTRAINT fk_keys_pool
      FOREIGN KEY (pool_id) REFERENCES license_pools(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE license_allocations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    key_id INT NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('Active', 'Expired', 'Revoked') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_allocations_user_status (user_id, status),
    INDEX idx_allocations_key_status (key_id, status),
    INDEX idx_allocations_end_status (end_date, status),
    CONSTRAINT chk_allocation_dates CHECK (end_date > start_date),
    CONSTRAINT fk_allocations_user
      FOREIGN KEY (user_id) REFERENCES users(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_allocations_key
      FOREIGN KEY (key_id) REFERENCES license_keys(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE activation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allocation_id INT NOT NULL,
    activation_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NULL,
    INDEX idx_activation_allocation (allocation_id, activation_time),
    CONSTRAINT fk_activation_allocation
      FOREIGN KEY (allocation_id) REFERENCES license_allocations(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE expiry_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allocation_id INT NOT NULL,
    sent_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    alert_type ENUM('7_days', '1_day') NOT NULL,
    UNIQUE KEY uq_expiry_alert (allocation_id, alert_type),
    CONSTRAINT fk_expiry_allocation
      FOREIGN KEY (allocation_id) REFERENCES license_allocations(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE revocation_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    allocation_id INT NOT NULL,
    revocation_time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reason VARCHAR(255) NOT NULL,
    INDEX idx_revocation_allocation (allocation_id, revocation_time),
    CONSTRAINT fk_revocation_allocation
      FOREIGN KEY (allocation_id) REFERENCES license_allocations(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE usage_stats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    software_id INT NOT NULL,
    department_id INT NOT NULL,
    active_count INT NOT NULL DEFAULT 0,
    report_period CHAR(7) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usage_period (software_id, department_id, report_period),
    CONSTRAINT fk_usage_software
      FOREIGN KEY (software_id) REFERENCES software_titles(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_usage_department
      FOREIGN KEY (department_id) REFERENCES departments(id)
      ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    actor VARCHAR(120) NOT NULL,
    action VARCHAR(120) NOT NULL,
    entity_type VARCHAR(80) NOT NULL,
    entity_id INT NULL,
    context_json JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_created (created_at),
    INDEX idx_audit_entity (entity_type, entity_id)
) ENGINE=InnoDB;

INSERT INTO departments (id, name, description) VALUES
(1, 'Khoa Công nghệ thông tin', 'Lập trình, hệ thống thông tin và an toàn dữ liệu'),
(2, 'Khoa Kinh tế', 'Kế toán, tài chính và phân tích dữ liệu kinh doanh'),
(3, 'Khoa Kiến trúc', 'Thiết kế, xây dựng và mô phỏng công trình'),
(4, 'Trung tâm Truyền thông', 'Thiết kế số, dựng phim và truyền thông đa phương tiện');

INSERT INTO users (id, department_id, full_name, email, role) VALUES
(1, 1, 'Nguyễn Minh An', 'an.nguyen@university.edu.vn', 'Student'),
(2, 1, 'Trần Bảo Dương', 'duong.tran@university.edu.vn', 'Teacher'),
(3, 2, 'Lê Thu Hà', 'ha.le@university.edu.vn', 'Student'),
(4, 3, 'Phạm Quốc Khánh', 'khanh.pham@university.edu.vn', 'Student'),
(5, 4, 'Vũ Hoàng Linh', 'linh.vu@university.edu.vn', 'Teacher'),
(6, 1, 'Platform Admin', 'admin@university.edu.vn', 'Admin');

INSERT INTO software_titles (id, name, vendor) VALUES
(1, 'MATLAB', 'MathWorks'),
(2, 'AutoCAD', 'Autodesk'),
(3, 'JetBrains All Products', 'JetBrains'),
(4, 'MISA AMIS', 'MISA'),
(5, 'Adobe Creative Cloud', 'Adobe'),
(6, 'Microsoft 365', 'Microsoft'),
(7, 'Revit', 'Autodesk');

INSERT INTO software_assets (software_id, version, os_type, download_url) VALUES
(1, 'R2026a', 'Windows', 'https://example.edu/downloads/matlab-r2026a-win'),
(1, 'R2026a', 'macOS', 'https://example.edu/downloads/matlab-r2026a-mac'),
(2, '2026', 'Windows', 'https://example.edu/downloads/autocad-2026-win'),
(3, '2026.1', 'Windows', 'https://example.edu/downloads/jetbrains-toolbox-win'),
(3, '2026.1', 'macOS', 'https://example.edu/downloads/jetbrains-toolbox-mac'),
(4, 'Cloud', 'Web', 'https://example.edu/downloads/misa-amis'),
(5, '2026', 'Windows', 'https://example.edu/downloads/adobe-cc-win'),
(6, 'Education', 'Web', 'https://portal.office.com'),
(7, '2026', 'Windows', 'https://example.edu/downloads/revit-2026-win');

INSERT INTO license_pools (id, software_id, total_quantity, available_quantity, purchase_date, expires_at, reusable_after_revocation) VALUES
(1, 1, 5, 0, '2026-01-15', '2027-01-15', 1),
(2, 2, 4, 0, '2026-02-01', '2027-02-01', 1),
(3, 3, 6, 0, '2026-01-20', '2027-01-20', 1),
(4, 4, 3, 0, '2026-03-10', '2027-03-10', 0),
(5, 5, 4, 0, '2026-04-01', '2027-04-01', 1),
(6, 6, 8, 0, '2026-01-01', '2027-01-01', 1),
(7, 7, 3, 0, '2026-02-15', '2027-02-15', 1);

INSERT INTO license_keys (pool_id, key_value, is_assigned, assigned_at) VALUES
(1, 'MATLAB-2026-AAAA-0001', 1, NOW()),
(1, 'MATLAB-2026-AAAA-0002', 0, NULL),
(1, 'MATLAB-2026-AAAA-0003', 0, NULL),
(1, 'MATLAB-2026-AAAA-0004', 0, NULL),
(1, 'MATLAB-2026-AAAA-0005', 0, NULL),
(2, 'AUTOCAD-2026-BBBB-0001', 1, NOW()),
(2, 'AUTOCAD-2026-BBBB-0002', 0, NULL),
(2, 'AUTOCAD-2026-BBBB-0003', 0, NULL),
(2, 'AUTOCAD-2026-BBBB-0004', 0, NULL),
(3, 'JB-ALL-2026-CCCC-0001', 1, NOW()),
(3, 'JB-ALL-2026-CCCC-0002', 0, NULL),
(3, 'JB-ALL-2026-CCCC-0003', 0, NULL),
(3, 'JB-ALL-2026-CCCC-0004', 0, NULL),
(3, 'JB-ALL-2026-CCCC-0005', 0, NULL),
(3, 'JB-ALL-2026-CCCC-0006', 0, NULL),
(4, 'MISA-AMIS-2026-DDDD-0001', 0, NULL),
(4, 'MISA-AMIS-2026-DDDD-0002', 0, NULL),
(4, 'MISA-AMIS-2026-DDDD-0003', 0, NULL),
(5, 'ADOBE-CC-2026-EEEE-0001', 0, NULL),
(5, 'ADOBE-CC-2026-EEEE-0002', 0, NULL),
(5, 'ADOBE-CC-2026-EEEE-0003', 0, NULL),
(5, 'ADOBE-CC-2026-EEEE-0004', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0001', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0002', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0003', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0004', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0005', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0006', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0007', 0, NULL),
(6, 'MS365-EDU-2026-FFFF-0008', 0, NULL),
(7, 'REVIT-2026-GGGG-0001', 0, NULL),
(7, 'REVIT-2026-GGGG-0002', 0, NULL),
(7, 'REVIT-2026-GGGG-0003', 0, NULL);

INSERT INTO allocation_rules (software_id, department_id, target_role) VALUES
(1, 1, 'All'),
(3, 1, 'All'),
(6, 1, 'All'),
(4, 2, 'All'),
(6, 2, 'All'),
(2, 3, 'All'),
(7, 3, 'All'),
(5, 4, 'All'),
(6, 4, 'Teacher');

INSERT INTO license_allocations (id, user_id, key_id, start_date, end_date, status) VALUES
(1, 1, 1, NOW(), DATE_ADD(NOW(), INTERVAL 365 DAY), 'Active'),
(2, 4, 6, NOW(), DATE_ADD(NOW(), INTERVAL 21 DAY), 'Active'),
(3, 2, 10, NOW(), DATE_ADD(NOW(), INTERVAL 5 DAY), 'Active');

INSERT INTO activation_logs (allocation_id, activation_time, ip_address) VALUES
(1, DATE_SUB(NOW(), INTERVAL 2 DAY), '10.0.1.23'),
(2, DATE_SUB(NOW(), INTERVAL 1 DAY), '10.0.3.41');

INSERT INTO expiry_notifications (allocation_id, sent_time, alert_type) VALUES
(3, NOW(), '7_days');

INSERT INTO usage_stats (software_id, department_id, active_count, report_period) VALUES
(1, 1, 1, '2026-05'),
(2, 3, 1, '2026-05'),
(3, 1, 1, '2026-05');

INSERT INTO audit_logs (actor, action, entity_type, entity_id, context_json, ip_address) VALUES
('System Seed', 'seed_database', 'database', NULL, JSON_OBJECT('tables', 13), '127.0.0.1');

UPDATE license_pools lp
SET available_quantity = (
    SELECT COUNT(*)
    FROM license_keys lk
    WHERE lk.pool_id = lp.id AND lk.is_assigned = 0
);
