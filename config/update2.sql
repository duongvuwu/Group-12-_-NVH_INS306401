-- update2.sql
-- Fix the live schema used by the allocation workflow.
-- Safe to rerun: the column is added only when it does not already exist.
USE license_management_db;

SET @assigned_at_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'license_keys'
      AND COLUMN_NAME = 'assigned_at'
);

SET @migration_sql := IF(
    @assigned_at_exists = 0,
    'ALTER TABLE license_keys ADD COLUMN assigned_at DATETIME NULL AFTER is_assigned',
    'SELECT ''license_keys.assigned_at already exists'' AS migration_status'
);

PREPARE migration_stmt FROM @migration_sql;
EXECUTE migration_stmt;
DEALLOCATE PREPARE migration_stmt;

-- Repair assignment timestamps for any pre-existing active allocations.
UPDATE license_keys lk
JOIN license_allocations la ON la.key_id = lk.id AND la.status = 'Active'
SET lk.is_assigned = 1,
    lk.assigned_at = COALESCE(lk.assigned_at, la.start_date);

-- Keep cached pool availability consistent with the real key rows.
UPDATE license_pools lp
SET lp.available_quantity = (
    SELECT COUNT(*)
    FROM license_keys lk
    WHERE lk.pool_id = lp.id
      AND lk.is_assigned = 0
);
