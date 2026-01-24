const { getMainPool } = require('./config/database');
const mysql = require('mysql2/promise');
require('dotenv').config();

process.on('unhandledRejection', (reason, p) => {
    console.error('Unhandled Rejection at:', p, 'reason:', reason);
});

async function copyUnitsFix() {
    console.log("🚀 Starting Unit Copy Fix...");

    let sourceConn, destConn;

    try {
        const mainPool = await getMainPool();

        // 1. Find Source (JKT)
        const [sources] = await mainPool.query("SELECT * FROM tenants WHERE name LIKE '%jkt%' OR database_name LIKE '%jkt%' LIMIT 1");
        if (sources.length === 0) throw new Error("Source tenant (JKT) not found!");
        const sourceTenant = sources[0];
        console.log(`✅ Source: ${sourceTenant.database_name}`);

        // 2. Find Destination (BKT)
        const [dests] = await mainPool.query("SELECT * FROM tenants WHERE name LIKE '%bkt%' OR database_name LIKE '%bkt%' LIMIT 1");
        if (dests.length === 0) throw new Error("Destination tenant (BKT) not found!");
        const destTenant = dests[0];
        console.log(`✅ Dest: ${destTenant.database_name}`);

        // 3. Connect
        sourceConn = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: sourceTenant.database_name
        });

        destConn = await mysql.createConnection({
            host: process.env.DB_HOST,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: destTenant.database_name
        });

        // 4. Copy Units with Fix
        console.log("📏 Copying/Fixing Units...");
        let sourceUnits = [];
        try {
            const [units] = await sourceConn.query("SELECT * FROM units");
            sourceUnits = units;
        } catch (e) {
            console.log("❌ Source has no units table! Cannot copy.");
            return;
        }

        for (const unit of sourceUnits) {
            try {
                const [existing] = await destConn.query("SELECT id FROM units WHERE name = ?", [unit.name]);

                if (existing.length > 0) {
                    console.log(`   - Skipped (Exists): ${unit.name}`);
                    continue;
                }

                // Fix Logic: Provide default short_name if missing
                let shortName = unit.short_name;
                if (!shortName) {
                    shortName = unit.name.toLowerCase().substring(0, 20); // Default to lowercase name
                    console.log(`     ⚠️  Generating short_name for '${unit.name}': '${shortName}'`);
                }

                // Fix Logic: Ensure type is valid enum
                let type = unit.type;
                const validTypes = ['quantity', 'weight', 'volume'];
                if (!validTypes.includes(type)) {
                    type = 'quantity'; // Default
                }

                await destConn.query(
                    "INSERT INTO units (name, short_name, type) VALUES (?, ?, ?)",
                    [unit.name, shortName, type]
                );
                console.log(`   + Copied: ${unit.name}`);

            } catch (e) { console.error(`   ❌ Failed unit ${unit.name}: ${e.message}`); }
        }

        console.log("✅ DONE.");

    } catch (e) {
        console.error("❌ Critical Error:", e);
    } finally {
        if (sourceConn) sourceConn.end();
        if (destConn) destConn.end();
        process.exit(0);
    }
}

copyUnitsFix();
