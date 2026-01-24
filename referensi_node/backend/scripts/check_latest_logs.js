const mysql = require('mysql2/promise');

async function checkLogs() {
    const connection = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: '',
        database: 'saga_tenant_rj0001'
    });

    try {
        console.log('--- Latest 5 Price Logs ---');
        const [rows] = await connection.execute(`
            SELECT id, old_price, new_price, created_at, user_id 
            FROM price_logs 
            ORDER BY created_at DESC 
            LIMIT 5
        `);
        console.log(JSON.stringify(rows, null, 2));
    } catch (error) {
        console.error('Error:', error);
    } finally {
        await connection.end();
    }
}

checkLogs();
