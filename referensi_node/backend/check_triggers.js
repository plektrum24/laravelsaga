const { getTenantPool } = require('./config/database');
require('dotenv').config();

const DB_NAME = 'saga_tenant_bkt0001';

async function checkTriggers() {
    try {
        console.log(`Checking Triggers in ${DB_NAME}...`);
        const pool = await getTenantPool(DB_NAME);

        const [triggers] = await pool.query("SHOW TRIGGERS");
        console.log("Triggers found:", triggers.length);

        for (const t of triggers) {
            console.log(`Trigger: ${t.Trigger} on Table: ${t.Table}`);
            console.log(`Statement: ${t.Statement}`);
        }
    } catch (e) {
        console.error("Error:", e);
    }
    process.exit(0);
}

checkTriggers();
