require('dotenv').config({ path: '../.env' });
const mysql = require('mysql2/promise');
const fs = require('fs');
const path = require('path');

async function migrate() {
    console.log('--- FORCING MIGRATION ON TENANT DB ---');
    try {
        const conn = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: 'saga_tenant_bkt0001', // HARDCODED TARGET
            multipleStatements: true
        });

        const sqlPath = path.join(__dirname, 'sql', 'migrate-barcode.sql');
        const sql = fs.readFileSync(sqlPath, 'utf8');

        console.log('Running SQL from:', sqlPath);
        console.log(sql);

        await conn.query(sql);
        console.log('✅ Migration executed successfully!');

        await conn.end();
    } catch (e) {
        console.error('❌ Migration Error:', e.message);
    }
}

migrate();
