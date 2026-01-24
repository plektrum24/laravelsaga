require('dotenv').config({ path: '../.env' });
const mysql = require('mysql2/promise');

async function checkSchema() {
    console.log('--- CHECKING SCHEMA ---');
    try {
        const conn = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: 'saga_tenant_bkt0001' // Hardcoded based on previous finding
        });

        const [columns] = await conn.execute('SHOW COLUMNS FROM products');
        console.log('Columns in products table:');
        const hasBarcode = columns.some(c => c.Field === 'barcode');

        columns.forEach(c => {
            console.log(`- ${c.Field} (${c.Type})`);
        });

        console.log('---------------------');
        if (hasBarcode) {
            console.log('✅ Column BARCODE exists!');
        } else {
            console.log('❌ Column BARCODE is MISSING!');
        }

        await conn.end();
    } catch (e) {
        console.error('Error:', e.message);
    }
}

checkSchema();
