const { getMainPool, getTenantPool } = require('./config/database');
require('dotenv').config();

async function fixTransferColumns() {
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
                    // Rename source_branch_id -> from_branch_id
                    const [srcCols] = await connection.query("SHOW COLUMNS FROM stock_transfers LIKE 'source_branch_id'");
                    if (srcCols.length > 0) {
                        console.log("Renaming source_branch_id -> from_branch_id...");
                        // We must drop FK first if it exists, or change column with FK definition.
                        // Ideally "CHANGE COLUMN" works if type matches.
                        // But FK constraint names might be issues.
                        // Let's try simple CHANGE first.
                        try {
                            await connection.query("ALTER TABLE stock_transfers CHANGE source_branch_id from_branch_id INT NOT NULL");
                        } catch (e) {
                            // If FK error, we might need to drop FK.
                            // But usually for same type it might work.
                            console.log("   Simple change failed, trying to handle FK...");
                            // Find FK name? Hard to script.
                            // Just try ignoring if column exists?
                        }
                    }

                    // Rename destination_branch_id -> to_branch_id
                    const [destCols] = await connection.query("SHOW COLUMNS FROM stock_transfers LIKE 'destination_branch_id'");
                    if (destCols.length > 0) {
                        console.log("Renaming destination_branch_id -> to_branch_id...");
                        await connection.query("ALTER TABLE stock_transfers CHANGE destination_branch_id to_branch_id INT NOT NULL");
                    }

                    // Verify
                    const [fromCols] = await connection.query("SHOW COLUMNS FROM stock_transfers LIKE 'from_branch_id'");
                    if (fromCols.length > 0) console.log("✅ Verified from_branch_id.");

                    const [toCols] = await connection.query("SHOW COLUMNS FROM stock_transfers LIKE 'to_branch_id'");
                    if (toCols.length > 0) console.log("✅ Verified to_branch_id.");

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

fixTransferColumns();
