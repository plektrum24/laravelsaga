const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixTenantJkt() {
    const conn = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        console.log("Updating Tenant 1 to 'saga_tenant_jkt001'...");
        await conn.execute("UPDATE tenants SET database_name = 'saga_tenant_jkt001' WHERE id = 1");
        console.log('Update Complete.');
    } catch (e) {
        console.error(e);
    } finally {
        await conn.end();
    }
}

fixTenantJkt();
