const mysql = require('mysql2/promise');
require('dotenv').config();

async function inspectAndFix() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: 'saga_tenant_jkt001'
    });

    console.log('Connected to database...');

    try {
        console.log('--- Columns in stock_transfer_items ---');
        const [columns] = await connection.execute('SHOW COLUMNS FROM stock_transfer_items');
        const columnNames = columns.map(c => c.Field);
        console.log(columnNames.join(', '));

        const missing = [];
        if (!columnNames.includes('qty_approved')) missing.push('qty_approved');
        if (!columnNames.includes('qty_requested')) missing.push('qty_requested');

        if (missing.length > 0) {
            console.log(`Missing columns: ${missing.join(', ')}`);
            for (const col of missing) {
                console.log(`Adding ${col}...`);
                // Assume DECIMAL(15,4)
                await connection.execute(`ALTER TABLE stock_transfer_items ADD COLUMN ${col} DECIMAL(15,4) DEFAULT 0`);
            }
            console.log('Fixed.');
        } else {
            console.log('All required columns present.');
        }

    } catch (error) {
        console.error('Error:', error);
    } finally {
        await connection.end();
    }
}

inspectAndFix();
