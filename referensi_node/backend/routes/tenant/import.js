const express = require('express');
const router = express.Router();
const ExcelJS = require('exceljs');
const multer = require('multer');

// Configure multer for file upload
const storage = multer.memoryStorage();
const upload = multer({
    storage,
    limits: { fileSize: 10 * 1024 * 1024 }, // 10MB limit
    fileFilter: (req, file, cb) => {
        if (file.mimetype === 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
            file.mimetype === 'application/vnd.ms-excel') {
            cb(null, true);
        } else {
            cb(new Error('Only Excel files allowed'));
        }
    }
});

/**
 * POST /api/import/products
 * Import products from Excel file
 * 
 * COLUMN STRUCTURE (A-Y = 25 columns):
 * A: NAMAITEM (nama produk) - WAJIB
 * B: KATEGORI (nama kategori) - WAJIB  
 * C: SATUAN1 (satuan dasar/terkecil, misal PCS) - WAJIB
 * D: SATUAN2 (satuan level 2, misal PAK)
 * E: SATUAN3 (satuan level 3, misal DUS)
 * F: SATUAN4 (satuan level 4, misal KARUNG)
 * G: SATUAN5 (satuan level 5, misal PALET)
 * H: KONVERSI1 (selalu 1 untuk satuan dasar)
 * I: KONVERSI2 (berapa SATUAN1 dalam 1 SATUAN2, misal 12)
 * J: KONVERSI3 (berapa SATUAN1 dalam 1 SATUAN3, misal 48)
 * K: KONVERSI4 (berapa SATUAN1 dalam 1 SATUAN4)
 * L: KONVERSI5 (berapa SATUAN1 dalam 1 SATUAN5)
 * M: HARGABELI1 (harga beli SATUAN1)
 * N: HARGABELI2 (harga beli SATUAN2)
 * O: HARGABELI3 (harga beli SATUAN3)
 * P: HARGABELI4 (harga beli SATUAN4)
 * Q: HARGABELI5 (harga beli SATUAN5)
 * R: HARGAJUAL1 (harga jual SATUAN1)
 * S: HARGAJUAL2 (harga jual SATUAN2)
 * T: HARGAJUAL3 (harga jual SATUAN3)
 * U: HARGAJUAL4 (harga jual SATUAN4)
 * V: HARGAJUAL5 (harga jual SATUAN5)
 * W: STOK (stok dalam satuan dasar)
 * X: STOKMIN (minimum stok)
 * Y: EXPIRY (tanggal kadaluarsa, format: YYYY-MM-DD atau DD/MM/YYYY, kosong = tidak ada expiry)
 */
