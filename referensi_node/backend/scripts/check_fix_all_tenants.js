/**
 * Check and Fix purchase_items table in all tenant databases
 */
const mysql = require('mysql2/promise');
require('dotenv').config({ path: __dirname + '/../.env' });

async function checkAndFix() {
    console.log('=== Checking purchase_items in all tenant databases ===\n');

    const mainPool = await mysql.createPool({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'saga_main',
        waitForConnections: true,
        connectionLimit: 5
    });

    try {
        // Get ALL tenants (not just active)
        const [tenants] = await mainPool.execute('SELECT id, code, database_name, status FROM tenants');
        console.log(`Found ${tenants.length} tenants total\n`);

        for (const tenant of tenants) {
            console.log(`\n=== ${tenant.code} (${tenant.database_name}) - ${tenant.status} ===`);

            try {
                const tenantPool = await mysql.createPool({
                    host: process.env.DB_HOST || 'localhost',
                    user: process.env.DB_USER || 'root',
                    password: process.env.DB_PASSWORD || '',
                    database: tenant.database_name,
                    waitForConnections: true,
                    connectionLimit: 2
                });

                // Check if purchase_items table exists
                const [tables] = await tenantPool.execute("SHOW TABLES LIKE 'purchase_items'");

                if (tables.length === 0) {
                    console.log('  Creating purchase_items table...');
                    await tenantPool.execute(`
                        CREATE TABLE purchase_items (
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
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                    `);
                    console.log('  ✓ Table created with current_stock column');
                } else {
                    // Check columns
                    const [cols] = await tenantPool.execute('DESCRIBE purchase_items');
                    const columnNames = cols.map(c => c.Field);
                    console.log('  Columns: ' + columnNames.join(', '));

                    // Check for current_stock
                    if (!columnNames.includes('current_stock')) {
                        console.log('  ADDING current_stock column...');
                        await tenantPool.execute('ALTER TABLE purchase_items ADD COLUMN current_stock DECIMAL(15,4) DEFAULT NULL');
                        console.log('  ✓ current_stock column ADDED');
                    } else {
                        console.log('  ✓ current_stock exists');
                    }

                    // Check for conversion_qty
                    if (!columnNames.includes('conversion_qty')) {
                        console.log('  ADDING conversion_qty column...');
                        await tenantPool.execute('ALTER TABLE purchase_items ADD COLUMN conversion_qty DECIMAL(15,4) DEFAULT 1');
                        console.log('  ✓ conversion_qty column ADDED');
                    } else {
                        console.log('  ✓ conversion_qty exists');
                    }
                }

                // Also ensure suppliers and purchases tables exist
                try {
                    await tenantPool.execute(`
                        CREATE TABLE IF NOT EXISTS suppliers (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            name VARCHAR(100) NOT NULL,
                            contact_person VARCHAR(100),
                            phone VARCHAR(20),
                            address TEXT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                    `);
                    console.log('  ✓ suppliers table OK');
                } catch (e) { }

                try {
                    await tenantPool.execute(`
                        CREATE TABLE IF NOT EXISTS purchases (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            branch_id INT,
                            supplier_id INT,
                            invoice_number VARCHAR(50),
                            date DATE,
                            due_date DATE,
                            total_amount DECIMAL(15,2) DEFAULT 0,
                            paid_amount DECIMAL(15,2) DEFAULT 0,
                            payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
                            notes TEXT,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                    `);
                    console.log('  ✓ purchases table OK');
                } catch (e) { }

                await tenantPool.end();

            } catch (tenantError) {
                console.error(`  ✗ Error: ${tenantError.message}`);
            }
        }

        console.log('\n=== All tenants checked and fixed! ===');
        console.log('\nNow restart the server and try import again.');

    } catch (error) {
        console.error('Error:', error.message);
    } finally {
        await mainPool.end();
    }
}

checkAndFix();
