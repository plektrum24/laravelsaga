const mysql = require('mysql2/promise');
require('dotenv').config();

async function printDbs() {
    const conn = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    });

    const [dbs] = await conn.execute("SHOW DATABASES");
    const names = dbs.map(r => Object.values(r)[0]);
    console.log('DATABASES:', JSON.stringify(names, null, 2));
    await conn.end();
}

printDbs();
