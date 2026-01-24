const { getTenantPool } = require('./config/database');
require('dotenv').config();

const DB_NAME = 'saga_tenant_bkt0001';

async function checkSchema() {
    try {
        console.log(`Checking ${DB_NAME} - stock_transfers...`);
        const pool = await getTenantPool(DB_NAME);
        const [cols] = await pool.query("SHOW COLUMNS FROM stock_transfers LIKE 'notes'");

        console.log("Columns found:", cols);

        if (cols.length > 0) {
            console.log("✅ Column 'notes' EXISTS in stock_transfers.");
        } else {
            console.log("❌ Column 'notes' MISSING in stock_transfers.");
            // Add it
            console.log("Adding notes column to stock_transfers...");
            await pool.query("ALTER TABLE stock_transfers ADD COLUMN notes TEXT");
            console.log("✅ Added.");
        }
    } catch (e) {
        console.error("Error:", e);
    }
    process.exit(0);
}

checkSchema();
