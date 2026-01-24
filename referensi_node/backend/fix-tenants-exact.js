const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixTenantExact() {
    const conn = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    try {
        const [dbs] = await conn.execute("SHOW DATABASES LIKE 'sagatoko_tenant_%'");
        const bestMatch = dbs[0] ? Object.values(dbs[0])[0] : null;

        if (!bestMatch) {
            console.error('No matching tenant DB found in system!');
            return;
        }

        console.log(`Found exact DB name: '${bestMatch}'`);

        await conn.execute("UPDATE tenants SET database_name = ? WHERE id = 1", [bestMatch]);
        console.log('Updated tenant 1 with exact database name.');

    } catch (e) {
        console.error(e);
    } finally {
        await conn.end();
    }
}

fixTenantExact();
