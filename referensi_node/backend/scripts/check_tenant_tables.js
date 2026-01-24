const mysql = require('mysql2/promise');

async function checkTables() {
    const connection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: ''
    });

    try {
        console.log('--- Checking Tenant Tables ---');
        // Pick one tenant db
        const [dbs] = await connection.execute("SHOW DATABASES LIKE 'saga_tenant_bkt0001'");
        if (dbs.length === 0) {
            console.log('Tenant db not found, picking first saga_tenant_...');
            const [allDbs] = await connection.execute("SHOW DATABASES LIKE 'saga_tenant_%'");
            if (allDbs.length) await connection.changeUser({ database: Object.values(allDbs[0])[0] });
        } else {
            await connection.changeUser({ database: 'saga_tenant_bkt0001' });
        }

        const [tables] = await connection.execute("SHOW TABLES LIKE 'users'");
        if (tables.length > 0) {
            console.log('Users table EXISTS in tenant db.');
            const [cols] = await connection.execute("DESCRIBE users");
            console.log('Columns:', cols.map(c => c.Field).join(', '));
        } else {
            console.log('Users table DOES NOT EXIST in tenant db.');
        }

    } catch (error) {
        console.error('Error:', error);
    } finally {
        await connection.end();
    }
}

checkTables();
