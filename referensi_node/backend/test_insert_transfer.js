const { getTenantPool } = require('./config/database');
require('dotenv').config();

const DB_NAME = 'saga_tenant_bkt0001';

async function testInsert() {
    try {
        console.log(`Inserting into ${DB_NAME} stock_transfers...`);
        const pool = await getTenantPool(DB_NAME);

        // Try deleting a dummy first
        try { await pool.query("DELETE FROM stock_transfers WHERE transfer_number = 'TEST-001'"); } catch (e) { }

        const sql = `INSERT INTO stock_transfers (transfer_number, from_branch_id, to_branch_id, notes, status) VALUES ('TEST-001', 1, 2, 'Test Note', 'pending')`;
        console.log("SQL:", sql);

        await pool.query(sql);
        console.log("✅ Insert SUCCEEDED with notes.");

    } catch (e) {
        console.error("❌ Insert FAILED:", e.message);
    }
    process.exit(0);
}

testInsert();
