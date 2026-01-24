const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '.env') });
const mysql = require('mysql2/promise');

const DATABASE_NAME = 'saga_tenant_bkt0001'; // Target specific tenant

const createSuppliersTable = async () => {
    let connection;
    try {
        console.log(`🔌 Connecting to database: ${DATABASE_NAME}...`);
        connection = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: DATABASE_NAME
        });

        console.log('📦 Creating suppliers table...');

        const sql = `
            CREATE TABLE IF NOT EXISTS suppliers (
                id INT PRIMARY KEY AUTO_INCREMENT,
                name VARCHAR(100) NOT NULL,
                contact_person VARCHAR(100),
                phone VARCHAR(20),
                address TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        `;

        await connection.query(sql);
        console.log('✅ Suppliers table created successfully!');

    } catch (error) {
        console.error('❌ Error creating suppliers table:', error);
    } finally {
        if (connection) await connection.end();
    }
};

createSuppliersTable();
