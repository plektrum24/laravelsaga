const mysql = require('mysql2/promise');
require('dotenv').config();

async function setup() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: 'saga_tenant_jkt001'
    });

    console.log('Connected to database...');

    try {
        // 1. Ensure Source Branch
        let [rows] = await connection.execute('SELECT id FROM branches WHERE name = ?', ['Cabang Source']);
        let sourceId;
        if (rows.length > 0) {
            sourceId = rows[0].id;
        } else {
            const [res] = await connection.execute('INSERT INTO branches (name, code, is_main, is_active) VALUES (?, ?, ?, ?)',
                ['Cabang Source', 'CAB-SRC-01', 0, 1]);
            sourceId = res.insertId;
        }
        console.log(`Source Branch ID: ${sourceId}`);

        // 2. Ensure Dest Branch
        [rows] = await connection.execute('SELECT id FROM branches WHERE name = ?', ['Cabang Dest']);
        let destId;
        if (rows.length > 0) {
            destId = rows[0].id;
        } else {
            const [res] = await connection.execute('INSERT INTO branches (name, code, is_main, is_active) VALUES (?, ?, ?, ?)',
                ['Cabang Dest', 'CAB-DST-01', 0, 1]);
            destId = res.insertId;
        }
        console.log(`Dest Branch ID: ${destId}`);

        // 3. Get a Product
        [rows] = await connection.execute('SELECT id, name FROM products WHERE is_active = 1 LIMIT 1');
        if (rows.length === 0) throw new Error('No active products found');
        const product = rows[0];
        console.log(`Product: ${product.name} (ID: ${product.id})`);

        // 4. Ensure Stock in Source
        // Check if branch_stock entry exists
        [rows] = await connection.execute('SELECT id FROM branch_stock WHERE branch_id = ? AND product_id = ?', [sourceId, product.id]);
        if (rows.length === 0) {
            await connection.execute('INSERT INTO branch_stock (branch_id, product_id, stock) VALUES (?, ?, ?)',
                [sourceId, product.id, 100]);
        } else {
            await connection.execute('UPDATE branch_stock SET stock = 100 WHERE id = ?', [rows[0].id]);
        }
        console.log(`Updated stock for Product ${product.id} in Branch ${sourceId} to 100`);

        // 5. Ensure Entry for Dest (optional, but good for cleanliness, though transfer logic handles create)
        // We leave it empty to test "Create new entry" logic in receive.

        console.log('Setup Complete');
    } catch (error) {
        console.error('Setup error:', error);
    } finally {
        await connection.end();
    }
}

setup();
