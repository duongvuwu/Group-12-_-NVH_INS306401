-- LicenseOS six-month demo fixture
-- Prerequisites: import database.sql, then update.sql.
-- Purpose: rebuild operational data while preserving departments, users and software titles.
-- Safe to rerun. All dates are relative to the import date so demo scenarios stay relevant.

USE license_management_db;
SET NAMES utf8mb4;

START TRANSACTION;

-- Remove only operational/demo rows. Master data and the 600 VNU users are preserved.
DELETE FROM assistant_messages;
DELETE FROM assistant_conversations;
DELETE FROM audit_logs;
DELETE FROM usage_stats;
DELETE FROM revocation_logs;
DELETE FROM expiry_notifications;
DELETE FROM activation_logs;
DELETE FROM license_allocations;
DELETE FROM license_keys;
DELETE FROM allocation_rules;
DELETE FROM license_pools;
DELETE FROM software_assets;

DROP TEMPORARY TABLE IF EXISTS demo_digits;
CREATE TEMPORARY TABLE demo_digits (n TINYINT UNSIGNED PRIMARY KEY);
INSERT INTO demo_digits (n) VALUES (0),(1),(2),(3),(4),(5),(6),(7),(8),(9);

DROP TEMPORARY TABLE IF EXISTS demo_numbers;
CREATE TEMPORARY TABLE demo_numbers AS
SELECT ones.n + tens.n * 10 + hundreds.n * 100 + 1 AS n
FROM demo_digits AS ones
CROSS JOIN demo_digits AS tens
CROSS JOIN demo_digits AS hundreds
WHERE ones.n + tens.n * 10 + hundreds.n * 100 < 600;
ALTER TABLE demo_numbers ADD PRIMARY KEY (n);

DROP TEMPORARY TABLE IF EXISTS demo_software_map;
CREATE TEMPORARY TABLE demo_software_map (
    software_seq INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    software_id INT NOT NULL UNIQUE
);
INSERT INTO demo_software_map (software_id)
SELECT id FROM software_titles ORDER BY id;

DROP TEMPORARY TABLE IF EXISTS demo_user_map;
CREATE TEMPORARY TABLE demo_user_map (
    user_seq INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    department_id INT NOT NULL
);
INSERT INTO demo_user_map (user_id, department_id)
SELECT id, department_id FROM users ORDER BY id;
SET @demo_user_count = (SELECT COUNT(*) FROM demo_user_map);

-- Download Center: two install assets for every software title.
INSERT INTO software_assets (software_id, version, os_type, download_url, created_at)
SELECT software_id, CONCAT('2026.', software_seq), 'Windows',
       CONCAT('https://downloads.example.edu.vn/software/', software_id, '/windows'),
       DATE_SUB(NOW(), INTERVAL 170 DAY)
FROM demo_software_map
UNION ALL
SELECT software_id, CONCAT('macOS-', software_seq), 'Mac',
       CONCAT('https://portal.example.edu.vn/apps/', software_id),
       DATE_SUB(NOW(), INTERVAL 165 DAY)
FROM demo_software_map;

-- Every department may request every demo product. This keeps the allocation demo open.
INSERT INTO allocation_rules (software_id, department_id, target_role, created_at)
SELECT sm.software_id, d.id, 'All', DATE_SUB(NOW(), INTERVAL 160 DAY)
FROM demo_software_map AS sm
CROSS JOIN departments AS d;

-- Pools deliberately include sold-out, low-stock and healthy-stock examples.
INSERT INTO license_pools (
    software_id, total_quantity, available_quantity, purchase_date, expires_at,
    reusable_after_revocation, created_at
)
SELECT software_id,
       CASE software_seq WHEN 1 THEN 40 WHEN 2 THEN 45 WHEN 3 THEN 50 ELSE 60 END,
       CASE software_seq WHEN 1 THEN 0 WHEN 2 THEN 2 WHEN 3 THEN 5
            ELSE 60 - (20 + software_seq) END,
       DATE_SUB(CURDATE(), INTERVAL 175 DAY),
       DATE_ADD(CURDATE(), INTERVAL (300 + software_seq * 15) DAY),
       CASE WHEN MOD(software_seq, 3) = 0 THEN 0 ELSE 1 END,
       DATE_SUB(NOW(), INTERVAL 175 DAY)
