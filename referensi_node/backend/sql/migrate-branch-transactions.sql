-- ============================================
-- MIGRATION: Add branch_id to shifts and transactions tables
-- Run this on each tenant database
-- ============================================

-- Add branch_id to shifts table
ALTER TABLE shifts ADD COLUMN branch_id INT NULL AFTER user_id;
ALTER TABLE shifts ADD INDEX idx_shifts_branch (branch_id);
ALTER TABLE shifts ADD FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL;

-- Add branch_id to transactions table
ALTER TABLE transactions ADD COLUMN branch_id INT NULL AFTER shift_id;
ALTER TABLE transactions ADD INDEX idx_transactions_branch (branch_id);
ALTER TABLE transactions ADD FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL;

-- Update existing shifts to use default branch (Pusat = id 1)
UPDATE shifts SET branch_id = 1 WHERE branch_id IS NULL;

-- Update existing transactions based on their shift's branch
UPDATE transactions t
JOIN shifts s ON t.shift_id = s.id
SET t.branch_id = s.branch_id
WHERE t.branch_id IS NULL;

SELECT 'Branch filtering migration completed!' as message;
