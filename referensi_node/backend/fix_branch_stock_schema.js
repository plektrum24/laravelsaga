const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function fixBranchStockSchema() {
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
                    // Check if quantity column exists
                    const [quantCols] = await connection.query("SHOW COLUMNS FROM branch_stock LIKE 'quantity'");
                    if (quantCols.length > 0) {
                        console.log("Renaming quantity to stock...");
                        await connection.query("ALTER TABLE branch_stock CHANGE quantity stock INT DEFAULT 0");
                        console.log("✅ Renamed quantity.");
                    } else {
                        console.log("Column quantity not found (maybe already renamed).");
                        // Ensure stock exists
                        const [stockCols] = await connection.query("SHOW COLUMNS FROM branch_stock LIKE 'stock'");
                        if (stockCols.length === 0) {
                            console.log("Adding stock column...");
                            await connection.query("ALTER TABLE branch_stock ADD COLUMN stock INT DEFAULT 0");
                            console.log("✅ Added stock.");
                        }
                    }

                    // Check min_stock
                    const [minStockCols] = await connection.query("SHOW COLUMNS FROM branch_stock LIKE 'min_stock'");
                    if (minStockCols.length === 0) {
                        console.log("Adding min_stock column...");
                        await connection.query("ALTER TABLE branch_stock ADD COLUMN min_stock INT DEFAULT 5");
                        console.log("✅ Added min_stock.");
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

fixBranchStockSchema();
