const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkUser() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME // sagatoko_system
    });

    const [users] = await connection.execute("SELECT id, email, tenant_id FROM users WHERE email = 'man3@test.com'");
    console.log('User man3:', users[0]);

    // Also check tenant 1 again
    const [tenants] = await connection.execute("SELECT id, database_name FROM tenants WHERE id = ?", [users[0].tenant_id]);
    console.log('Tenant Config:', tenants[0]);

    await connection.end();
}

checkUser();
