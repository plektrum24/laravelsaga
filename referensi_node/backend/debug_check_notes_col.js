const { getTenantPool } = require('./config/database');
require('dotenv').config();

const DB_NAME = 'saga_tenant_bkt0001';

async function checkNotes() {
    try {
        console.log(`Checking ${DB_NAME}...`);
        const pool = await getTenantPool(DB_NAME);
        const [cols] = await pool.query("SHOW COLUMNS FROM stock_transfer_items LIKE 'notes'");

        console.log("Columns found:", cols);

        if (cols.length > 0) {
            console.log("✅ Column 'notes' EXISTS.");
        } else {
            console.log("❌ Column 'notes' MISSING.");
            // Try adding it
            console.log("Attempting to add...");
            await pool.query("ALTER TABLE stock_transfer_items ADD COLUMN notes TEXT");
            console.log("Added.");
        }
    } catch (e) {
        console.error("Error:", e);
    }
    process.exit(0);
}

checkNotes();
