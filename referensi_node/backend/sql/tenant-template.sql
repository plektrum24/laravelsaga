-- SAGA TOKO APP - Tenant Database Template
-- This script is used as a template when creating new tenant databases
-- Replace {DATABASE_NAME} with actual tenant database name

CREATE DATABASE IF NOT EXISTS `{DATABASE_NAME}`;
USE `{DATABASE_NAME}`;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  prefix VARCHAR(5),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Units table (master units for all products)
CREATE TABLE IF NOT EXISTS units (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  sort_order INT NOT NULL
);

-- Default units (large → small)
INSERT INTO units (name, sort_order) VALUES
('Dus', 1), ('Bal', 2), ('Karung', 3), ('Ikat', 4),
('Tim', 5), ('Pack', 6), ('Pcs', 7), ('Btl', 8),
('Bks', 9), ('Kg', 10), ('1/2 Kg', 11), ('1/4 Kg', 12), ('Ons', 13),
('Krat', 14), ('Kodi', 15), ('Lusin', 16),
('Pail', 17), ('Meter', 18), ('Jerigen', 19), ('Papan', 20), ('Kaleng', 21);

-- SKU sequences for auto-generation
CREATE TABLE IF NOT EXISTS sku_sequences (
  category_id INT NOT NULL,
  year INT NOT NULL,
  last_number INT DEFAULT 0,
  PRIMARY KEY (category_id, year)
);

-- ============================================
-- MULTI-BRANCH SYSTEM (Must be before products for FK)
-- ============================================

-- Branches table
CREATE TABLE IF NOT EXISTS branches (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  code VARCHAR(20) UNIQUE,
  address TEXT,
  phone VARCHAR(20),
  is_main BOOLEAN DEFAULT FALSE,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Seed default main branch
INSERT INTO branches (name, code, is_main) VALUES ('Pusat', 'PUSAT', TRUE);

-- Products table
CREATE TABLE IF NOT EXISTS products (
  id INT PRIMARY KEY AUTO_INCREMENT,
  sku VARCHAR(50) UNIQUE NOT NULL,
  name VARCHAR(255) NOT NULL,
  category_id INT,
  base_unit_id INT,
  stock DECIMAL(15,4) DEFAULT 0,
  min_stock DECIMAL(15,4) DEFAULT 5,
  image_url VARCHAR(500),
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
  FOREIGN KEY (base_unit_id) REFERENCES units(id) ON DELETE SET NULL
);

-- Product units for conversions and pricing
CREATE TABLE IF NOT EXISTS product_units (
  id INT PRIMARY KEY AUTO_INCREMENT,
  product_id INT NOT NULL,
  unit_id INT NOT NULL,
  conversion_qty DECIMAL(15,4) NOT NULL DEFAULT 1,
  buy_price DECIMAL(15,2) DEFAULT 0,
  sell_price DECIMAL(15,2) DEFAULT 0,
  weight DECIMAL(10,2) DEFAULT 0, -- Weight in Grams
  is_base_unit BOOLEAN DEFAULT FALSE,
  sort_order INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- Suppliers table
CREATE TABLE IF NOT EXISTS suppliers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  contact_person VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Purchases table (Goods In)
CREATE TABLE IF NOT EXISTS purchases (
  id INT PRIMARY KEY AUTO_INCREMENT,
  branch_id INT,
  supplier_id INT,
  invoice_number VARCHAR(50),
  date DATE NOT NULL,
  due_date DATE,
  total_amount DECIMAL(15,2) DEFAULT 0,
  paid_amount DECIMAL(15,2) DEFAULT 0,
  payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
);

-- Purchase Items table
CREATE TABLE IF NOT EXISTS purchase_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  purchase_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity DECIMAL(15,4) NOT NULL,
  unit_price DECIMAL(15,2) NOT NULL,
  subtotal DECIMAL(15,2) NOT NULL,
  unit_id INT,
  expiry_date DATE,
  current_stock DECIMAL(15,4) DEFAULT NULL,
  conversion_qty DECIMAL(15,4) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
);

-- Shifts table
CREATE TABLE IF NOT EXISTS shifts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  branch_id INT NULL,
  start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  end_time TIMESTAMP NULL,
  opening_cash DECIMAL(15,2) NOT NULL DEFAULT 0,
  closing_cash DECIMAL(15,2) NULL,
  status ENUM('open', 'closed') DEFAULT 'open',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Transactions table
CREATE TABLE IF NOT EXISTS transactions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  shift_id INT NULL,
  branch_id INT NULL,
  invoice_number VARCHAR(50) NOT NULL,
  subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
  discount DECIMAL(15,2) DEFAULT 0,
  tax DECIMAL(15,2) DEFAULT 0,
  total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  payment_method ENUM('cash', 'debit', 'credit', 'qris') NOT NULL DEFAULT 'cash',
  payment_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
  change_amount DECIMAL(15,2) DEFAULT 0,
  status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
  -- Android/Mobile fields
  sales_id INT NULL,                    -- ID Sales untuk tracking penjualan per sales
  latitude DECIMAL(10,8) NULL,          -- GPS latitude koordinat transaksi
  longitude DECIMAL(11,8) NULL,         -- GPS longitude koordinat transaksi
  customer_name VARCHAR(100) NULL,      -- Nama pelanggan (opsional)
  customer_phone VARCHAR(20) NULL,      -- No HP pelanggan (opsional)
  notes TEXT NULL,                      -- Catatan transaksi
  device_id VARCHAR(100) NULL,          -- Device ID untuk tracking
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (shift_id) REFERENCES shifts(id),
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL
);

