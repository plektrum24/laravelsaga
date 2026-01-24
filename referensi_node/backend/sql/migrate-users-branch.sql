-- ============================================
-- MIGRATION: Add branch_id to users table (Main DB)
-- ============================================

USE saga_posv2_main;

ALTER TABLE users 
ADD COLUMN branch_id INT NULL AFTER tenant_id;

-- Add index
CREATE INDEX idx_users_branch ON users(branch_id);

SELECT 'Main database users table migrated successfully!' as message;
