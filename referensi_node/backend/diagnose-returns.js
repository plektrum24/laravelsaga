require('dotenv').config();
const mysql = require('mysql2/promise');

async function runDiagnosis() {
    const supplierId = process.argv[2];
    const productId = process.argv[3];

    if (!supplierId || !productId) {
        console.log('Usage: node backend/diagnose-returns.js <supplier_id> <product_id>');
        process.exit(1);
    }

    console.log(`\n🔍 DIAGNOSIS: Supplier ${supplierId} | Product ${productId}\n`);

    const dbConfig = {
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME
    };

    try {
        const connection = await mysql.createConnection(dbConfig);
        console.log('✅ Connected to DB');

        // 1. Check Product Existence & Global Stock
        const [products] = await connection.execute('SELECT id, name, sku, stock FROM products WHERE id = ?', [productId]);
        if (products.length === 0) {
            console.error('❌ Product NOT FOUND in DB');
            process.exit(1);
        }
        console.log('📦 Product Info:', products[0]);

        // 2. Check Supplier Info
        const [suppliers] = await connection.execute('SELECT id, name FROM suppliers WHERE id = ?', [supplierId]);
        if (suppliers.length === 0) {
            console.error('❌ Supplier NOT FOUND in DB');
        } else {
            console.log('🏭 Supplier Info:', suppliers[0]);
        }

        // 3. Run SEARCH Query Logic (from products.js)
        console.log('\n--- 1. Testing SEARCH Logic (Why it appears in search) ---');
        const searchSql = `
            SELECT pi.id, pi.current_stock, pi.quantity, pur.supplier_id 
            FROM purchase_items pi 
            JOIN purchases pur ON pi.purchase_id = pur.id 
            WHERE pi.product_id = ? 
            AND pur.supplier_id = ? 
            AND (pi.current_stock > 0 OR pi.current_stock IS NULL)
        `;
        const [searchResults] = await connection.execute(searchSql, [productId, supplierId]);
        console.log(`Found ${searchResults.length} Valid Batches (Search Logic):`);
        console.table(searchResults);

        if (searchResults.length > 0) {
            console.log('✅ Search Logic Correct: Product SHOULD appear in search.');
        } else {
            console.log('❌ Search Logic Miss: Product Should NOT have appeared in search.');
        }

        // 4. Run RETURN BATCHES Logic (from purchase-returns.js)
        console.log('\n--- 2. Testing RETURN BATCHES Logic (Why it says Empty) ---');
        const returnSql = `
            SELECT 
                pi.id as batch_id,
                pi.current_stock,
                pur.supplier_id
            FROM purchase_items pi
            JOIN products p ON pi.product_id = p.id
            JOIN purchases pur ON pi.purchase_id = pur.id
            WHERE pi.product_id = ?
            AND (pi.current_stock > 0 OR pi.current_stock IS NULL)
            AND pur.supplier_id = ?
        `;
        const [returnResults] = await connection.execute(returnSql, [productId, supplierId]);
        console.log(`Found ${returnResults.length} Valid Batches (Return Logic):`);
        console.table(returnResults);

        if (returnResults.length === 0) {
            console.error('❌ RETURN LOGIC RETURNS NOTHING! (Data exists but query failed)');
        } else {
            console.log('✅ RETURN LOGIC FINDS DATA! (Server code must be stale)');
        }

        connection.end();

    } catch (e) {
        console.error('❌ Error:', e);
    }
}

runDiagnosis();
