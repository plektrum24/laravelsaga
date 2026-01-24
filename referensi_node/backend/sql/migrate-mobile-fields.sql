-- SAGA TOKO APP - Migration Script: Add Mobile/Android Fields
-- Run this script on existing tenant databases to add GPS and Sales tracking

USE `{DATABASE_NAME}`;

-- Add mobile/android fields to transactions table
ALTER TABLE transactions
  ADD COLUMN sales_id INT NULL AFTER status,
  ADD COLUMN latitude DECIMAL(10,8) NULL AFTER sales_id,
  ADD COLUMN longitude DECIMAL(11,8) NULL AFTER latitude,
  ADD COLUMN customer_name VARCHAR(100) NULL AFTER longitude,
  ADD COLUMN customer_phone VARCHAR(20) NULL AFTER customer_name,
  ADD COLUMN notes TEXT NULL AFTER customer_phone,
  ADD COLUMN device_id VARCHAR(100) NULL AFTER notes;

-- Add index for sales_id for faster lookup
CREATE INDEX idx_transactions_sales ON transactions(sales_id);

-- Add index for GPS coordinates (for location-based queries)
CREATE INDEX idx_transactions_location ON transactions(latitude, longitude);

SELECT 'Mobile fields migration completed!' as message;
