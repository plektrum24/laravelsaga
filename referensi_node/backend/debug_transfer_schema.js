require('dotenv').config();
const { getMainPool } = require('./config/database');

async function debugSchema() {
    try {
        const pool = await getMainPool();
        const [rows] = await pool.execute("SHOW COLUMNS FROM sagatoko_tenant_sagatoko.stock_transfers LIKE 'status'");
        console.log('Status Column:', rows);
        process.exit(0);
        console.log(rows);
        process.exit(0);
    } catch (err) {
        console.error(err);
        process.exit(1);
    }
}

debugSchema();
