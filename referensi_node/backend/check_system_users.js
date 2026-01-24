const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkSystemUsers() {
    try {
        const connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME
        });

        const [users] = await connection.execute("SELECT email, role, branch_id FROM users WHERE email IN ('man3@test.com', 'cash4@test.com')");
        console.log('Users:', JSON.stringify(users, null, 2));

        await connection.end();
    } catch (e) {
        console.error('Connection Failed:', e.message);
    }
}

checkSystemUsers();
