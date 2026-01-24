const { getTenantPool } = require('./config/database');
require('dotenv').config();

const DB_NAME = 'saga_tenant_bkt0001';

async function fixCol() {
    try {
        console.log(`Fixing ${DB_NAME} stock_transfers...`);
        const pool = await getTenantPool(DB_NAME);

        try {
            await pool.query("ALTER TABLE stock_transfers ADD COLUMN transfer_number VARCHAR(50) UNIQUE AFTER id");
            console.log("✅ Added transfer_number.");
        } catch (e) {
            console.log("⚠️ Error/Exist:", e.message);
        }

    } catch (e) {
        console.error("Error:", e);
    }
    process.exit(0);
}

fixCol();
