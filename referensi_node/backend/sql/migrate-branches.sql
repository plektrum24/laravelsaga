-- ============================================
-- MIGRATION: Add Multi-Branch Support
-- Run this on existing tenant databases
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

-- Create default main branch and migrate existing stock
INSERT INTO branches (name, code, is_main) VALUES ('Pusat', 'PUSAT', TRUE);

-- Migrate existing product stock to branch_stock for main branch
INSERT INTO branch_stock (branch_id, product_id, stock, min_stock)
SELECT 1, id, stock, min_stock FROM products WHERE is_active = true;

SELECT 'Multi-branch migration completed!' as message;
