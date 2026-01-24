const mysql = require('mysql2/promise');
const dotenv = require('dotenv');
const path = require('path');

dotenv.config({ path: path.join(__dirname, '.env') });

const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: 'saga_tenant_bkt0001' // Target dev tenant
};

async function fixSchema() {
    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        console.log(`Connected to ${dbConfig.database}`);

        // 1. Create Customers Table
        await connection.query(`
            CREATE TABLE IF NOT EXISTS customers (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        `);
        console.log('✅ Created customers table');

        // 2. Create Purchases Table (Supplier Debt)
        await connection.query(`
            CREATE TABLE IF NOT EXISTS purchases (
                id INT PRIMARY KEY AUTO_INCREMENT,
                supplier_id INT,
                invoice_number VARCHAR(50),
                date DATE NOT NULL,
                due_date DATE,
                total_amount DECIMAL(15,2) DEFAULT 0,
                paid_amount DECIMAL(15,2) DEFAULT 0,
                payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
                notes TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
            )
        `);
        console.log('✅ Created purchases table');

        // 3. Create Purchase Items Table
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
            )
        `);
        console.log('✅ Created purchase_items table');

        // 4. Update Transactions Table (Customer Receivables)
        // Check columns first to avoid duplicate errors
        const [columns] = await connection.query(`SHOW COLUMNS FROM transactions`);
        const colNames = columns.map(c => c.Field);

        if (!colNames.includes('customer_id')) {
            await connection.query(`ALTER TABLE transactions ADD COLUMN customer_id INT NULL AFTER shift_id`);
            await connection.query(`ALTER TABLE transactions ADD CONSTRAINT fk_trans_customer FOREIGN KEY (customer_id) REFERENCES customers(id)`);
            console.log('Example: Added customer_id');
        }

        if (!colNames.includes('payment_status')) {
            await connection.query(`ALTER TABLE transactions ADD COLUMN payment_status ENUM('paid', 'unpaid', 'partial', 'debt') DEFAULT 'paid' AFTER total_amount`);
            console.log('Example: Added payment_status');
        }

        // Modify ENUM if it exists (mysql doesn't support "IF NOT EXISTS" for enum values easily, so we just alter it)
        // We will just execute the ALTER to be safe it includes 'debt' and 'unpaid'
        try {
            await connection.query(`ALTER TABLE transactions MODIFY COLUMN payment_status ENUM('paid', 'unpaid', 'partial', 'debt') DEFAULT 'paid'`);
        } catch (e) { console.log('Enum update note:', e.message); }


        if (!colNames.includes('due_date')) {
            await connection.query(`ALTER TABLE transactions ADD COLUMN due_date DATE NULL AFTER payment_status`);
            console.log('Example: Added due_date');
        }

        console.log('✅ Schema update complete');

    } catch (error) {
        console.error('❌ Schema update failed:', error);
    } finally {
        if (connection) await connection.end();
    }
}

fixSchema();
