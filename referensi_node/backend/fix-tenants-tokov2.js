const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixTenantTokov2() {
    const conn = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        console.log("Updating Tenant 1 to 'sagatoko_tokov2'...");
        await conn.execute("UPDATE tenants SET database_name = 'sagatoko_tokov2' WHERE id = 1");
        console.log('Update Complete.');
    } catch (e) {
        console.error(e);
    } finally {
        await conn.end();
    }
}

fixTenantTokov2();