FROM demo_software_map;

DROP TEMPORARY TABLE IF EXISTS demo_pool_map;
CREATE TEMPORARY TABLE demo_pool_map AS
SELECT sm.software_seq, sm.software_id, lp.id AS pool_id, lp.total_quantity
FROM demo_software_map AS sm
JOIN license_pools AS lp ON lp.software_id = sm.software_id;
ALTER TABLE demo_pool_map ADD PRIMARY KEY (pool_id), ADD UNIQUE KEY (software_seq);

INSERT INTO license_keys (pool_id, key_value, is_assigned, assigned_at, created_at)
SELECT pm.pool_id,
       CONCAT('LIC-DEMO-', LPAD(pm.software_seq, 2, '0'), '-', LPAD(n.n, 3, '0'), '-2026'),
       0, NULL, DATE_SUB(NOW(), INTERVAL 170 DAY)
FROM demo_pool_map AS pm
JOIN demo_numbers AS n ON n.n <= pm.total_quantity;

DROP TEMPORARY TABLE IF EXISTS demo_key_map;
CREATE TEMPORARY TABLE demo_key_map AS
SELECT pm.software_seq, pm.software_id, pm.pool_id, lk.id AS key_id,
       ROW_NUMBER() OVER (PARTITION BY lk.pool_id ORDER BY lk.id) AS key_seq
FROM demo_pool_map AS pm
JOIN license_keys AS lk ON lk.pool_id = pm.pool_id;
ALTER TABLE demo_key_map ADD PRIMARY KEY (key_id), ADD KEY (software_seq, key_seq);

-- Historical expired allocations: demonstrate a complete lifecycle without holding a key now.
INSERT INTO license_allocations (
    user_id, key_id, start_date, end_date, status, created_at
)
SELECT um.user_id, km.key_id,
       DATE_SUB(NOW(), INTERVAL (178 - km.key_seq) DAY),
       DATE_SUB(NOW(), INTERVAL (148 - km.key_seq) DAY),
       'Expired',
       DATE_SUB(NOW(), INTERVAL (178 - km.key_seq) DAY)
FROM demo_key_map AS km
JOIN demo_user_map AS um
  ON um.user_seq = MOD(200 + km.software_seq * 31 + km.key_seq - 1, @demo_user_count) + 1
WHERE km.key_seq BETWEEN 1 AND 10;

-- Historical revoked allocations: used to show revocation reasons and reusable keys.
INSERT INTO license_allocations (
    user_id, key_id, start_date, end_date, status, created_at
)
SELECT um.user_id, km.key_id,
       DATE_SUB(NOW(), INTERVAL (165 - km.key_seq) DAY),
       DATE_ADD(NOW(), INTERVAL 30 DAY),
       'Revoked',
       DATE_SUB(NOW(), INTERVAL (165 - km.key_seq) DAY)
FROM demo_key_map AS km
JOIN demo_user_map AS um
  ON um.user_seq = MOD(350 + km.software_seq * 29 + km.key_seq - 1, @demo_user_count) + 1
WHERE km.key_seq BETWEEN 11 AND 18;

