const { createTenantDatabase, dropTenantDatabase } = require('./utils/dbGenerator');
require('dotenv').config();

const DB_NAME = 'saga_tenant_verif_03';

async function run() {
    try {
        console.log(`🧪 Testing Tenant Creation: ${DB_NAME}...`);

        await dropTenantDatabase(DB_NAME);
        await createTenantDatabase(DB_NAME);

        console.log("✅ VERIFICATION SUCCESS: Tenant created without error.");

    } catch (error) {
        console.error("❌ VERIFICATION FAILED:", error);
    } finally {
        process.exit(0);
    }
}

run();
