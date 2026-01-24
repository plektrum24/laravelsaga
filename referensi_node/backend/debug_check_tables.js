const fs = require('fs');
const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkTables() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME
        });

        const [dbs] = await connection.execute("SHOW DATABASES");
        fs.writeFileSync('dbs.json', JSON.stringify(dbs, null, 2));
        console.log('Saved to dbs.json');

        await connection.end();
    } catch (e) {
        console.error('Connection Failed:', e.message);
    }
}

checkTables();
