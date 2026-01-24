const mysql = require('mysql2/promise');

async function fixAllTenants() {
    console.log('Finding tenant databases...');

    // Get list of tenant databases
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

        // Connect directly to tenant DB
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

            // Check if tax column exists
            const [cols] = await conn.query("SHOW COLUMNS FROM transactions LIKE 'tax'");
            if (cols.length === 0) {
                console.log('  Adding tax column...');
                await conn.query('ALTER TABLE transactions ADD COLUMN tax DECIMAL(15,2) DEFAULT 0');
                console.log('  Done!');
            } else {
                console.log('  tax column already exists');
            }

            // Fix payment_method ENUM
            console.log('  Fixing payment_method ENUM...');
            await conn.query(`
                ALTER TABLE transactions 
                MODIFY COLUMN payment_method ENUM('cash','debit','credit','qris','debt') NOT NULL DEFAULT 'cash'
            `);
            console.log('  Done!');
        } catch (e) {
            console.error('  Error:', e.message);
        }

        await conn.end();
    }

    console.log('\nAll done!');
}

fixAllTenants().catch(e => console.error('Error:', e.message));
