require('dotenv').config({ path: '../.env' });
const mysql = require('mysql2/promise');

async function checkTenants() {
    const config = {
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'saga_main'
    };

    console.log('--- DB INFO CHECK ---');
    try {
        const conn = await mysql.createConnection(config);
        const [tenants] = await conn.execute('SELECT id, name, database_name FROM tenants');

        console.log('MAIN DB (Auth/Tenant Info):', config.database);
        console.log('---------------------');
        if (tenants.length === 0) {
            console.log('NO TENANTS FOUND. You might be using single-tenant logic directly in main DB?');
        } else {
            console.log('TENANTS FOUND:', tenants.length);
            tenants.forEach(t => {
                console.log(`- Tenant: ${t.name} (ID: ${t.id})`);
                console.log(`  Target Database (DATA PRODUK DISINI): ${t.database_name}`);
            });
        }
        console.log('---------------------');

        await conn.end();
    } catch (e) {
        console.error('Error:', e.message);
    }
}

checkTenants();