-- Current allocations. Software 1 is sold out, 2 and 3 are nearly out of keys.
-- Four active rows intentionally have past end dates for the Copilot risk scenario.
INSERT INTO license_allocations (
    user_id, key_id, start_date, end_date, status, created_at
)
SELECT um.user_id, km.key_id,
       DATE_SUB(NOW(), INTERVAL (10 + MOD(km.software_seq * 17 + km.key_seq * 7, 110)) DAY),
       CASE
           WHEN km.software_seq = 1 AND km.key_seq <= 4
               THEN DATE_SUB(NOW(), INTERVAL km.key_seq DAY)
           WHEN km.software_seq = 1 AND km.key_seq = 5
               THEN DATE_ADD(NOW(), INTERVAL 1 DAY)
           WHEN km.software_seq = 1 AND km.key_seq BETWEEN 6 AND 9
               THEN DATE_ADD(NOW(), INTERVAL (km.key_seq - 3) DAY)
           WHEN km.software_seq = 2 AND km.key_seq <= 6
               THEN DATE_ADD(NOW(), INTERVAL (km.key_seq + 7) DAY)
           WHEN km.software_seq = 3 AND km.key_seq <= 5
               THEN DATE_ADD(NOW(), INTERVAL (km.key_seq + 20) DAY)
           ELSE DATE_ADD(NOW(), INTERVAL (45 + MOD(km.key_seq * 3, 100)) DAY)
       END,
       'Active',
       DATE_SUB(NOW(), INTERVAL (10 + MOD(km.software_seq * 17 + km.key_seq * 7, 110)) DAY)
FROM demo_key_map AS km
JOIN demo_user_map AS um
  ON um.user_seq = MOD((km.software_seq - 1) * 61 + km.key_seq - 1, @demo_user_count) + 1
WHERE km.key_seq <= CASE km.software_seq
    WHEN 1 THEN 40 WHEN 2 THEN 43 WHEN 3 THEN 45 ELSE 20 + km.software_seq END;

-- Activation evidence for all allocations.
INSERT INTO activation_logs (allocation_id, activation_time, ip_address)
SELECT id, DATE_ADD(start_date, INTERVAL 1 HOUR),
       CONCAT('10.20.', MOD(user_id, 20) + 1, '.', MOD(id, 240) + 10)
FROM license_allocations;

INSERT INTO revocation_logs (allocation_id, revocation_time, reason)
SELECT id, DATE_ADD(start_date, INTERVAL 21 DAY),
       CASE MOD(id, 3)
           WHEN 0 THEN 'Người dùng chuyển khoa'
           WHEN 1 THEN 'Hoàn tất môn học và trả license'
           ELSE 'Thiết bị không còn được sử dụng'
       END
FROM license_allocations
WHERE status = 'Revoked';

INSERT INTO expiry_notifications (allocation_id, sent_time, alert_type)
SELECT id, NOW(),
       CASE WHEN DATEDIFF(end_date, NOW()) <= 1 THEN '1_day' ELSE '7_days' END
FROM license_allocations
WHERE status = 'Active'
  AND DATEDIFF(end_date, NOW()) BETWEEN 1 AND 7;

-- Key flags and pool counters are derived from the real active rows, never hard-coded.
UPDATE license_keys SET is_assigned = 0, assigned_at = NULL;
UPDATE license_keys AS lk
JOIN (
    SELECT key_id, MIN(start_date) AS assigned_at
    FROM license_allocations
    WHERE status = 'Active'
    GROUP BY key_id
) AS active_allocations ON active_allocations.key_id = lk.id
SET lk.is_assigned = 1, lk.assigned_at = active_allocations.assigned_at;

UPDATE license_pools AS lp
SET lp.available_quantity = (
    SELECT COUNT(*) FROM license_keys AS lk
    WHERE lk.pool_id = lp.id AND lk.is_assigned = 0
);

-- Six monthly snapshots for every software/department pair.
INSERT INTO usage_stats (software_id, department_id, active_count, report_period, created_at)
SELECT sm.software_id, d.id,
       GREATEST(0,
           4 + sm.software_seq * 2 + d.id
           - month_numbers.n
           + MOD(sm.software_seq + d.id + month_numbers.n, 5)
       ),
       DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL (month_numbers.n - 1) MONTH), '%Y-%m'),
       TIMESTAMP(
           LAST_DAY(DATE_SUB(CURDATE(), INTERVAL (month_numbers.n - 1) MONTH)),
           '18:00:00'
       )
