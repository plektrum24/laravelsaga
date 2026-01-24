const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function fixUnitsSchema() {
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
                    // Create units table
                    await connection.query(`
                        CREATE TABLE IF NOT EXISTS units (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            name VARCHAR(50) NOT NULL,
                            short_name VARCHAR(20) NOT NULL,
                            type ENUM('weight', 'volume', 'quantity') DEFAULT 'quantity',
                            is_active BOOLEAN DEFAULT TRUE,
                            sort_order INT DEFAULT 0,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )
                    `);
                    console.log("✅ Verified units table.");

                    // Check products.base_unit_id column
                    const [baseUnitCols] = await connection.query("SHOW COLUMNS FROM products LIKE 'base_unit_id'");
                    if (baseUnitCols.length === 0) {
                        console.log("Adding base_unit_id to products...");
                        await connection.query("ALTER TABLE products ADD COLUMN base_unit_id INT");
                        await connection.query("ALTER TABLE products ADD CONSTRAINT fk_products_base_unit FOREIGN KEY (base_unit_id) REFERENCES units(id) ON DELETE SET NULL");
                        console.log("✅ Added base_unit_id.");
                    }

                    // Create product_units table
                    await connection.query(`
                        CREATE TABLE IF NOT EXISTS product_units (
                            id INT PRIMARY KEY AUTO_INCREMENT,
                            product_id INT NOT NULL,
                            unit_id INT NOT NULL,
                            conversion_factor DECIMAL(10,4) NOT NULL DEFAULT 1,
                            buy_price DECIMAL(15,2) DEFAULT 0,
                            sell_price DECIMAL(15,2) DEFAULT 0,
                            is_default BOOLEAN DEFAULT FALSE,
                            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                            FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
                            UNIQUE KEY unique_product_unit (product_id, unit_id)
                        )
                    `);
                    console.log("✅ Verified product_units table.");

                    // Verify stock_transfer_items columns
                    const [tiCols] = await connection.query("SHOW COLUMNS FROM stock_transfer_items LIKE 'qty_requested'");
                    if (tiCols.length === 0) {
                        console.log("Updating stock_transfer_items schema...");
                        // Rename quantity -> qty_requested if exists, else add
                        const [oldQtyCols] = await connection.query("SHOW COLUMNS FROM stock_transfer_items LIKE 'quantity'");
                        if (oldQtyCols.length > 0) {
                            await connection.query("ALTER TABLE stock_transfer_items CHANGE quantity qty_requested INT NOT NULL");
                        } else {
                            await connection.query("ALTER TABLE stock_transfer_items ADD COLUMN qty_requested INT NOT NULL");
                        }

                        await connection.query("ALTER TABLE stock_transfer_items ADD COLUMN qty_approved INT DEFAULT 0");
                        await connection.query("ALTER TABLE stock_transfer_items ADD COLUMN qty_received INT DEFAULT 0");
                        await connection.query("ALTER TABLE stock_transfer_items ADD COLUMN unit_id INT");
                        await connection.query("ALTER TABLE stock_transfer_items ADD COLUMN notes TEXT");

                        // Add FK for unit_id
                        await connection.query("ALTER TABLE stock_transfer_items ADD CONSTRAINT fk_transfer_items_unit FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE SET NULL");

                        console.log("✅ Updated stock_transfer_items schema.");
                    }

                    // Insert default units
                    await connection.query(`
                        INSERT INTO units (name, short_name, type) VALUES 
                        ('Pcs', 'pcs', 'quantity'),
                        ('Kilogram', 'kg', 'weight'),
                        ('Liter', 'l', 'volume'),
                        ('Box', 'box', 'quantity')
                        ON DUPLICATE KEY UPDATE name = name
                    `);
                    console.log("✅ Inserted default units.");

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

fixUnitsSchema();
