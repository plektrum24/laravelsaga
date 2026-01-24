const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkJkt() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: 'saga_tenant_jkt001'
        });

        const [tables] = await connection.execute("SHOW TABLES");
        console.log('Tables in jkt001:', tables.map(t => Object.values(t)[0]));

        await connection.end();
    } catch (e) {
        console.error('Connection Failed:', e.message);
    }
}

checkJkt();
