require('dotenv').config();
const mysql = require('mysql2/promise');

async function checkSchema() {
    console.log('🔍 STARTING DEEP SCHEMA CHECK...\n');
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
        const activeTenant = tenants.find(t => t.database_name || t.db_name); // handle both potential column names just in case

        if (!activeTenant) {
            console.error('❌ No active tenant found to check.');
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

        // 3. Check Products Table
        console.log('\n--- Checking PRODUCTS Table ---');
        const [prodCols] = await tenantDb.query(`SHOW COLUMNS FROM products`);
        const prodFields = prodCols.map(c => c.Field);
        console.log('Existing Columns:', prodFields.join(', '));

        const expectedProd = ['weight', 'barcode', 'image_url', 'min_stock', 'is_active'];
        expectedProd.forEach(col => {
            if (!prodFields.includes(col)) console.error(`   ❌ MISSING COLUMN: ${col}`);
            else console.log(`   ✅ ${col} exists`);
        });

        // 4. Check Transactions Table
        console.log('\n--- Checking TRANSACTIONS Table ---');
        const [trxCols] = await tenantDb.query(`SHOW COLUMNS FROM transactions`);
        const trxFields = trxCols.map(c => c.Field);

        const expectedTrx = ['cashier_name', 'shift_id', 'payment_method']; // 'cashier_name' was added via migration
        expectedTrx.forEach(col => {
            if (!trxFields.includes(col)) console.error(`   ❌ MISSING COLUMN: ${col}`);
            else console.log(`   ✅ ${col} exists`);
        });

        // 5. Check Transaction Items Table
        console.log('\n--- Checking TRANSACTION_ITEMS Table ---');
        const [itemCols] = await tenantDb.query(`SHOW COLUMNS FROM transaction_items`);
        const itemFields = itemCols.map(c => c.Field);

        const expectedItems = ['unit_name', 'buy_price', 'conversion_qty']; // 'unit_name' & 'buy_price' added via migration
        expectedItems.forEach(col => {
            if (!itemFields.includes(col)) console.error(`   ❌ MISSING COLUMN: ${col}`);
            else console.log(`   ✅ ${col} exists`);
        });

        console.log('\n✅ SCHEMA CHECK COMPLETED.');

    } catch (error) {
        console.error('\n❌ FATAL ERROR:', error.message);
    } finally {
        if (mainDb) await mainDb.end();
        if (tenantDb) await tenantDb.end();
    }
}

checkSchema();
