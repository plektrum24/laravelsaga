const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function addNotesColumn() {
    try {
        const mainPool = await getMainPool();
        const [tenants] = await mainPool.execute('SELECT * FROM tenants WHERE status = "active"');

        console.log(`Found ${tenants.length} active tenants.`);

        for (const tenant of tenants) {
            console.log(`Processing tenant: ${tenant.name} (${tenant.database_name})...`);
            try {
                const tenantPool = await getTenantPool(tenant.database_name);
                const connection = await tenantPool.getConnection();

                try {
                    const [cols] = await connection.query("SHOW COLUMNS FROM stock_transfer_items LIKE 'notes'");
                    if (cols.length === 0) {
                        console.log("Adding notes column to stock_transfer_items...");
                        await connection.query("ALTER TABLE stock_transfer_items ADD COLUMN notes TEXT");
                        console.log("✅ Added notes column.");
                    } else {
                        console.log("✅ notes column already exists.");
                    }
                } finally {
                    connection.release();
                }
            } catch (err) {
                console.error(`❌ Failed for ${tenant.database_name}:`, err.message);
            }
        }
    } catch (error) {
        console.error("Critical error:", error);
    } finally {
        process.exit(0);
    }
}

addNotesColumn();