router.post('/products', upload.single('file'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ success: false, message: 'No file uploaded' });
        }

        const workbook = new ExcelJS.Workbook();
        await workbook.xlsx.load(req.file.buffer);

        const sheet = workbook.getWorksheet(1);
        if (!sheet) {
            return res.status(400).json({ success: false, message: 'No worksheet found' });
        }

        // Lazy Migration: Ensure required tables exist for stock import
        // This handles existing tenant databases that may be missing these tables
        try {
            await req.tenantDb.execute(`
                CREATE TABLE IF NOT EXISTS suppliers (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    contact_person VARCHAR(100),
                    phone VARCHAR(20),
                    address TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            `);
        } catch (e) { /* Table already exists */ }

        try {
            await req.tenantDb.execute(`
                CREATE TABLE IF NOT EXISTS purchases (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    branch_id INT,
                    supplier_id INT,
                    invoice_number VARCHAR(50),
                    date DATE NOT NULL,
                    due_date DATE,
                    total_amount DECIMAL(15,2) DEFAULT 0,
                    paid_amount DECIMAL(15,2) DEFAULT 0,
                    payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            `);
        } catch (e) { /* Table already exists */ }

        try {
            await req.tenantDb.execute(`
                CREATE TABLE IF NOT EXISTS purchase_items (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    purchase_id INT NOT NULL,
                    product_id INT NOT NULL,
                    quantity DECIMAL(15,4) NOT NULL,
                    unit_price DECIMAL(15,2) NOT NULL,
                    subtotal DECIMAL(15,2) NOT NULL,
                    unit_id INT,
                    expiry_date DATE,
                    current_stock DECIMAL(15,4) DEFAULT NULL,
                    conversion_qty DECIMAL(15,4) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            `);
        } catch (e) { /* Table already exists */ }

        // Ensure current_stock column exists (for older databases)
        try {
            await req.tenantDb.execute(`ALTER TABLE purchase_items ADD COLUMN current_stock DECIMAL(15,4) DEFAULT NULL`);
        } catch (e) { /* Column already exists */ }

        // Get categories and units for lookup
        const [categories] = await req.tenantDb.execute('SELECT id, name, prefix FROM categories');
        const [units] = await req.tenantDb.execute('SELECT id, name FROM units');

        const categoryMap = {};
        categories.forEach(c => categoryMap[c.name.toUpperCase().trim()] = c);

        // Helper to get or create category
        const getOrCreateCategory = async (catName) => {
            const key = catName.toUpperCase().trim();
            if (categoryMap[key]) {
                return categoryMap[key];
            }

            // Auto-create category if not exists
            try {
                const prefix = catName.trim().substring(0, 3).toUpperCase();
                const [result] = await req.tenantDb.execute(
                    'INSERT INTO categories (name, prefix, is_active) VALUES (?, ?, true)',
                    [catName.trim(), prefix]
                );
                const newCat = { id: result.insertId, name: catName.trim(), prefix };
                categoryMap[key] = newCat;
                console.log(`Auto-created category: ${catName}`);
                return newCat;
            } catch (err) {
                console.error(`Failed to create category ${catName}:`, err.message);
                return null;
            }
        };

        // Build unit map - case insensitive matching
        // Key = uppercase name, Value = unit object with id
        const unitMap = {};

        // Add all system units with uppercase key
        units.forEach(u => {
            const key = u.name.toUpperCase().trim();
            unitMap[key] = u;
        });

        // Log available units for debugging
        console.log('Available units in system:', units.map(u => u.name).join(', '));

        // Helper to get or create unit
        const getOrCreateUnit = async (unitName) => {
            const key = unitName.toUpperCase().trim();
            if (unitMap[key]) {
                return unitMap[key];
            }

            // Auto-create unit if not exists
            try {
                const [result] = await req.tenantDb.execute(
                    'INSERT INTO units (name, sort_order) VALUES (?, ?)',
                    [unitName.trim(), units.length + Object.keys(unitMap).length]
                );
                const newUnit = { id: result.insertId, name: unitName.trim() };
                unitMap[key] = newUnit;
                console.log(`Auto-created unit: ${unitName}`);
                return newUnit;
            } catch (err) {
                console.error(`Failed to create unit ${unitName}:`, err.message);
                return null;
            }
        };

        const results = { created: 0, errors: [] };
        const currentYear = new Date().getFullYear();

        // Column indices (1-indexed for ExcelJS)
        const COL = {
            NAMAITEM: 1,    // A
            KATEGORI: 2,    // B
            SATUAN1: 3,     // C (base unit - WAJIB)
            SATUAN2: 4,     // D
            SATUAN3: 5,     // E
            SATUAN4: 6,     // F
            SATUAN5: 7,     // G
            KONVERSI1: 8,   // H (always 1)
            KONVERSI2: 9,   // I
            KONVERSI3: 10,  // J
            KONVERSI4: 11,  // K
            KONVERSI5: 12,  // L
            HARGABELI1: 13, // M
            HARGABELI2: 14, // N
            HARGABELI3: 15, // O
            HARGABELI4: 16, // P
            HARGABELI5: 17, // Q
            HARGAJUAL1: 18, // R
            HARGAJUAL2: 19, // S
            HARGAJUAL3: 20, // T
            HARGAJUAL4: 21, // U
            HARGAJUAL5: 22, // V
            STOK: 23,       // W
            STOKMIN: 24,    // X
            EXPIRY: 25      // Y - Expiry Date (optional, format: YYYY-MM-DD or DD/MM/YYYY)
        };

        // Process rows (skip header row 1)
        for (let rowNum = 2; rowNum <= sheet.rowCount; rowNum++) {
            const row = sheet.getRow(rowNum);

            // Helper to get cell value
            const getVal = (col) => {
                const cell = row.getCell(col);
                if (cell.value === null || cell.value === undefined) return null;
                if (typeof cell.value === 'object' && cell.value.text) return cell.value.text;
                if (typeof cell.value === 'object' && cell.value.result) return cell.value.result;
                return cell.value;
            };

            const name = getVal(COL.NAMAITEM)?.toString().trim();
            const categoryName = getVal(COL.KATEGORI)?.toString().trim();
            const baseUnitName = getVal(COL.SATUAN1)?.toString().trim();

            // Skip empty rows
            if (!name) continue;

            // Validate required fields
            if (!categoryName) {
                results.errors.push(`Row ${rowNum}: KATEGORI kosong untuk "${name}"`);
                continue;
            }

            // Get or create category
            const category = await getOrCreateCategory(categoryName);
            if (!category) {
                results.errors.push(`Row ${rowNum}: Gagal membuat KATEGORI "${categoryName}"`);
                continue;
            }

            if (!baseUnitName) {
                results.errors.push(`Row ${rowNum}: SATUAN1 (satuan dasar) kosong untuk "${name}"`);
                continue;
            }

            // Get or create base unit (will determine later which is actually base)
            const baseUnit = await getOrCreateUnit(baseUnitName);
            if (!baseUnit) {
                results.errors.push(`Row ${rowNum}: Gagal membuat SATUAN1 "${baseUnitName}"`);
                continue;
            }

            // Build units array - collect all units first
            // Then determine base unit (LAST filled unit = base = largest)
            const tempUnits = [];

            // Process all 5 potential units
            for (let i = 1; i <= 5; i++) {
                const unitName = getVal(COL[`SATUAN${i}`])?.toString().trim();
                if (!unitName) continue;

                // Get or create unit
                const unit = await getOrCreateUnit(unitName);
                if (!unit) {
                    results.errors.push(`Row ${rowNum}: Gagal membuat SATUAN${i} "${unitName}"`);
                    continue;
                }

                const conversion = parseFloat(getVal(COL[`KONVERSI${i}`])) || 1;
                const buyPrice = parseFloat(getVal(COL[`HARGABELI${i}`])) || 0;
                const sellPrice = parseFloat(getVal(COL[`HARGAJUAL${i}`])) || 0;

                tempUnits.push({
                    unit_id: unit.id,
                    unit_name: unit.name,
                    conversion_qty: conversion,
                    buy_price: buyPrice,
                    sell_price: sellPrice,
                    original_index: i
                });
            }

            if (tempUnits.length === 0) {
                results.errors.push(`Row ${rowNum}: Tidak ada satuan valid untuk "${name}"`);
                continue;
            }

            // Base unit = FIRST unit (SATUAN1) = smallest unit
            // Keep same order as Excel - SATUAN1 first (base), then SATUAN2, etc.

            // DEDUPLICATION LOGIC:
            // Ensure unique unit_ids. If user puts 'PCS' in both SATUAN1 and SATUAN2, keep SATUAN1.
            const seenUnitIds = new Set();
            const uniqueUnits = [];
            for (const u of tempUnits) {
                if (!seenUnitIds.has(u.unit_id)) {
                    seenUnitIds.add(u.unit_id);
                    uniqueUnits.push(u);
                }
            }

            if (uniqueUnits.length === 0) {
                results.errors.push(`Row ${rowNum}: Tidak ada satuan valid untuk "${name}"`);
                continue;
            }

            // Frontend expects units[0] to be base unit
            const productUnits = [];
            for (let i = 0; i < uniqueUnits.length; i++) {
                const u = uniqueUnits[i];
                productUnits.push({
                    unit_id: u.unit_id,
                    conversion_qty: u.conversion_qty,
                    buy_price: u.buy_price,
                    sell_price: u.sell_price,
                    is_base_unit: i === 0 ? 1 : 0 // First valid unique unit is base
                });
            }

            const baseUnitData = uniqueUnits[0]; // The first one after dedup

            const stock = parseFloat(getVal(COL.STOK)) || 0;
            const minStock = parseFloat(getVal(COL.STOKMIN)) || 5;

            // Parse expiry date - supports YYYY-MM-DD, DD/MM/YYYY, or Excel date number
            let expiryDate = null;
            const expiryVal = getVal(COL.EXPIRY);
            if (expiryVal) {
                if (expiryVal instanceof Date) {
                    // Excel Date object
                    expiryDate = expiryVal.toISOString().split('T')[0];
                } else if (typeof expiryVal === 'number') {
                    // Excel serial date number
                    const excelEpoch = new Date(1899, 11, 30);
                    const jsDate = new Date(excelEpoch.getTime() + expiryVal * 86400000);
                    expiryDate = jsDate.toISOString().split('T')[0];
                } else if (typeof expiryVal === 'string') {
                    const str = expiryVal.trim();
                    // Try DD/MM/YYYY format
                    if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(str)) {
                        const [d, m, y] = str.split('/');
                        expiryDate = `${y}-${m.padStart(2, '0')}-${d.padStart(2, '0')}`;
                    } else if (/^\d{4}-\d{2}-\d{2}$/.test(str)) {
                        // Already YYYY-MM-DD
                        expiryDate = str;
                    }
                }
            }

            const productData = {
                name,
                category_id: category.id,
                category_prefix: category.prefix || category.name.substring(0, 3).toUpperCase(),
                base_unit_id: baseUnitData.unit_id, // SATUAN1 = base
                stock,
                min_stock: minStock,
                expiry_date: expiryDate // null = non-expirable, date = has expiry
            };

            try {
                await saveProduct(req.tenantDb, productData, productUnits, currentYear);
                results.created++;
            } catch (e) {
                results.errors.push(`Row ${rowNum}: Gagal menyimpan "${name}": ${e.message}`);
            }
        }

        res.json({
            success: true,
            message: `Imported ${results.created} products`,
            data: results
        });

    } catch (error) {
        console.error('Import error:', error);
        res.status(500).json({ success: false, message: 'Failed to import: ' + error.message });
    }
});

