-- Migration script for existing tenant databases
-- Run this to add units system to existing databases

-- Add prefix column to categories
ALTER TABLE categories ADD COLUMN IF NOT EXISTS prefix VARCHAR(5);

-- Update existing categories with auto-prefixes
UPDATE categories SET prefix = UPPER(SUBSTRING(name, 1, 2)) WHERE prefix IS NULL;

-- Create units table
CREATE TABLE IF NOT EXISTS units (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  sort_order INT NOT NULL
);

-- Insert units if empty
INSERT IGNORE INTO units (id, name, sort_order) VALUES
(1, 'Dus', 1), (2, 'Bal', 2), (3, 'Karung', 3), (4, 'Ikat', 4),
(5, 'Tim', 5), (6, 'Pack', 6), (7, 'Pcs', 7), (8, 'Btl', 8),
(9, 'Bks', 9), (10, 'Kg', 10), (11, '1/2 Kg', 11), (12, '1/4 Kg', 12), (13, 'Ons', 13),
(14, 'Krat', 14), (15, 'Kodi', 15), (16, 'Lusin', 16),
(17, 'Pail', 17), (18, 'Meter', 18), (19, 'Jerigen', 19), (20, 'Papan', 20);

-- Create sku_sequences table
CREATE TABLE IF NOT EXISTS sku_sequences (
  category_id INT NOT NULL,
  year INT NOT NULL,
  last_number INT DEFAULT 0,
  PRIMARY KEY (category_id, year)
);

-- Add base_unit_id to products if not exists
ALTER TABLE products ADD COLUMN IF NOT EXISTS base_unit_id INT;

-- Modify stock to decimal
ALTER TABLE products MODIFY COLUMN stock DECIMAL(15,4) DEFAULT 0;
ALTER TABLE products MODIFY COLUMN min_stock DECIMAL(15,4) DEFAULT 5;

-- Create product_units table
CREATE TABLE IF NOT EXISTS product_units (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  unit_id INT NOT NULL,
  conversion_qty DECIMAL(15,4) NOT NULL DEFAULT 1,
  buy_price DECIMAL(15,2) DEFAULT 0,
  sell_price DECIMAL(15,2) DEFAULT 0,
  weight DECIMAL(10,2) DEFAULT 0,
  is_base_unit BOOLEAN DEFAULT FALSE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- Create index for new table
CREATE INDEX IF NOT EXISTS idx_product_units_product ON product_units(product_id);

-- Migrate existing products to have Pcs as default unit
INSERT INTO product_units (product_id, unit_id, conversion_qty, buy_price, sell_price, is_base_unit, sort_order)
SELECT p.id, 7, 1, p.buy_price, p.sell_price, TRUE, 0
FROM products p
LEFT JOIN product_units pu ON p.id = pu.product_id
WHERE pu.id IS NULL;

-- Update products base_unit_id to Pcs (7) for those without
UPDATE products SET base_unit_id = 7 WHERE base_unit_id IS NULL;

-- Add more categories if needed
INSERT IGNORE INTO categories (name, prefix) VALUES 
('Makanan', 'MA'),
('Minuman', 'MI'),
('Sembako', 'SE'),
('Snack', 'SN'),
('Rokok', 'RO'),
('ATK', 'AT'),
('Elektronik', 'EL'),
('Kosmetik', 'KO'),
('Obat', 'OB');

SELECT 'Migration completed successfully!' as message;
