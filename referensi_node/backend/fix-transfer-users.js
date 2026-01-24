const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixTransferUsers() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: 'saga_tenant_jkt001'
    });

    console.log('Connected to database...');

    try {
        console.log('Checking stock_transfers columns...');
        const [columns] = await connection.execute('SHOW COLUMNS FROM stock_transfers');
        const columnNames = columns.map(c => c.Field);

        const colsToAdd = [
            { name: 'approved_by', type: 'INT NULL' },
            { name: 'approved_at', type: 'DATETIME NULL' },
            { name: 'received_by', type: 'INT NULL' },
            { name: 'received_at', type: 'DATETIME NULL' }
        ];

        for (const col of colsToAdd) {
            if (!columnNames.includes(col.name)) {
                console.log(`Adding ${col.name}...`);
                await connection.execute(`ALTER TABLE stock_transfers ADD COLUMN ${col.name} ${col.type}`);
            }
        }

        console.log('Schema Fixed.');
    } catch (error) {
        console.error('Schema fix error:', error);
    } finally {
        await connection.end();
    }
}

fixTransferUsers();
