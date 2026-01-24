require('dotenv').config({ path: '../.env' });
const mysql = require('mysql2/promise');

async function migrate() {
    const config = {
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: process.env.DB_NAME || 'saga_main'
    };

    console.log('Connecting to DB:', config.host, config.database);

    let connection;
    try {
        connection = await mysql.createConnection(config);
        console.log('Connected!');

        // Check if tenants table exists to iterate over all tenant DBs (if centralized)
        const [tables] = await connection.execute('SHOW TABLES LIKE "products"');
        if (tables.length > 0) {
            console.log('Migrating saga_main...');
            await runMigration(connection);
        } else {
            console.log('products table not found in saga_main. Checking tenants...');
            const [tenants] = await connection.execute('SELECT * FROM tenants');
            for (const t of tenants) {
                console.log('Migrating tenant:', t.db_name);
                const tConn = await mysql.createConnection({ ...config, database: t.db_name });
                await runMigration(tConn);
                await tConn.end();
            }
        }

        console.log('Migration Complete!');
    } catch (e) {
        console.error('Migration Failed:', e);
    } finally {
        if (connection) await connection.end();
        process.exit(0);
    }
}

async function runMigration(conn) {
    try {
        // ADD COLUMN
        await conn.execute('ALTER TABLE products ADD COLUMN barcode VARCHAR(255) DEFAULT NULL');
        console.log('Column added.');
        // ADD INDEX
        await conn.execute('CREATE INDEX idx_products_barcode ON products(barcode)');
        console.log('Index added.');
    } catch (e) {
        if (e.code === 'ER_DUP_FIELDNAME') {
            console.log('Column already exists. Skipping.');
        } else {
            console.error('SQL Error:', e.message);
        }
    }
}

migrate();