// Helper function to save product with SKU generation
async function saveProduct(db, product, units, year) {
    // Get next SKU sequence
    const [seqs] = await db.execute(
        'SELECT last_number FROM sku_sequences WHERE category_id = ? AND year = ?',
        [product.category_id, year]
    );

    let nextNumber;
    if (seqs.length === 0) {
        await db.execute(
            'INSERT INTO sku_sequences (category_id, year, last_number) VALUES (?, ?, 1)',
            [product.category_id, year]
        );
        nextNumber = 1;
    } else {
        nextNumber = seqs[0].last_number + 1;
        await db.execute(
            'UPDATE sku_sequences SET last_number = ? WHERE category_id = ? AND year = ?',
            [nextNumber, product.category_id, year]
        );
    }

    const sku = `${product.category_prefix}-${year}-${nextNumber}`;

    // Insert product
    const [result] = await db.execute(`
        INSERT INTO products (name, sku, category_id, base_unit_id, stock, min_stock)
        VALUES (?, ?, ?, ?, ?, ?)
    `, [product.name, sku, product.category_id, product.base_unit_id, product.stock, product.min_stock]);

    const productId = result.insertId;

    // Insert units (Use UPSERT to handle potential Triggers or duplicates)
    for (let i = 0; i < units.length; i++) {
        const unit = units[i];
        await db.execute(`
            INSERT INTO product_units (product_id, unit_id, conversion_qty, buy_price, sell_price, is_base_unit, sort_order)
            VALUES (?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE 
            conversion_qty = VALUES(conversion_qty),
            buy_price = VALUES(buy_price),
            sell_price = VALUES(sell_price),
            is_base_unit = VALUES(is_base_unit),
            sort_order = VALUES(sort_order)
        `, [productId, unit.unit_id, unit.conversion_qty, unit.buy_price, unit.sell_price, unit.is_base_unit, i]);
    }

    // --- INITIAL STOCK LOGIC (AUTO-OPNAME) ---
    // If stock > 0 in Excel, we treat it as "Initial Import" batch.

    // 1. Calculate REAL Stock (Base Unit) based on Largest Unit Logic
    // User says: "Stock in Excel = Qty of Largest Unit".
    // So we find the unit with MAX conversion_qty.
    let maxUnit = units[0];
    for (const u of units) {
        if (u.conversion_qty > maxUnit.conversion_qty) {
            maxUnit = u;
        }
    }

    const rawStock = product.stock; // From Excel
    if (rawStock > 0) {
        const conversionFactor = maxUnit.conversion_qty;
        const realStockBase = rawStock * conversionFactor;

        // Lazy Migration: Ensure tables and columns exist before inserting
        try {
            await db.execute(`
                CREATE TABLE IF NOT EXISTS suppliers (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    name VARCHAR(100) NOT NULL,
                    contact_person VARCHAR(100),
                    phone VARCHAR(20),
                    address TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            `);
        } catch (e) { /* ignore */ }

        try {
            await db.execute(`
                CREATE TABLE IF NOT EXISTS purchases (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    branch_id INT,
                    supplier_id INT,
                    invoice_number VARCHAR(50),
                    date DATE NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    due_date DATE,
                    total_amount DECIMAL(15,2) DEFAULT 0,
                    paid_amount DECIMAL(15,2) DEFAULT 0,
                    payment_status ENUM('paid', 'unpaid', 'partial') DEFAULT 'unpaid',
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            `);
        } catch (e) { /* ignore */ }

        try {
            await db.execute(`
                CREATE TABLE IF NOT EXISTS purchase_items (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    purchase_id INT NOT NULL,
                    product_id INT NOT NULL,
                    quantity DECIMAL(15,4) NOT NULL,
                    unit_price DECIMAL(15,2) NOT NULL,
                    subtotal DECIMAL(15,2) NOT NULL,
                    unit_id INT,
                    expiry_date DATE,
                    current_stock DECIMAL(15,4) DEFAULT NULL,
                    conversion_qty DECIMAL(15,4) DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            `);
        } catch (e) { /* ignore */ }

        // Add current_stock column if table exists but column doesn't
        try {
            await db.execute(`ALTER TABLE purchase_items ADD COLUMN current_stock DECIMAL(15,4) DEFAULT NULL`);
        } catch (e) { /* Column already exists */ }

        // 2. Find or Create "System - Initial Import" Supplier
        let supplierId;
        const [suppliers] = await db.execute("SELECT id FROM suppliers WHERE name = 'System - Initial Import' LIMIT 1");
        if (suppliers.length > 0) {
            supplierId = suppliers[0].id;
        } else {
            const [newSup] = await db.execute("INSERT INTO suppliers (name, address, phone) VALUES (?, ?, ?)", ['System - Initial Import', 'System', '000']);
            supplierId = newSup.insertId;
        }

        // 3. Find or Create Purchase Header "INV-INIT-{YYYY}-{MM}"
        // Only created once per month to group imports? Or one per import batch? 
        // Safer: One per Month per Branch is standard, but here we can reuse one for the whole import session if we passed a session ID.
        // For simplicity: "INV-INIT-{YYYY}-{MM}" is fine.
        const date = new Date().toISOString().split('T')[0];
        const month = year + '-' + new Date().toISOString().split('-')[1]; // YYYY-MM
        const invNumber = `INV-INIT-${month}`;

        let purchaseId;
        // Check if exists for Main Branch (Assume Main Branch for Import)
        // Need to find Main Branch ID
        let branchId;
        const [branches] = await db.execute("SELECT id FROM branches WHERE is_main = 1 LIMIT 1");
        if (branches.length > 0) branchId = branches[0].id;
        else {
            const [anyBranch] = await db.execute("SELECT id FROM branches LIMIT 1");
            if (anyBranch.length > 0) branchId = anyBranch[0].id;
        }

        if (branchId) {
            const [purchases] = await db.execute("SELECT id FROM purchases WHERE invoice_number = ? AND branch_id = ?", [invNumber, branchId]);
            if (purchases.length > 0) {
                purchaseId = purchases[0].id;
            } else {
                const [newPur] = await db.execute(`
                    INSERT INTO purchases (branch_id, supplier_id, invoice_number, date, total_amount, payment_status, notes)
                    VALUES (?, ?, ?, ?, 0, 'paid', 'Auto-generated via Excel Import')
                `, [branchId, supplierId, invNumber, date]);
                purchaseId = newPur.insertId;
            }

            // 4. Create Purchase Item (The Stock Batch)
            // Use RAW STOCK (Excel Value) for quantity, but link to LARGEST UNIT ID
            // current_stock is also RAW VALUE because logic deducts based on unit conversion on the fly? 
            // WAIT - Transaction logic usually deducts from current_stock.
            // If Transaction logic deducts BASE UNIT amount, then current_stock MUST BE BASE UNIT?
            // Let's check transaction logic.
            // Transaction.js: "deductQty = item.quantity * conversion"; "item.current_stock -= deductQty"
            // So current_stock in purchase_items IS IN BASE UNIT?
            // User code in purchases.js: 
            // "INSERT INTO purchase_items (..., quantity, ... current_stock) VALUES (..., item.quantity, ..., item.quantity)"
            // AND "stockQty = item.quantity * conversionQty" -> This goes to branch_stock.
            // So purchase_items.quantity IS IN PURCHASED UNIT.
            // But transaction.js MUST know the unit of the purchase_item to convert?
            // Yes, purchase_items has unit_id.
            // So, for Import:
            // quantity = rawStock (e.g. 1 Dus)
            // unit_id = maxUnit.unit_id (Dus ID)
            // current_stock = rawStock (1 Dus)

            // Wait, does transaction.js convert the SALE quantity to PURCHASE UNIT?
            // Or convert PURCHASE quantity to BASE? 
            // Usually simpler: Store everything in BASE? No, the schema stores unit_id.

            // Standard Saga Logic (Verified):
            // purchase_items.quantity = 1 (Dus)
            // purchase_items.unit_id = DusID
            // purchase_items.current_stock = 1 (Dus)

            // When selling 6 Pcs (0.5 Dus):
            // Transaction Logic needs to handle unit conversion carefully.
            // Assuming transaction logic handles it:

            await db.execute(`
                INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, subtotal, unit_id, current_stock, expiry_date)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            `, [purchaseId, productId, rawStock, maxUnit.buy_price, rawStock * maxUnit.buy_price, maxUnit.unit_id, rawStock, product.expiry_date]);

            // 5. Update Real Stock in Tables
            // Update Products Table (Overwrite the raw value with real base value)
            await db.execute("UPDATE products SET stock = ? WHERE id = ?", [realStockBase, productId]);

            // Update Branch Stock
            const [bStock] = await db.execute("SELECT id FROM branch_stock WHERE branch_id = ? AND product_id = ?", [branchId, productId]);
            if (bStock.length > 0) {
                await db.execute("UPDATE branch_stock SET stock = ? WHERE id = ?", [realStockBase, bStock[0].id]);
            } else {
                await db.execute("INSERT INTO branch_stock (branch_id, product_id, stock) VALUES (?, ?, ?)", [branchId, productId, realStockBase]);
            }
        }
    }

    return productId;
}

