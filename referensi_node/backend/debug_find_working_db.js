const mysql = require('mysql2/promise');
require('dotenv').config();

async function findWorkingDb() {
    console.log('Scanning Databases...');

    const mainConn = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    const [dbs] = await mainConn.execute("SHOW DATABASES LIKE 'sagatoko%'");
    await mainConn.end();

    const dbNames = dbs.map(r => Object.values(r)[0]);

    for (const name of dbNames) {
        if (name === process.env.DB_NAME) continue;

        try {
            const conn = await mysql.createConnection({
                host: process.env.DB_HOST,
                user: process.env.DB_USER,
                password: process.env.DB_PASSWORD,
                database: name
            });
            console.log(`[SUCCESS] Connected to '${name}'`);

            // Check for stock_transfers table
            const [params] = await conn.execute("SHOW TABLES LIKE 'stock_transfers'");
            if (params.length > 0) console.log(`   -> Has stock_transfers`);

            await conn.end();
            return; // Stop on first success just to be quick? No, check all.
        } catch (e) {
            console.log(`[FAILED]  Could not connect to '${name}': ${e.message}`); // Quotes to see whitespace
        }
    }
}

findWorkingDb();
