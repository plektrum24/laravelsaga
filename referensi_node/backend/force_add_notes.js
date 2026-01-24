const { getTenantPool } = require('./config/database');
require('dotenv').config();

async function forceAdd() {
    try {
        const { getMainPool, getTenantPool } = require('./config/database');
        const mainPool = await getMainPool();
        const [tenants] = await mainPool.execute('SELECT * FROM tenants');

        for (const tenant of tenants) {
            console.log(`Processing: ${tenant.name} (${tenant.database_name})`);
            try {
                const pool = await getTenantPool(tenant.database_name);
                try {
                    await pool.query("ALTER TABLE stock_transfer_items ADD COLUMN notes TEXT");
                    console.log("   ✅ Added.");
                } catch (e) {
                    console.log("   ⚠️ Error/Exist:", e.message);
                }
            } catch (e) {
                console.log("   ❌ Connect Failed:", e.message);
            }
        }
    } catch (e) {
        console.error("Error:", e);
    }
    process.exit(0);
}

forceAdd();
