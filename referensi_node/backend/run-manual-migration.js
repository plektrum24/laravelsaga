require('dotenv').config();
const mysql = require('mysql2/promise');

async function runManualMigration() {
    console.log('🛠️ STARTING MANUAL MIGRATION...\n');
    let mainDb = null;
    let tenantDb = null;

    try {
        // 1. Get Active Tenant
        mainDb = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME,
            port: process.env.DB_PORT || 3306
        });

        const [tenants] = await mainDb.query('SELECT * FROM tenants');
        const activeTenant = tenants.find(t => t.database_name || t.db_name);

        if (!activeTenant) {
            console.error('❌ No active tenant found.');
            return;
        }

        const dbName = activeTenant.database_name || activeTenant.db_name;
        console.log(`Target Tenant DB: ${dbName}`);

        // 2. Connect to Tenant DB
        tenantDb = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: dbName,
            port: process.env.DB_PORT || 3306
        });

        // 3. Execute Migrations
        console.log('\n--- Executing Updates ---');

        const migrations = [
            "ALTER TABLE transaction_items ADD COLUMN buy_price DECIMAL(15,2) DEFAULT 0",
            "ALTER TABLE transaction_items ADD COLUMN conversion_qty INT DEFAULT 1",
            "ALTER TABLE products ADD COLUMN weight DECIMAL(10,2) DEFAULT 0",
            "ALTER TABLE products ADD COLUMN barcode VARCHAR(255) NULL"
        ];

        for (const sql of migrations) {
            try {
                await tenantDb.execute(sql);
                console.log(`   ✅ Executed: ${sql}`);
            } catch (e) {
                if (e.code === 'ER_DUP_FIELDNAME') {
                    console.log(`   ⚠️ Skipped (Already exists): ${sql}`);
                } else {
                    console.error(`   ❌ Failed: ${e.message}`);
                }
            }
        }

        console.log('\n✅ MIGRATION COMPLETED.');

    } catch (error) {
        console.error('\n❌ FATAL ERROR:', error.message);
    } finally {
        if (mainDb) await mainDb.end();
        if (tenantDb) await tenantDb.end();
    }
}

runManualMigration();