/**
 * POST /api/import/suppliers
 * Import suppliers from Excel
 */
router.post('/suppliers', upload.single('file'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ success: false, message: 'No file uploaded' });
        }

        const workbook = new ExcelJS.Workbook();
        await workbook.xlsx.load(req.file.buffer);

        const sheet = workbook.getWorksheet(1);
        if (!sheet) {
            return res.status(400).json({ success: false, message: 'No worksheet found' });
        }

        const results = { created: 0, skipped: 0, errors: [] };

        // Helper function to get cell value safely
        const getCellValue = (cell) => {
            if (!cell || cell.value === null || cell.value === undefined) return '';
            // Handle rich text
            if (typeof cell.value === 'object' && cell.value.richText) {
                return cell.value.richText.map(r => r.text).join('');
            }
            // Handle formula result
            if (typeof cell.value === 'object' && cell.value.result !== undefined) {
                return String(cell.value.result);
            }
            return String(cell.value).trim();
        };

        console.log(`Processing ${sheet.rowCount} rows for suppliers...`);

        // Process rows (skip header row 1)
        for (let rowNum = 2; rowNum <= sheet.rowCount; rowNum++) {
            const row = sheet.getRow(rowNum);

            const name = getCellValue(row.getCell(1));
            const contact_person = getCellValue(row.getCell(2));
            const phone = getCellValue(row.getCell(3));
            const address = getCellValue(row.getCell(4));

            // Skip if name is empty
            if (!name) {
                results.skipped++;
                continue;
            }

            try {
                // Check if already exists
                const [existing] = await req.tenantDb.execute(
                    'SELECT id FROM suppliers WHERE name = ?',
                    [name]
                );

                if (existing.length > 0) {
                    results.skipped++;
                    results.errors.push(`Row ${rowNum}: "${name}" sudah ada`);
                    continue;
                }

                await req.tenantDb.execute(
                    'INSERT INTO suppliers (name, contact_person, phone, address) VALUES (?, ?, ?, ?)',
                    [name, contact_person || null, phone || null, address || null]
                );
                results.created++;
                console.log(`Row ${rowNum}: Imported supplier "${name}"`);
            } catch (e) {
                results.errors.push(`Row ${rowNum}: Gagal menyimpan "${name}": ${e.message}`);
            }
        }

        res.json({
            success: true,
            message: `Imported ${results.created} suppliers, ${results.skipped} skipped`,
            data: results
        });

    } catch (error) {
        console.error('Import error:', error);
        res.status(500).json({ success: false, message: 'Failed to import: ' + error.message });
    }
});

