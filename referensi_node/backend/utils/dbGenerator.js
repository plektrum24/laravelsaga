const { getMainPool } = require('../config/database');

/**
 * Create a new tenant database with all required tables
 * @param {string} databaseName - The database name to create
 */
const createTenantDatabase = async (databaseName) => {
  const pool = await getMainPool();
  const connection = await pool.getConnection();

  try {
    // Create database
    await connection.query(`CREATE DATABASE IF NOT EXISTS \`${databaseName}\``);

    // Use the new database
    await connection.query(`USE \`${databaseName}\``);

    // Create branches table (Moved up due to FK dependencies)
    await connection.query(`
      CREATE TABLE IF NOT EXISTS branches (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        address TEXT,
        phone VARCHAR(20),
        is_active BOOLEAN DEFAULT TRUE,
        is_main BOOLEAN DEFAULT FALSE,
        code VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB
    `);

    // Create categories table (ADDED prefix)
    await connection.query(`
      CREATE TABLE IF NOT EXISTS categories (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        prefix VARCHAR(10),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB
    `);

    // Create sku_sequences table (ADDED)
    await connection.query(`
      CREATE TABLE IF NOT EXISTS sku_sequences (
        id INT PRIMARY KEY AUTO_INCREMENT,
        category_id INT NOT NULL,
        year INT NOT NULL,
        last_number INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
        UNIQUE KEY unique_sequence (category_id, year)
      ) ENGINE=InnoDB
    `);

    // Create units table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS units (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(50) NOT NULL,
        short_name VARCHAR(20) NOT NULL,
        type ENUM('weight', 'volume', 'quantity') DEFAULT 'quantity',
        is_active BOOLEAN DEFAULT TRUE,
        sort_order INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB
    `);

    // Create products table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS products (
        id INT PRIMARY KEY AUTO_INCREMENT,
        sku VARCHAR(50) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        category_id INT,
        base_unit_id INT,
        buy_price DECIMAL(15,2) NOT NULL DEFAULT 0,
        sell_price DECIMAL(15,2) NOT NULL DEFAULT 0,
        stock INT DEFAULT 0,
        min_stock INT DEFAULT 5,
        image_url VARCHAR(500),
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
        FOREIGN KEY (base_unit_id) REFERENCES units(id) ON DELETE SET NULL
      ) ENGINE=InnoDB
    `);

    // Create product_units table (FIXED columns)
    await connection.query(`
      CREATE TABLE IF NOT EXISTS product_units (
        id INT PRIMARY KEY AUTO_INCREMENT,
        product_id INT NOT NULL,
        unit_id INT NOT NULL,
        conversion_qty DECIMAL(10,4) NOT NULL DEFAULT 1,
        buy_price DECIMAL(15,2) DEFAULT 0,
        sell_price DECIMAL(15,2) DEFAULT 0,
        is_base_unit BOOLEAN DEFAULT FALSE,
        sort_order INT DEFAULT 0,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
        UNIQUE KEY unique_product_unit (product_id, unit_id)
      ) ENGINE=InnoDB
    `);

    // Create customers table (MUST be before transactions for FK)
    await connection.query(`
      CREATE TABLE IF NOT EXISTS customers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        credit_limit DECIMAL(15,2) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=InnoDB
    `);

    // Create suppliers table (MUST be before purchases for FK)
    await connection.query(`
      CREATE TABLE IF NOT EXISTS suppliers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        contact_person VARCHAR(100),
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
      ) ENGINE=InnoDB
    `);

    // Create shifts table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS shifts (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT NOT NULL,
        start_time TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        end_time TIMESTAMP NULL,
        opening_cash DECIMAL(15,2) NOT NULL DEFAULT 0,
        closing_cash DECIMAL(15,2) NULL,
        status ENUM('open', 'closed') DEFAULT 'open',
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      ) ENGINE=InnoDB
    `);

    // Create transactions table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS transactions (
        id INT PRIMARY KEY AUTO_INCREMENT,
        shift_id INT NOT NULL,
        branch_id INT NULL,
        customer_id INT NULL,
        invoice_number VARCHAR(50) NOT NULL,
        subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
        discount DECIMAL(15,2) DEFAULT 0,
        tax DECIMAL(15,2) DEFAULT 0,
        total_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        payment_method ENUM('cash', 'debit', 'credit', 'qris', 'debt') NOT NULL DEFAULT 'cash',
        payment_amount DECIMAL(15,2) NOT NULL DEFAULT 0,
        change_amount DECIMAL(15,2) DEFAULT 0,
        status ENUM('completed', 'pending', 'cancelled') DEFAULT 'completed',
        payment_status ENUM('paid', 'unpaid', 'partial', 'debt') DEFAULT 'paid',
        due_date DATE NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (shift_id) REFERENCES shifts(id),
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
        FOREIGN KEY (customer_id) REFERENCES customers(id)
      ) ENGINE=InnoDB
    `);

    // Create transaction_items table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS transaction_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        transaction_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        unit_price DECIMAL(15,2) NOT NULL,
        subtotal DECIMAL(15,2) NOT NULL,
        unit_name VARCHAR(50) DEFAULT 'Pcs',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
      ) ENGINE=InnoDB
    `);

    // Create purchases table (Supplier Debt)
    await connection.query(`
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
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
      ) ENGINE=InnoDB
    `);

    // Create purchase_items table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS purchase_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        purchase_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(15,2) NOT NULL,
        subtotal DECIMAL(15,2) NOT NULL,
        unit_id INT,
        FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id),
        FOREIGN KEY (unit_id) REFERENCES units(id)
      ) ENGINE=InnoDB
    `);

    // Create branch_stock table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS branch_stock (
        id INT PRIMARY KEY AUTO_INCREMENT,
        branch_id INT NOT NULL,
        product_id INT NOT NULL,
        stock INT DEFAULT 0,
        min_stock INT DEFAULT 5,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        UNIQUE KEY unique_branch_product (branch_id, product_id)
      ) ENGINE=InnoDB
    `);

    // Create stock_transfers table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS stock_transfers (
        id INT PRIMARY KEY AUTO_INCREMENT,
        from_branch_id INT NOT NULL,
        to_branch_id INT NOT NULL,
        status ENUM('pending', 'shipped', 'received', 'cancelled') DEFAULT 'pending',
        items_count INT DEFAULT 0,
        notes TEXT,
        created_by INT,
        approved_by INT,
        received_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        approved_at TIMESTAMP NULL,
        received_at TIMESTAMP NULL,
        FOREIGN KEY (from_branch_id) REFERENCES branches(id),
        FOREIGN KEY (to_branch_id) REFERENCES branches(id)
      ) ENGINE=InnoDB
    `);

    // Create stock_transfer_items table
    await connection.query(`
      CREATE TABLE IF NOT EXISTS stock_transfer_items (
        id INT PRIMARY KEY AUTO_INCREMENT,
        transfer_id INT NOT NULL,
        product_id INT NOT NULL,
        qty_requested INT NOT NULL,
        qty_approved INT DEFAULT 0,
        qty_received DECIMAL(10,2) DEFAULT 0,
        unit_id INT,
        notes TEXT,
        FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL
      ) ENGINE=InnoDB
    `);

    // Insert Default Branch (Pusat) with is_main=1 and code
    await connection.query(`
      INSERT INTO branches (name, address, phone, is_active, is_main, code)
      VALUES ('Pusat', 'Main Office', '', 1, 1, 'CAB-001')
    `);

    // Insert default category (WITH PREFIX)
    await connection.query(`
      INSERT INTO categories (name, prefix) VALUES ('General', 'GEN')
      ON DUPLICATE KEY UPDATE prefix = 'GEN'
    `);

    // Insert default units
    await connection.query(`
      INSERT INTO units (name, short_name, type) VALUES
      ('Pcs', 'pcs', 'quantity'),
      ('Kilogram', 'kg', 'weight'),
      ('Liter', 'l', 'volume'),
      ('Box', 'box', 'quantity')
      ON DUPLICATE KEY UPDATE name = name
    `);

    console.log(`✅ Tenant database created: ${databaseName}`);
    return true;
  } catch (error) {
    console.error(`❌ Failed to create tenant database: ${databaseName}`, error);
    throw error;
  } finally {
    connection.release();
  }
};

/**
 * Drop a tenant database (use with caution!)
 * @param {string} databaseName - The database name to drop
 */
const dropTenantDatabase = async (databaseName) => {
  const pool = await getMainPool();

  try {
    await pool.query(`DROP DATABASE IF EXISTS \`${databaseName}\``);
    console.log(`🗑️ Tenant database dropped: ${databaseName}`);
    return true;
  } catch (error) {
    console.error(`❌ Failed to drop tenant database: ${databaseName}`, error);
    throw error;
  }
};

module.exports = {
  createTenantDatabase,
  dropTenantDatabase
};
