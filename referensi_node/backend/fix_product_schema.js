const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function fixProductSchema() {
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
                    // 1. categories.prefix
                    const [catCols] = await connection.query("SHOW COLUMNS FROM categories LIKE 'prefix'");
                    if (catCols.length === 0) {
                        console.log("Adding prefix column to categories...");
                        await connection.query("ALTER TABLE categories ADD COLUMN prefix VARCHAR(10)");

                        // Update existing categories with default prefix
                        const [cats] = await connection.query("SELECT id, name FROM categories");
                        for (const cat of cats) {
                            let prefix = cat.name.toUpperCase().substring(0, 3).replace(/[^A-Z]/g, 'X');
                            if (prefix.length < 3) prefix = (prefix + 'XXX').substring(0, 3);
                            await connection.query("UPDATE categories SET prefix = ? WHERE id = ?", [prefix, cat.id]);
                        }
                        console.log("✅ Added prefix.");
                    }

                    // 2. product_units.sort_order
                    const [puCols] = await connection.query("SHOW COLUMNS FROM product_units LIKE 'sort_order'");
                    if (puCols.length === 0) {
                        console.log("Adding sort_order column to product_units...");
                        await connection.query("ALTER TABLE product_units ADD COLUMN sort_order INT DEFAULT 0");
                        console.log("✅ Added sort_order.");
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

fixProductSchema();