-- Transaction items table
CREATE TABLE IF NOT EXISTS transaction_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  transaction_id INT NOT NULL,
  product_id INT NOT NULL,
  product_name VARCHAR(255) NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price DECIMAL(15,2) NOT NULL,
  subtotal DECIMAL(15,2) NOT NULL,
  unit_name VARCHAR(50) DEFAULT 'Pcs',
  buy_price DECIMAL(15,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100),
  phone VARCHAR(20),
  address TEXT,
  credit_limit DECIMAL(15,2) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX idx_products_sku ON products(sku);
CREATE INDEX idx_products_category ON products(category_id);
CREATE INDEX idx_products_stock ON products(stock, min_stock);
CREATE INDEX idx_product_units_product ON product_units(product_id);
CREATE INDEX idx_transactions_shift ON transactions(shift_id);
CREATE INDEX idx_transactions_status ON transactions(status);
CREATE INDEX idx_transactions_date ON transactions(created_at);
CREATE INDEX idx_shifts_user ON shifts(user_id);
CREATE INDEX idx_shifts_status ON shifts(status);
CREATE INDEX idx_purchases_supplier ON purchases(supplier_id);
CREATE INDEX idx_purchases_date ON purchases(date);
CREATE INDEX idx_purchase_items_purchase ON purchase_items(purchase_id);
CREATE INDEX idx_purchase_items_product ON purchase_items(product_id);

-- Seed default categories with prefix (2 letters for SKU)
INSERT INTO categories (name, prefix) VALUES 
('General', 'GE'),
('Makanan', 'MA'),
('Minuman', 'MI'),
('Sembako', 'SE'),
('Snack', 'SN'),
('Rokok', 'RO'),
('ATK', 'AT'),
('Elektronik', 'EL'),
('Kosmetik', 'KO'),
('Obat', 'OB');

-- Branch stock (separate stock per branch)
CREATE TABLE IF NOT EXISTS branch_stock (
  id INT PRIMARY KEY AUTO_INCREMENT,
  branch_id INT NOT NULL,
  product_id INT NOT NULL,
  stock DECIMAL(15,4) DEFAULT 0,
  min_stock DECIMAL(15,4) DEFAULT 5,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  UNIQUE KEY unique_branch_product (branch_id, product_id)
);

-- Stock transfers between branches
CREATE TABLE IF NOT EXISTS stock_transfers (
  id INT PRIMARY KEY AUTO_INCREMENT,
  transfer_number VARCHAR(50) UNIQUE NOT NULL,
  from_branch_id INT NOT NULL,
  to_branch_id INT NOT NULL,
  status ENUM('pending', 'in_transit', 'received', 'cancelled') DEFAULT 'pending',
  notes TEXT,
  created_by INT,
  approved_by INT,
  received_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  approved_at TIMESTAMP NULL,
  received_at TIMESTAMP NULL,
  FOREIGN KEY (from_branch_id) REFERENCES branches(id),
  FOREIGN KEY (to_branch_id) REFERENCES branches(id)
);

-- Stock transfer line items
CREATE TABLE IF NOT EXISTS stock_transfer_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  transfer_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity DECIMAL(15,4) NOT NULL,
  unit_id INT,
  notes VARCHAR(255),
  FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (unit_id) REFERENCES units(id)
);

-- Indexes for branch tables
CREATE INDEX idx_branch_stock_branch ON branch_stock(branch_id);
CREATE INDEX idx_branch_stock_product ON branch_stock(product_id);
CREATE INDEX idx_transfers_from ON stock_transfers(from_branch_id);
CREATE INDEX idx_transfers_to ON stock_transfers(to_branch_id);
CREATE INDEX idx_transfers_status ON stock_transfers(status);
CREATE INDEX idx_transfer_items_transfer ON stock_transfer_items(transfer_id);

-- ============================================
-- SUPPLIER RETURNS SYSTEM
-- ============================================

-- Purchase Returns Header
CREATE TABLE IF NOT EXISTS purchase_returns (
  id INT PRIMARY KEY AUTO_INCREMENT,
  branch_id INT,
  supplier_id INT NOT NULL,
  return_number VARCHAR(50) NOT NULL,
  date DATE NOT NULL,
  total_amount DECIMAL(15,2) DEFAULT 0,
  status ENUM('draft', 'completed', 'cancelled') DEFAULT 'draft',
  reason ENUM('expired', 'damaged', 'wrong_item', 'quality_issue', 'other') DEFAULT 'other',
  notes TEXT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
  FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE CASCADE
);

-- Purchase Return Items (links to specific batch)
CREATE TABLE IF NOT EXISTS purchase_return_items (
  id INT PRIMARY KEY AUTO_INCREMENT,
  return_id INT NOT NULL,
  purchase_item_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity DECIMAL(15,4) NOT NULL,
  unit_id INT,
  unit_price DECIMAL(15,2) NOT NULL,
  subtotal DECIMAL(15,2) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (return_id) REFERENCES purchase_returns(id) ON DELETE CASCADE,
  FOREIGN KEY (purchase_item_id) REFERENCES purchase_items(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
);

-- Indexes for supplier returns
CREATE INDEX idx_purchase_returns_supplier ON purchase_returns(supplier_id);
CREATE INDEX idx_purchase_returns_date ON purchase_returns(date);
CREATE INDEX idx_purchase_returns_status ON purchase_returns(status);
CREATE INDEX idx_purchase_return_items_return ON purchase_return_items(return_id);
CREATE INDEX idx_purchase_return_items_batch ON purchase_return_items(purchase_item_id);

SELECT 'Tenant database initialized successfully!' as message;
