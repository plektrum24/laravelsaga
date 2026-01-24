const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function fixSkuSequencesSchema() {
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
                    // Create sku_sequences table
                    await connection.query(`
                        CREATE TABLE IF NOT EXISTS sku_sequences (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            category_id INT NOT NULL,
                            year INT NOT NULL,
                            last_number INT DEFAULT 0,
                            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
                            UNIQUE KEY unique_sequence (category_id, year)
                        )
                    `);
                    console.log("✅ Verified sku_sequences table.");

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

fixSkuSequencesSchema();