/**
 * POST /api/import/customers
 * Import customers from Excel
 */
router.post('/customers', upload.single('file'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ success: false, message: 'No file uploaded' });
        }

        const workbook = new ExcelJS.Workbook();
        await workbook.xlsx.load(req.file.buffer);

        const sheet = workbook.getWorksheet(1);
        if (!sheet) {
            return res.status(400).json({ success: false, message: 'No worksheet found' });
        }

        const results = { created: 0, skipped: 0, errors: [] };

        // Helper function to get cell value safely
        const getCellValue = (cell) => {
            if (!cell || cell.value === null || cell.value === undefined) return '';
            // Handle rich text
            if (typeof cell.value === 'object' && cell.value.richText) {
                return cell.value.richText.map(r => r.text).join('');
            }
            // Handle formula result
            if (typeof cell.value === 'object' && cell.value.result !== undefined) {
                return String(cell.value.result);
            }
            return String(cell.value).trim();
        };

        console.log(`Processing ${sheet.rowCount} rows...`);

        // Process rows (skip header row 1)
        for (let rowNum = 2; rowNum <= sheet.rowCount; rowNum++) {
            const row = sheet.getRow(rowNum);

            const name = getCellValue(row.getCell(1));
            const email = getCellValue(row.getCell(2));
            const phone = getCellValue(row.getCell(3));
            const address = getCellValue(row.getCell(4));
            const credit_limit = parseFloat(getCellValue(row.getCell(5))) || 0;

            // Skip if name is empty
            if (!name) {
                results.skipped++;
                continue;
            }

            try {
                // Check if already exists
                const [existing] = await req.tenantDb.execute(
                    'SELECT id FROM customers WHERE name = ?',
                    [name]
                );

                if (existing.length > 0) {
                    results.skipped++;
                    results.errors.push(`Row ${rowNum}: "${name}" sudah ada`);
                    continue;
                }

                await req.tenantDb.execute(
                    'INSERT INTO customers (name, email, phone, address, credit_limit) VALUES (?, ?, ?, ?, ?)',
                    [name, email || null, phone || null, address || null, credit_limit]
                );
                results.created++;
                console.log(`Row ${rowNum}: Imported "${name}"`);
            } catch (e) {
                results.errors.push(`Row ${rowNum}: Gagal menyimpan "${name}": ${e.message}`);
            }
        }

        res.json({
            success: true,
            message: `Imported ${results.created} customers, ${results.skipped} skipped`,
            data: results
        });

    } catch (error) {
        console.error('Import error:', error);
        res.status(500).json({ success: false, message: 'Failed to import: ' + error.message });
    }
});

module.exports = router;
