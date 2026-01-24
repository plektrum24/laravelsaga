const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function fixProductUnitsColumns() {
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
                    // 1. Rename conversion_factor -> conversion_qty
                    const [cfCols] = await connection.query("SHOW COLUMNS FROM product_units LIKE 'conversion_factor'");
                    if (cfCols.length > 0) {
                        console.log("Renaming conversion_factor -> conversion_qty...");
                        await connection.query("ALTER TABLE product_units CHANGE conversion_factor conversion_qty DECIMAL(10,4) NOT NULL DEFAULT 1");
                    }

                    // 2. Rename is_default -> is_base_unit
                    const [defCols] = await connection.query("SHOW COLUMNS FROM product_units LIKE 'is_default'");
                    if (defCols.length > 0) {
                        console.log("Renaming is_default -> is_base_unit...");
                        await connection.query("ALTER TABLE product_units CHANGE is_default is_base_unit BOOLEAN DEFAULT FALSE");
                    }

                    // Verify
                    const [qtyCols] = await connection.query("SHOW COLUMNS FROM product_units LIKE 'conversion_qty'");
                    if (qtyCols.length > 0) console.log("✅ Verified conversion_qty.");

                    const [baseCols] = await connection.query("SHOW COLUMNS FROM product_units LIKE 'is_base_unit'");
                    if (baseCols.length > 0) console.log("✅ Verified is_base_unit.");

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

fixProductUnitsColumns();
