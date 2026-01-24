const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function fixBranchSchema() {
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
                    // 1. Add missing columns FIRST
                    const [mainCols] = await connection.query("SHOW COLUMNS FROM branches LIKE 'is_main'");
                    if (mainCols.length === 0) {
                        console.log("Adding is_main column...");
                        await connection.query("ALTER TABLE branches ADD COLUMN is_main BOOLEAN DEFAULT FALSE");
                        console.log("✅ Added is_main.");
                    }

                    const [codeCols] = await connection.query("SHOW COLUMNS FROM branches LIKE 'code'");
                    if (codeCols.length === 0) {
                        console.log("Adding code column...");
                        await connection.query("ALTER TABLE branches ADD COLUMN code VARCHAR(50)");
                        console.log("✅ Added code.");
                    }

                    // 2. Update Data
                    // Set Pusat as main and give it a code
                    await connection.query("UPDATE branches SET is_main = TRUE, code = 'CAB-001' WHERE name = 'Pusat'");
                    console.log("✅ Updated Pusat branch data.");

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

fixBranchSchema();