FROM demo_software_map AS sm
CROSS JOIN departments AS d
JOIN demo_numbers AS month_numbers ON month_numbers.n <= 6;

-- Audit trail spread across the last six months.
INSERT INTO audit_logs (
    actor, action, entity_type, entity_id, context_json, ip_address, created_at
)
SELECT CASE MOD(n.n, 4)
           WHEN 0 THEN 'admin@vnu.edu.vn'
           WHEN 1 THEN 'inventory.operator@vnu.edu.vn'
           WHEN 2 THEN 'allocation.manager@vnu.edu.vn'
           ELSE 'LicenseOS Scheduler'
       END,
       CASE MOD(n.n, 6)
           WHEN 0 THEN 'CREATE_ALLOCATION'
           WHEN 1 THEN 'IMPORT_LICENSE_KEYS'
           WHEN 2 THEN 'REVOKE_ALLOCATION'
           WHEN 3 THEN 'UPDATE_ALLOCATION_RULE'
           WHEN 4 THEN 'DOWNLOAD_SOFTWARE_ASSET'
           ELSE 'EXPIRY_RISK_SCAN'
       END,
       CASE MOD(n.n, 6)
           WHEN 0 THEN 'license_allocations'
           WHEN 1 THEN 'license_keys'
           WHEN 2 THEN 'license_allocations'
           WHEN 3 THEN 'allocation_rules'
           WHEN 4 THEN 'software_assets'
           ELSE 'system'
       END,
       n.n,
       JSON_OBJECT(
           'source', 'six_month_demo',
           'result', 'success',
           'sequence', n.n
       ),
       CONCAT('10.10.', MOD(n.n, 12) + 1, '.', MOD(n.n * 7, 240) + 10),
       DATE_SUB(NOW(), INTERVAL MOD(n.n * 11, 180) DAY)
FROM demo_numbers AS n
WHERE n.n <= 150;

-- Read-only Copilot history. Responses contain summaries only, never raw license keys.
INSERT INTO assistant_conversations (
    session_token, language, metadata_json, started_at, last_activity_at
)
SELECT MD5(CONCAT('licenseos-demo-session-', n.n)),
       CASE WHEN MOD(n.n, 3) = 0 THEN 'en' ELSE 'vi' END,
       JSON_OBJECT('source', 'six_month_demo', 'channel', 'web_widget'),
       DATE_SUB(NOW(), INTERVAL MOD(n.n * 9, 170) DAY),
       DATE_SUB(NOW(), INTERVAL MOD(n.n * 9, 170) DAY)
FROM demo_numbers AS n
WHERE n.n <= 18;

DROP TEMPORARY TABLE IF EXISTS demo_conversation_map;
CREATE TEMPORARY TABLE demo_conversation_map AS
SELECT n.n AS conversation_seq, c.id AS conversation_id, c.language, c.started_at
FROM demo_numbers AS n
JOIN assistant_conversations AS c
  ON c.session_token = MD5(CONCAT('licenseos-demo-session-', n.n))
WHERE n.n <= 18;
ALTER TABLE demo_conversation_map ADD PRIMARY KEY (conversation_seq);

INSERT INTO assistant_messages (
    conversation_id, sender, intent, message_text, response_json, status, duration_ms, created_at
)
SELECT conversation_id, 'User',
       CASE MOD(conversation_seq, 6)
           WHEN 0 THEN 'active_count'
           WHEN 1 THEN 'expiring_licenses'
           WHEN 2 THEN 'overdue_unrevoked'
           WHEN 3 THEN 'department_usage'
           WHEN 4 THEN 'low_stock_software'
           ELSE 'system_risk'
       END,
       CASE MOD(conversation_seq, 6)
           WHEN 0 THEN 'Hiện có bao nhiêu license đang active?'
           WHEN 1 THEN 'License nào sắp hết hạn trong 14 ngày?'
           WHEN 2 THEN 'Có license quá hạn nhưng chưa thu hồi không?'
           WHEN 3 THEN 'Khoa nào đang sử dụng nhiều license nhất?'
           WHEN 4 THEN 'Phần mềm nào sắp hết key?'
           ELSE 'Kiểm tra rủi ro hệ thống'
       END,
       NULL, 'Success', NULL, started_at
