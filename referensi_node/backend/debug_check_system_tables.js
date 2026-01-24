const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkSystemTables() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME // sagatoko_system
        });

        const [tables] = await connection.execute("SHOW TABLES");
        const tableNames = tables.map(t => Object.values(t)[0]);
        console.log('Tables in sagatoko_system:');
        console.log(JSON.stringify(tableNames, null, 2));

        await connection.end();
    } catch (e) {
        console.error('Connection Failed:', e.message);
    }
}

checkSystemTables();
