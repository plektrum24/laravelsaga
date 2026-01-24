require('dotenv').config();
const mysql = require('mysql2/promise');

async function checkSchema() {
    console.log('--- CHECKING SCHEMA FOR GOODS IN ---');
    try {
        const conn = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: 'saga_tenant_bkt0001'
        });

        // Check purchases table
        console.log('\nChecking purchase_items table...');
        const [columns] = await conn.execute('SHOW COLUMNS FROM purchase_items');
        let hasExpired = false;
        columns.forEach(c => {
            console.log(`- ${c.Field} (${c.Type})`);
            if (c.Field === 'expired_date') hasExpired = true;
        });

        console.log('---------------------');
        if (hasExpired) {
            console.log('✅ Column EXPIRED_DATE exists in purchase_items!');
        } else {
            console.log('❌ Column EXPIRED_DATE is MISSING from purchase_items!');
        }

        await conn.end();
    } catch (e) {
        console.error('Error:', e.message);
    }
}

checkSchema();