FROM demo_conversation_map;

INSERT INTO assistant_messages (
    conversation_id, sender, intent, message_text, response_json, status, duration_ms, created_at
)
SELECT conversation_id, 'Assistant',
       CASE MOD(conversation_seq, 6)
           WHEN 0 THEN 'active_count'
           WHEN 1 THEN 'expiring_licenses'
           WHEN 2 THEN 'overdue_unrevoked'
           WHEN 3 THEN 'department_usage'
           WHEN 4 THEN 'low_stock_software'
           ELSE 'system_risk'
       END,
       CASE MOD(conversation_seq, 6)
           WHEN 0 THEN 'Đã tổng hợp số license active từ dữ liệu cấp phát hiện tại.'
           WHEN 1 THEN 'Đã tìm thấy các license cần theo dõi trong 14 ngày tới.'
           WHEN 2 THEN 'Phát hiện 4 license đã quá hạn nhưng vẫn đang ở trạng thái Active.'
           WHEN 3 THEN 'Đã xếp hạng nhu cầu license theo khoa/phòng ban.'
           WHEN 4 THEN 'Có 3 phần mềm đang ở mức tồn kho thấp hoặc đã hết key.'
           ELSE 'Đã hoàn tất kiểm tra tồn kho, hết hạn và thu hồi.'
       END,
       JSON_OBJECT(
           'intent', CASE MOD(conversation_seq, 6)
               WHEN 0 THEN 'active_count'
               WHEN 1 THEN 'expiring_licenses'
               WHEN 2 THEN 'overdue_unrevoked'
               WHEN 3 THEN 'department_usage'
               WHEN 4 THEN 'low_stock_software'
               ELSE 'system_risk'
           END,
           'title', 'LicenseOS Assistant demo insight',
           'summary', 'Kết quả được tổng hợp bằng truy vấn chỉ đọc đã kiểm soát.',
           'severity', CASE WHEN MOD(conversation_seq, 6) IN (2,4,5) THEN 'warning' ELSE 'info' END,
           'privacy', 'No raw license key returned',
           'source', 'six_month_demo'
       ),
       'Success', 80 + conversation_seq * 7,
       DATE_ADD(started_at, INTERVAL 2 SECOND)
FROM demo_conversation_map;

COMMIT;

-- Import summary shown by phpMyAdmin/mysql after a successful run.
SELECT 'departments' AS metric, COUNT(*) AS total FROM departments
UNION ALL SELECT 'users', COUNT(*) FROM users
UNION ALL SELECT 'software_titles', COUNT(*) FROM software_titles
UNION ALL SELECT 'software_assets', COUNT(*) FROM software_assets
UNION ALL SELECT 'allocation_rules', COUNT(*) FROM allocation_rules
UNION ALL SELECT 'license_pools', COUNT(*) FROM license_pools
UNION ALL SELECT 'license_keys', COUNT(*) FROM license_keys
UNION ALL SELECT 'active_allocations', COUNT(*) FROM license_allocations WHERE status = 'Active'
UNION ALL SELECT 'expired_allocations', COUNT(*) FROM license_allocations WHERE status = 'Expired'
UNION ALL SELECT 'revoked_allocations', COUNT(*) FROM license_allocations WHERE status = 'Revoked'
UNION ALL SELECT 'overdue_active_risks', COUNT(*) FROM license_allocations WHERE status = 'Active' AND end_date < NOW()
UNION ALL SELECT 'usage_snapshots', COUNT(*) FROM usage_stats
UNION ALL SELECT 'audit_events', COUNT(*) FROM audit_logs
UNION ALL SELECT 'assistant_conversations', COUNT(*) FROM assistant_conversations
UNION ALL SELECT 'assistant_messages', COUNT(*) FROM assistant_messages;
