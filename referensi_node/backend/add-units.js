require('dotenv').config();
const mysql = require('mysql2/promise');

async function addUnits() {
    console.log('🛠️ ADDING REQUESTED UNITS...\n');
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

        // 3. Add Units
        const unitsToAdd = ['Lembar', 'Box', 'Gram', 'Kotak', 'Kalender'];

        for (const name of unitsToAdd) {
            const [existing] = await tenantDb.execute('SELECT id FROM units WHERE name = ?', [name]);
            if (existing.length === 0) {
                await tenantDb.execute('INSERT INTO units (name, sort_order) VALUES (?, 99)', [name]);
                console.log(`   ✅ Added: ${name}`);
            } else {
                console.log(`   ⚠️ Skipped (Exists): ${name}`);
            }
        }

        console.log('\n✅ UNITS ADDED SUCCESSFULLY.');

    } catch (error) {
        console.error('\n❌ FATAL ERROR:', error.message);
    } finally {
        if (mainDb) await mainDb.end();
        if (tenantDb) await tenantDb.end();
    }
}

addUnits();
