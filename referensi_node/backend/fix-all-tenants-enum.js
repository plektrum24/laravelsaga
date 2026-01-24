const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixAllTenants() {
    console.log('Starting Fix for ALL Tenants...');

    // Connect to Main to list DBs
    const mainConn = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
    }); // No database selected initially

    try {
        const [dbs] = await mainConn.execute("SHOW DATABASES LIKE 'sagatoko_%'");
        const dbNames = dbs.map(r => Object.values(r)[0]); // Get the value of the first column
        console.log('Found Databases:', dbNames);

        for (const dbName of dbNames) {
            if (dbName === 'sagatoko_system') continue; // Skip system db

            console.log(`\nProcessing ${dbName}...`);
            let dbConn;
            try {
                dbConn = await mysql.createConnection({
                    host: process.env.DB_HOST,
                    user: process.env.DB_USER,
                    password: process.env.DB_PASSWORD,
                    database: dbName
                });

                // Check table existence
                const [tables] = await dbConn.execute("SHOW TABLES LIKE 'stock_transfers'");
                if (tables.length === 0) {
                    console.log(`Skipping ${dbName} (no stock_transfers table)`);
                    continue;
                }

                // 1. Fix ENUM
                console.log(' - Fixing ENUM...');
                await dbConn.execute(`
                    ALTER TABLE stock_transfers 
                    MODIFY COLUMN status ENUM('pending', 'shipped', 'received', 'cancelled') NOT NULL DEFAULT 'pending'
                `);

                // 2. Fix Broken Data
                console.log(' - Fixing Records...');
                const [res] = await dbConn.execute(`
                    UPDATE stock_transfers 
                    SET status = 'shipped' 
                    WHERE status = '' AND approved_at IS NOT NULL
                `);
                console.log(` - Fixed ${res.affectedRows} records.`);

            } catch (e) {
                console.error(`Error processing ${dbName}:`, e.message);
            } finally {
                if (dbConn) await dbConn.end();
            }
        }
    } catch (err) {
        console.error('Fatal Error:', err);
    } finally {
        await mainConn.end();
    }
}

fixAllTenants();
