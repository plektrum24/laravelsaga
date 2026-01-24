const mysql = require('mysql2/promise');
require('dotenv').config();

async function getBranches() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: 'saga_tenant_jkt001'
        });

        const [branches] = await connection.execute("SELECT id, name FROM branches");
        const fs = require('fs');
        fs.writeFileSync('branches.json', JSON.stringify(branches, null, 2));
        console.log('Saved branches.json');

        await connection.end();
    } catch (e) {
        console.error('Connection Failed:', e.message);
    }
}

getBranches();
