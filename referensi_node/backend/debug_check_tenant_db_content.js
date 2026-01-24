const { getMainPool } = require('./config/database');
const mysql = require('mysql2/promise');
require('dotenv').config();

async function checkTenantContent() {
    const mainPool = await getMainPool();
    const [users] = await mainPool.execute("SELECT * FROM users WHERE email = 'adminhaikal@saga.com'");

    if (users.length === 0) {
        console.log("❌ User adminhaikal@saga.com NOT FOUND in system users.");
        process.exit(1);
    }

    const user = users[0];
    console.log(`✅ Found user: ${user.name} (Role: ${user.role}, TenantID: ${user.tenant_id})`);

    if (!user.tenant_id) {
        console.log("❌ User has no tenant_id!");
        process.exit(1);
    }

    const [tenants] = await mainPool.execute("SELECT * FROM tenants WHERE id = ?", [user.tenant_id]);
    if (tenants.length === 0) {
        console.log("❌ Tenant NOT FOUND!");
        process.exit(1);
    }

    const tenant = tenants[0];
    console.log(`✅ Found tenant: ${tenant.name} (DB: ${tenant.database_name})`);

    // Connect to tenant DB
    try {
        const tenantConfig = {
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: tenant.database_name
        };
        const tenantConnection = await mysql.createConnection(tenantConfig);
        console.log(`✅ Connected to ${tenant.database_name}`);

        // Check tables
        const [tables] = await tenantConnection.execute("SHOW TABLES");
        console.log("Tables in tenant DB:", tables.map(t => Object.values(t)[0]));

        // Check content
        const [branches] = await tenantConnection.execute("SELECT * FROM branches");
        console.log("Branches:", branches);

        if (branches.length === 0) {
            console.log("❌ Branches table is EMPTY!");
            // Try inserting default
            console.log("Attempting to insert 'Pusat'...");
            await tenantConnection.execute("INSERT INTO branches (name, address, phone, is_active) VALUES ('Pusat', 'Main Office', '', 1)");
            console.log("✅ Inserted 'Pusat'.");
        } else {
            console.log(`✅ Found ${branches.length} branches.`);
        }

        tenantConnection.end();

    } catch (e) {
        console.error("❌ Failed to connect to tenant DB:", e.message);
    } finally {
        process.exit(0);
    }
}

checkTenantContent();
