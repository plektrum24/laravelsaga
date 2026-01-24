const mysql = require('mysql2/promise');
require('dotenv').config();

async function fixTenantsSchema() {
    console.log('Fixing tenants table in Main DB...');

    // Connect to Main DB
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST,
        user: process.env.DB_USER,
        password: process.env.DB_PASSWORD,
        database: process.env.DB_NAME // sagatoko_system
    });

    try {
        // 1. Check if column exists
        const [cols] = await connection.execute("SHOW COLUMNS FROM tenants LIKE 'database_name'");
        if (cols.length === 0) {
            console.log('Adding database_name column...');
            await connection.execute("ALTER TABLE tenants ADD COLUMN database_name VARCHAR(255) AFTER subdomain");
        } else {
            console.log('database_name column already exists.');
        }

        // 2. Update Tenant 1
        console.log('Updating Tenant 1 configuration...');
        const [res] = await connection.execute(`
            UPDATE tenants 
            SET database_name = 'sagatoko_tenant_sagatoko' 
            WHERE id = 1
        `);
        console.log(`Updated ${res.affectedRows} tenant(s).`);

        // Verify
        const [rows] = await connection.execute("SELECT * FROM tenants WHERE id = 1");
        console.log('Tenant 1 Config:', rows[0]);

    } catch (err) {
        console.error('Fix failed:', err);
    } finally {
        await connection.end();
    }
}

fixTenantsSchema();
