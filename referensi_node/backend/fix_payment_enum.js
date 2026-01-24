const mysql = require('mysql2/promise');

async function fixPaymentEnum() {
    const conn = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: '',
        database: 'saga_tenant_bkt0001'
    });

    console.log('Fixing payment_method ENUM...');
    await conn.execute(`
        ALTER TABLE transactions 
        MODIFY COLUMN payment_method ENUM('cash','debit','credit','qris','debt') NOT NULL DEFAULT 'cash'
    `);
    console.log('Success!');
    await conn.end();
}

fixPaymentEnum().catch(e => console.error('Error:', e.message));
