require('dotenv').config();
const mysql = require('mysql2/promise');

async function checkIndices() {
    try {
        const pool = mysql.createPool({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME // Checking Main DB first, but triggers are usually on Tenant DBs.
        });

        console.log('--- Checking Indices for product_units ---');

        // We need to verify for a specific Tenant DB.
        // Assuming 'sagatoko_tenant_plektrum24' exists or usually user selects one.
        // I'll list databases first.
        const [dbs] = await pool.execute("SHOW DATABASES LIKE 'sagatoko_tenant_%'");
        if (dbs.length === 0) {
            console.log('No tenant databases found.');
            return;
        }

        const tenantDbName = dbs[0]['Database (sagatoko_tenant_%)'];
        console.log(`Checking Tenant DB: ${tenantDbName}`);

        const [tables] = await pool.execute(`SHOW TABLES FROM ${tenantDbName} LIKE 'product_units'`);
        if (tables.length === 0) {
            console.log('product_units table not found');
            return;
        }

        const [indices] = await pool.execute(`SHOW INDEX FROM ${tenantDbName}.product_units`);
        console.table(indices);

        console.log('\n--- Checking Triggers ---');
        const [triggers] = await pool.execute(`SHOW TRIGGERS FROM ${tenantDbName}`);
        console.table(triggers);

        process.exit(0);
    } catch (error) {
        console.error('Error:', error);
        process.exit(1);
    }
}

checkIndices();
