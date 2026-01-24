require('dotenv').config();
const { getMainPool } = require('./config/database');

async function debugSchema() {
    try {
        const pool = await getMainPool();
        const [columns] = await pool.execute('SHOW COLUMNS FROM users');
        console.log('Users Table Columns:');
        columns.forEach(col => console.log(`- ${col.Field} (${col.Type})`));
        process.exit(0);
    } catch (err) {
        console.error('Error:', err);
        process.exit(1);
    }
}

debugSchema();
