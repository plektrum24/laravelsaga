const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixTransferEnum() {
    console.log('Connecting to sagatoko_tenant_sagatoko...');

    let connection;
    try {
        console.log(`Targeting Tenant DB: saga_tenant_jkt001`);
        connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: 'saga_tenant_jkt001'
        });

        console.log('Connected.');

        // 1. Fix ENUM
        console.log('Updating status column definition...');
        // We use MODIFY COLUMN to redefine the ENUM
        await connection.execute(`
            ALTER TABLE stock_transfers 
            MODIFY COLUMN status ENUM('pending', 'shipped', 'received', 'cancelled') NOT NULL DEFAULT 'pending'
        `);
        console.log('Status column updated.');

        // 2. Fix Broken Data (Empty Status)
        console.log('Fixing broken transfer records...');
        const [result] = await connection.execute(`
            UPDATE stock_transfers 
            SET status = 'shipped' 
            WHERE status = '' AND approved_at IS NOT NULL
        `);
        console.log(`Updated ${result.affectedRows} broken records.`);

        // 3. Verify
        const [rows] = await connection.execute("SHOW COLUMNS FROM stock_transfers LIKE 'status'");
        console.log('New Column Def:', rows[0].Type);

        await connection.end();
    } catch (err) {
        console.error('Fix failed:', err);
    }
}

fixTransferEnum();
