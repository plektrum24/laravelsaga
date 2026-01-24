const { getMainPool } = require('./config/database');
const mysql = require('mysql2/promise');
require('dotenv').config();

process.on('unhandledRejection', (reason, p) => {
    console.error('Unhandled Rejection at:', p, 'reason:', reason);
});

async function copyTenantData() {
    console.log("🚀 Starting Safe Copy (.query)...");

    let sourceConn, destConn;

    try {
        const mainPool = await getMainPool();

        const [sources] = await mainPool.query("SELECT * FROM tenants WHERE name LIKE '%jkt%' OR database_name LIKE '%jkt%' LIMIT 1");
        if (sources.length === 0) throw new Error("Source tenant (JKT) not found!");
        const sourceTenant = sources[0];
        console.log(`✅ Source: ${sourceTenant.database_name}`);

        const [dests] = await mainPool.query("SELECT * FROM tenants WHERE name LIKE '%bkt%' OR database_name LIKE '%bkt%' LIMIT 1");
        if (dests.length === 0) throw new Error("Destination tenant (BKT) not found!");
        const destTenant = dests[0];
        console.log(`✅ Dest: ${destTenant.database_name}`);

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

        // 4. Copy Categories
        console.log("📦 Copying Categories...");
        const [categories] = await sourceConn.query("SELECT * FROM categories WHERE is_active = true");
        const categoryMap = {};

        for (const cat of categories) {
            try {
                const [existing] = await destConn.query("SELECT id FROM categories WHERE name = ?", [cat.name]);
                let catId;
                if (existing.length > 0) {
                    catId = existing[0].id;
                } else {
                    const [result] = await destConn.query("INSERT INTO categories (name, is_active) VALUES (?, ?)", [cat.name, cat.is_active]);
                    catId = result.insertId;
                }
                categoryMap[cat.id] = catId;
            } catch (e) { console.error(`Failed cat ${cat.name}: ${e.message}`); }
        }

        // 5. Copy Units
        console.log("📏 Copying Units...");
        let sourceUnits = [];
        try {
            const [units] = await sourceConn.query("SELECT * FROM units");
            sourceUnits = units;
        } catch (e) { console.log("   (Source has no units table)"); }

        const unitMap = {};
        for (const unit of sourceUnits) {
            try {
                const [existing] = await destConn.query("SELECT id FROM units WHERE name = ?", [unit.name]);
                let unitId;
                if (existing.length > 0) {
                    unitId = existing[0].id;
                } else {
                    const [result] = await destConn.query("INSERT INTO units (name, short_name, type) VALUES (?, ?, ?)", [unit.name, unit.short_name, unit.type]);
                    unitId = result.insertId;
                }
                unitMap[unit.id] = unitId;
            } catch (e) { console.error(`Failed unit ${unit.name}: ${e.message}`); }
        }

        // 6. Copy Products
        console.log("🛍️ Copying Products...");
        const [products] = await sourceConn.query("SELECT * FROM products WHERE is_active = true");

        for (const prod of products) {
            try {
                const [existing] = await destConn.query("SELECT id FROM products WHERE sku = ?", [prod.sku]);
                if (existing.length > 0) {
                    continue; // Skip existing
                }

                const newCatId = categoryMap[prod.category_id] || null;
                const newUnitId = unitMap[prod.base_unit_id] || null;

                const [result] = await destConn.query(`
                    INSERT INTO products (
                        sku, name, category_id, base_unit_id, buy_price, sell_price, 
                        stock, min_stock, image_url, is_active
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                `, [
                    prod.sku, prod.name, newCatId, newUnitId, prod.buy_price, prod.sell_price,
                    0, prod.min_stock || 5, prod.image_url, prod.is_active
                ]);

                const newProdId = result.insertId;

                // Copy Product Units
                if (newUnitId) {
                    try {
                        const [prodUnits] = await sourceConn.query("SELECT * FROM product_units WHERE product_id = ?", [prod.id]);
                        for (const pu of prodUnits) {
                            const mappedUnitId = unitMap[pu.unit_id];
                            if (mappedUnitId) {
                                await destConn.query(`
                                    INSERT INTO product_units (product_id, unit_id, conversion_factor, buy_price, sell_price, is_default)
                                    VALUES (?, ?, ?, ?, ?, ?)
                                `, [newProdId, mappedUnitId, pu.conversion_factor, pu.buy_price, pu.sell_price, pu.is_default]);
                            }
                        }
                    } catch (e) { }
                }
            } catch (e) { console.error(`Failed product ${prod.sku}: ${e.message}`); }
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

copyTenantData();
