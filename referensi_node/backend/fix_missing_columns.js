const mysql = require('mysql2/promise');

async function fixMissingColumns() {
    console.log('Finding tenant databases...');

    const mainConn = await mysql.createConnection({
        host: 'localhost',
        user: 'root',
        password: ''
    });
    const [dbs] = await mainConn.query("SHOW DATABASES LIKE 'saga_tenant_%'");
    await mainConn.end();

    for (const row of dbs) {
        const dbName = Object.values(row)[0];
        console.log('\nFixing:', dbName);

        const conn = await mysql.createConnection({
            host: 'localhost',
            user: 'root',
            password: '',
            database: dbName
        });

        try {
            // Check if transactions table exists
            const [tables] = await conn.query("SHOW TABLES LIKE 'transactions'");
            if (tables.length === 0) {
                console.log('  No transactions table, skipping...');
                await conn.end();
                continue;
            }

            // Get existing columns
            const [cols] = await conn.query("SHOW COLUMNS FROM transactions");
            const colNames = cols.map(c => c.Field);

            // Add missing columns
            if (!colNames.includes('customer_id')) {
                console.log('  Adding customer_id column...');
                await conn.query('ALTER TABLE transactions ADD COLUMN customer_id INT(11) DEFAULT NULL');
            }

            if (!colNames.includes('payment_status')) {
                console.log('  Adding payment_status column...');
                await conn.query("ALTER TABLE transactions ADD COLUMN payment_status ENUM('paid','unpaid','partial','debt') DEFAULT 'paid'");
            }

            if (!colNames.includes('due_date')) {
                console.log('  Adding due_date column...');
                await conn.query('ALTER TABLE transactions ADD COLUMN due_date DATE DEFAULT NULL');
            }

            if (!colNames.includes('tax')) {
                console.log('  Adding tax column...');
                await conn.query('ALTER TABLE transactions ADD COLUMN tax DECIMAL(15,2) DEFAULT 0');
            }

            console.log('  Done!');
        } catch (e) {
            console.error('  Error:', e.message);
        }

        await conn.end();
    }

    console.log('\nAll done!');
}

fixMissingColumns().catch(e => console.error('Error:', e.message));
