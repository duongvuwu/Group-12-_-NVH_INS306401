USE license_management_db;

ALTER TABLE license_pools
    ADD COLUMN IF NOT EXISTS expires_at DATE NULL AFTER purchase_date,
    ADD COLUMN IF NOT EXISTS reusable_after_revocation TINYINT(1) NOT NULL DEFAULT 1 AFTER expires_at;
