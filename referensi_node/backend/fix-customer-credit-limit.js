const mysql = require('mysql2/promise');
const dotenv = require('dotenv');
const path = require('path');

dotenv.config({ path: path.join(__dirname, '.env') });

const dbConfig = {
    host: process.env.DB_HOST,
    user: process.env.DB_USER,
    password: process.env.DB_PASSWORD,
    database: 'saga_tenant_bkt0001' // Target dev tenant
};

async function fixSchema() {
    let connection;
    try {
        connection = await mysql.createConnection(dbConfig);
        console.log(`Connected to ${dbConfig.database}`);

        // Check/Add credit_limit
        const [columns] = await connection.query(`SHOW COLUMNS FROM customers`);
        const colNames = columns.map(c => c.Field);

        if (!colNames.includes('credit_limit')) {
            await connection.query(`ALTER TABLE customers ADD COLUMN credit_limit DECIMAL(15,2) DEFAULT 0 AFTER address`);
            console.log('✅ Added credit_limit column to customers');
        } else {
            console.log('ℹ️ credit_limit column already exists');
        }

    } catch (error) {
        console.error('❌ Schema update failed:', error);
    } finally {
        if (connection) await connection.end();
    }
}

fixSchema();
