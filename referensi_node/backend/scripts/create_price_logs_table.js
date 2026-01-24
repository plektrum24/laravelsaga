require('dotenv').config();
const mysql = require('mysql2/promise');

async function createPriceLogsTable() {
    console.log('Starting migration: create_price_logs_table');

    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        details: { port: process.env.DB_PORT } // Handle different port configs if needed
    });

    try {
        // Fetch all tenant DBs
        // Assuming tenants are managed in a main DB or we iterate known prefixes.
        // For this project structure, it seems we often iterate or target specific DBs.
        // Let's check existing scripts pattern. usually 'sagatokov3_tenant_...'

        // Simpler approach: Check current DB from .env or just hardcode for rj0001 as target first
        // But better to make it generic.

        // Let's assume we run this against specific tenant DB passed in env or hardcoded for now,
        // or discover from information_schema.

        // Based on previous context, we are working with `saga_tenant_rj0001`.
        const targetDb = 'saga_tenant_rj0001';

        console.log(`Connecting to ${targetDb}...`);
        await connection.changeUser({ database: targetDb });

        const createTableSql = `
            CREATE TABLE IF NOT EXISTS price_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                product_id INT NOT NULL,
                unit_id INT NOT NULL,
                user_id INT DEFAULT NULL,
                old_price DECIMAL(15, 2) NOT NULL,
                new_price DECIMAL(15, 2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
        `;

        await connection.execute(createTableSql);
        console.log('✅ Table price_logs created (or already exists).');

    } catch (error) {
        console.error('Migration failed:', error);
    } finally {
        await connection.end();
    }
}

createPriceLogsTable();
