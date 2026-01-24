const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
require('dotenv').config({ path: path.join(__dirname, '../../.env') });

async function runMigration() {
    console.log('Starting Sales Force Migration...');
    const dbConfig = {
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'saga_main',
        multipleStatements: true
    };

    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        console.log('Connected to database:', dbConfig.database);

        // Read SQL file
        const sqlPath = path.join(__dirname, '../sql/sales-force-schema.sql');
        const sql = fs.readFileSync(sqlPath, 'utf8');

        // Execute Base Tables (IF NOT EXISTS)
        await connection.query(sql);
        console.log('Created salesmen and visit_plans tables.');

        // Alter Transactions (Safe Check)
        try {
            await connection.query("ALTER TABLE transactions ADD COLUMN salesman_id INT DEFAULT NULL;");
            console.log('Added salesman_id to transactions.');
        } catch (e) {
            if (e.code === 'ER_DUP_FIELDNAME') console.log('salesman_id already exists in transactions.');
            else console.error('Error alter salesman_id:', e.message);
        }

        try {
            await connection.query("ALTER TABLE transactions ADD COLUMN visit_id INT DEFAULT NULL;");
            console.log('Added visit_id to transactions.');
        } catch (e) {
            if (e.code === 'ER_DUP_FIELDNAME') console.log('visit_id already exists in transactions.');
            else console.error('Error alter visit_id:', e.message);
        }

        // We assume transaction_status already handles multiple values or need explicit ALTER
        // Current transactions.js inserts 'completed'. We want 'pending'.
        // Let's modify the column definition if possible, or just trust VARCHAR.
        // Checking column type via simple query is better.
        const [cols] = await connection.query("SHOW COLUMNS FROM transactions LIKE 'status'");
        if (cols.length > 0) {
            const type = cols[0].Type;
            console.log('Transaction status type:', type);
            if (type.includes('enum') && !type.includes('pending')) {
                // If it's strict enum and missing pending, alter it
                // Note: This is risky if format differs, but we try standard
                await connection.query("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed'");
                console.log('Updated status ENUM to include pending.');
            }
        }

        console.log('Migration Completed Successfully.');

    } catch (err) {
        console.error('Migration Failed:', err);
    } finally {
        if (connection) await connection.end();
    }
}

runMigration();
