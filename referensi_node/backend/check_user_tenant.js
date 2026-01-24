const { getMainPool } = require('./config/database');
const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkUserTenant() {
    try {
        const pool = await getMainPool();
        const [users] = await pool.query("SELECT * FROM users WHERE email = 'rjbrgudang@saga.com'");

        if (users.length === 0) {
            console.log("❌ User 'rjbrgudang@saga.com' NOT FOUND in system.");
            return;
        }

        const user = users[0];
        console.log(`✅ User Found: ${user.name} (ID: ${user.id}, TenantID: ${user.tenant_id})`);

        const [tenants] = await pool.query("SELECT * FROM tenants WHERE id = ?", [user.tenant_id]);
        if (tenants.length === 0) {
            console.log("❌ Tenant NOT FOUND.");
            return;
        }

        const tenant = tenants[0];
        console.log(`✅ Tenant: ${tenant.name} (DB: ${tenant.database_name})`);

    } catch (e) {
        console.error("Error:", e);
    }
    process.exit(0);
}

checkUserTenant();
