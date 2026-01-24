/**
 * Fix Purchase Items Table Script
 * Run this script to add missing current_stock column to purchase_items table
 * for all tenant databases
 */

const mysql = require('mysql2/promise');
require('dotenv').config({ path: __dirname + '/../.env' });

async function fixAllTenantDatabases() {
    console.log('=== Fix Purchase Items Table for All Tenants ===\n');

    // Connect to main database
    const mainPool = await mysql.createPool({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'saga_main',
        waitForConnections: true,
        connectionLimit: 5
    });

    try {
        // Get all active tenants
        const [tenants] = await mainPool.execute('SELECT id, code, database_name FROM tenants WHERE status = "active"');
        console.log(`Found ${tenants.length} active tenants\n`);

        for (const tenant of tenants) {
            console.log(`Processing tenant: ${tenant.code} (${tenant.database_name})`);

            try {
                // Connect to tenant database
                const tenantPool = await mysql.createPool({
                    host: process.env.DB_HOST || 'localhost',
                    user: process.env.DB_USER || 'root',
                    password: process.env.DB_PASSWORD || '',
                    database: tenant.database_name,
                    waitForConnections: true,
                    connectionLimit: 2
                });

                // Create suppliers table if not exists
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
                } catch (e) {
                    console.log('  ✓ suppliers table exists');
                }

                // Create purchases table if not exists
                try {
                    await tenantPool.execute(`
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
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                    `);
                    console.log('  ✓ purchases table OK');
                } catch (e) {
                    console.log('  ✓ purchases table exists');
                }

                // Create purchase_items table if not exists  
                try {
                    await tenantPool.execute(`
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
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                    `);
                    console.log('  ✓ purchase_items table created with current_stock');
                } catch (e) {
                    // Table exists, try to add column
                    try {
                        await tenantPool.execute(`ALTER TABLE purchase_items ADD COLUMN current_stock DECIMAL(15,4) DEFAULT NULL`);
                        console.log('  ✓ current_stock column ADDED');
                    } catch (e2) {
                        console.log('  ✓ current_stock column exists');
                    }
                }

                // Try to add conversion_qty column too
                try {
                    await tenantPool.execute(`ALTER TABLE purchase_items ADD COLUMN conversion_qty DECIMAL(15,4) DEFAULT 1`);
                    console.log('  ✓ conversion_qty column ADDED');
                } catch (e) {
                    console.log('  ✓ conversion_qty column exists');
                }

                await tenantPool.end();
                console.log(`  ✓ Tenant ${tenant.code} FIXED!\n`);

            } catch (tenantError) {
                console.error(`  ✗ Error for tenant ${tenant.code}: ${tenantError.message}\n`);
            }
        }

        console.log('\n=== All tenants processed! ===');
        console.log('Please restart the backend server and try import again.');

    } catch (error) {
        console.error('Error:', error.message);
    } finally {
        await mainPool.end();
    }
}

fixAllTenantDatabases();
