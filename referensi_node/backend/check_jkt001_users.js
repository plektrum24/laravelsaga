const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkUsers() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: 'saga_tenant_jkt001'
        });

        const [users] = await connection.execute("SELECT id, name, email, role, branch_id FROM users");
        console.log('Users:', JSON.stringify(users, null, 2));

        await connection.end();
    } catch (e) {
        console.error('Connection Failed:', e.message);
    }
}

checkUsers();
