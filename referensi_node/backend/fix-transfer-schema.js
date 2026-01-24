const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixSchema() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: 'saga_tenant_jkt001'
    });

    console.log('Connected to database...');

    try {
        console.log('Checking stock_transfer_items columns...');
        const [columns] = await connection.execute('SHOW COLUMNS FROM stock_transfer_items');
        const columnNames = columns.map(c => c.Field);

        if (!columnNames.includes('unit_id')) {
            console.log('Adding unit_id column...');
            await connection.execute('ALTER TABLE stock_transfer_items ADD COLUMN unit_id INT NULL');
        }

        if (!columnNames.includes('notes')) {
            console.log('Adding notes column...');
            await connection.execute('ALTER TABLE stock_transfer_items ADD COLUMN notes TEXT NULL');
        }

        console.log('Schema Fixed.');
    } catch (error) {
        console.error('Schema fix error:', error);
    } finally {
        await connection.end();
    }
}

fixSchema();
