require('dotenv').config();
const { getMainPool } = require('./config/database');

async function fixUserSchema() {
    const pool = await getMainPool();
    const conn = await pool.getConnection();
    try {
        console.log('Checking users table schema...');

        // Check for branch_id
        const [cols] = await conn.execute("SHOW COLUMNS FROM users LIKE 'branch_id'");
        if (cols.length === 0) {
            console.log('Adding branch_id column...');
            await conn.execute("ALTER TABLE users ADD COLUMN branch_id INT DEFAULT NULL AFTER role");
        } else {
            console.log('branch_id column exists.');
        }

        // Check for tenant_id (just in case)
        const [tenantCols] = await conn.execute("SHOW COLUMNS FROM users LIKE 'tenant_id'");
        if (tenantCols.length === 0) {
            console.log('Adding tenant_id column...');
            await conn.execute("ALTER TABLE users ADD COLUMN tenant_id INT DEFAULT NULL AFTER role");
        } else {
            console.log('tenant_id column exists.');
        }

        console.log('Schema update complete.');
    } catch (err) {
        console.error('Migration failed:', err);
    } finally {
        conn.release();
        process.exit(0);
    }
}

fixUserSchema();
