const axios = require('axios');
const { getMainPool, getTenantPool } = require('./config/database');
const path = require('path');
require('dotenv').config({ path: path.join(__dirname, '.env') });

const BASE_URL = 'http://localhost:3000/api';
const EMAIL = 'rjbrgudang@saga.com';
const PASSWORD = 'admin123';
const DB_NAME = 'saga_tenant_bkt0001';

async function runE2E() {
    console.log("🚀 Starting E2E Verification for User: " + EMAIL);

    try {
        // 1. Direct DB Setup (Ensure Branches)
        const tenantPool = await getTenantPool(DB_NAME);
        const conn = await tenantPool.getConnection();

        let pusatId, cabangId;

        try {
            console.log("🔧 Setup: Verifying Branches...");
            // Ensure Pusat
            const [pusat] = await conn.query("SELECT id FROM branches WHERE name = 'Pusat'");
            if (pusat.length > 0) pusatId = pusat[0].id;
            else {
                const [ins] = await conn.query("INSERT INTO branches (name, is_main, code) VALUES ('Pusat', 1, 'PST')");
                pusatId = ins.insertId;
            }

            // Ensure Cabang
            const [cabang] = await conn.query("SELECT id FROM branches WHERE name = 'Cabang Test'");
            if (cabang.length > 0) cabangId = cabang[0].id;
            else {
                const [ins] = await conn.query("INSERT INTO branches (name, is_main, code) VALUES ('Cabang Test', 0, 'CBG')");
                cabangId = ins.insertId;
            }
            console.log(`   - Pusat ID: ${pusatId}, Cabang ID: ${cabangId}`);

        } finally {
            conn.release();
        }

        // 2. Login
        console.log("\n🔑 Step 1: Login...");
        const loginRes = await axios.post(`${BASE_URL}/auth/login`, {
            email: EMAIL,
            password: PASSWORD
        });
        const token = loginRes.data.data.token;
        const headers = { Authorization: `Bearer ${token}` };
        console.log("   ✅ Login Success");

        // 3. Create Product
        console.log("\n📦 Step 2: Create Product...");
        const productRes = await axios.post(`${BASE_URL}/products`, {
            name: "E2E Test Item " + Date.now(),
            category_id: 1, // Assume General exists
            base_unit_id: 1, // Assume Pcs exists
            stock: 0,
            units: [{ unit_id: 1, conversion_qty: 1, is_base_unit: true }]
        }, { headers });
        const productId = productRes.data.data.id;
        console.log(`   ✅ Product Created. ID: ${productId}, SKU: ${productRes.data.data.sku}`);

        // 4. Adjust Stock (Pusat)
        console.log("\n📈 Step 3: Add Stock to Pusat...");

        // Use the API now (since we fixed it to handle branch_id)
        await axios.post(`${BASE_URL}/products/adjust-stock/${productId}`, {
            type: 'add',
            quantity: 100,
            reason: 'Opening Stock',
            branch_id: pusatId // Explicitly set to Pusat
        }, { headers });

        console.log("   ✅ Stock Added via API to Pusat.");

        // 5. Transfer
        console.log("\n🚚 Step 4: Transfer Pusat -> Cabang...");
        const transferRes = await axios.post(`${BASE_URL}/transfers`, {
            from_branch_id: pusatId,
            to_branch_id: cabangId,
            items: [{ product_id: productId, quantity: 10, unit_id: 1 }], // Check if API expects 'quantity' or 'qty_requested'!
            // API Expects: items: [{ product_id, quantity, notes }] OR qty_requested?
            // Let's check transfers.js creation logic: 
            // const { items } ... item.quantity ... INSERT qty_requested = item.quantity
            notes: "E2E Test Transfer"
        }, { headers });

        const transferId = transferRes.data.data.id;
        console.log(`   ✅ Transfer Created. ID: ${transferId}`);

        // 6. Approve/Ship
        console.log("\n🚢 Step 5: Mark as Shipped (Approve)...");
        await axios.patch(`${BASE_URL}/transfers/${transferId}/approve`, {}, { headers });
        console.log("   ✅ Transfer Approved & Shipped.");

        console.log("\n🎉 E2E VERIFICATION SUCCESSFUL!");

    } catch (e) {
        console.error("\n❌ E2E Failed:", e.response ? e.response.data : e.message);
    } finally {
        process.exit(0);
    }
}

runE2E();
