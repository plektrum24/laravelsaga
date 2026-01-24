const mysql = require('mysql2/promise');
require('dotenv').config();

async function migrate() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: 'saga_tenant_jkt001',
        multipleStatements: true
    });

    console.log('Connected to database...');

    try {
        // Create branches table
        await connection.execute(`
            CREATE TABLE IF NOT EXISTS branches (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(50) NOT NULL,
                address TEXT,
                phone VARCHAR(50),
                is_main BOOLEAN DEFAULT FALSE,
                is_active BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY unique_code (code)
            )
        `);
        console.log('✓ Created branches table');

        // Create branch_stock table
        await connection.execute(`
            CREATE TABLE IF NOT EXISTS branch_stock (
                id INT PRIMARY KEY AUTO_INCREMENT,
                branch_id INT NOT NULL,
                product_id INT NOT NULL,
                stock DECIMAL(15,4) DEFAULT 0,
                min_stock DECIMAL(15,4) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                UNIQUE KEY unique_branch_product (branch_id, product_id)
            )
        `);
        console.log('✓ Created branch_stock table');

        // Create stock_transfers table
        await connection.execute(`
            CREATE TABLE IF NOT EXISTS stock_transfers (
                id INT PRIMARY KEY AUTO_INCREMENT,
                transfer_number VARCHAR(50) NOT NULL UNIQUE,
                from_branch_id INT NOT NULL,
                to_branch_id INT NOT NULL,
                status ENUM('pending', 'approved', 'in_transit', 'completed', 'cancelled', 'rejected') DEFAULT 'pending',
                created_by INT,
                approved_by INT,
                received_by INT,
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (from_branch_id) REFERENCES branches(id),
                FOREIGN KEY (to_branch_id) REFERENCES branches(id)
            )
        `);
        console.log('✓ Created stock_transfers table');

        // Create stock_transfer_items table
        await connection.execute(`
            CREATE TABLE IF NOT EXISTS stock_transfer_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                transfer_id INT NOT NULL,
                product_id INT NOT NULL,
                qty_requested DECIMAL(15,4) NOT NULL,
                qty_approved DECIMAL(15,4) DEFAULT 0,
                qty_received DECIMAL(15,4) DEFAULT 0,
                notes TEXT,
                FOREIGN KEY (transfer_id) REFERENCES stock_transfers(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )
        `);
        console.log('✓ Created stock_transfer_items table');

        console.log('\n✅ Branch Migration completed successfully!');
    } catch (error) {
        console.error('Migration error:', error);
    } finally {
        await connection.end();
    }
}

migrate();
