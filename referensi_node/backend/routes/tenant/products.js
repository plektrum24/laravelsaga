const express = require('express');
const router = express.Router();
const { body, validationResult, query } = require('express-validator');

/**
 * GET /api/products/expiry
 * Get products near expiry date
 */
router.get('/expiry', async (req, res) => {
    try {
        const { branch_id, days = 90 } = req.query; // Default check 90 days ahead

        // Lazy Migration: Ensure current_stock column exists
        try {
            await req.tenantDb.execute(`ALTER TABLE purchase_items ADD COLUMN current_stock DECIMAL(10, 2) DEFAULT NULL`);
        } catch (e) {
            // Ignore error if column already exists
        }

        // Query: Join purchase_items (where expiry exists) with products
        // Filter by expiry_date not null AND remaining stock > 0
        // Order by expiry_date ASC (soonest first)
        const [items] = await req.tenantDb.execute(`
            SELECT 
                pi.expiry_date, 
                pi.quantity as batch_initial_qty,
                COALESCE(pi.current_stock, pi.quantity) as batch_remaining_qty,
                DATEDIFF(pi.expiry_date, NOW()) as days_remaining,
                p.name as product_name, 
                p.sku,
                p.stock as global_stock,
                s.name as supplier_name,
                pur.invoice_number,
                pur.date as purchase_date
            FROM purchase_items pi
            JOIN products p ON pi.product_id = p.id
            JOIN purchases pur ON pi.purchase_id = pur.id
            LEFT JOIN suppliers s ON pur.supplier_id = s.id
            WHERE pi.expiry_date IS NOT NULL
            AND (pi.current_stock > 0 OR pi.current_stock IS NULL)
            AND pi.expiry_date <= DATE_ADD(NOW(), INTERVAL ? DAY)
            ORDER BY pi.expiry_date ASC
            LIMIT 100
        `, [days]);

        res.json({
            success: true,
            data: items
        });
    } catch (error) {
        console.error('Get expiry error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get expiry data'
        });
    }
});

// Lazy Migration Helper
async function ensureProductColumns(db) {
    try {
        // 1. Check/Add weight
        const [weightCols] = await db.execute("SHOW COLUMNS FROM product_units LIKE 'weight'");
        if (weightCols.length === 0) {
            console.log('[MIGRATION] Adding weight column to product_units table...');
            await db.execute("ALTER TABLE product_units ADD COLUMN weight DECIMAL(10,2) DEFAULT 0 AFTER sell_price");
        }

        // 2. Check/Add is_base_unit
        const [baseUnitCols] = await db.execute("SHOW COLUMNS FROM product_units LIKE 'is_base_unit'");
        if (baseUnitCols.length === 0) {
            console.log('[MIGRATION] Adding is_base_unit column to product_units table...');
            await db.execute("ALTER TABLE product_units ADD COLUMN is_base_unit BOOLEAN DEFAULT FALSE");
        }

        // 3. Check/Add sort_order
        const [sortCols] = await db.execute("SHOW COLUMNS FROM product_units LIKE 'sort_order'");
        if (sortCols.length === 0) {
            console.log('[MIGRATION] Adding sort_order column to product_units table...');
            await db.execute("ALTER TABLE product_units ADD COLUMN sort_order INT DEFAULT 99");
        }
    } catch (error) {
        console.error('[MIGRATION] Failed to check/add product columns:', error);
    }
}

/**
 * GET /api/products
 * Get all products with optional filters and sorting
 */
router.get('/', async (req, res) => {
    try {
        const { category_id, search, low_stock, page = 1, limit = 50, sort = 'name_asc' } = req.query;
        const offset = (page - 1) * limit;

        // Determine effective branch context: Query Param > User Context
        // Exception: If user is Superadmin (no branch_id) and no query param, show Global.
        const branchId = req.query.branch_id || req.user?.branch_id;

        let sql = `
            SELECT p.*, c.name as category_name, u.name as base_unit_name,
                (SELECT MIN(pu.sell_price) FROM product_units pu WHERE pu.product_id = p.id) as base_price
                ${branchId ? ', COALESCE(bs.stock, 0) as branch_stock, p.stock as global_stock' : ', p.stock as global_stock'}
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            LEFT JOIN units u ON p.base_unit_id = u.id
            ${branchId ? 'LEFT JOIN branch_stock bs ON p.id = bs.product_id AND bs.branch_id = ?' : ''}
            WHERE p.is_active = true
        `;

        const params = [];
        if (branchId) params.push(branchId);

        if (category_id) {
            sql += ' AND p.category_id = ?';
            params.push(category_id);
        }

        if (req.query.supplier_id) {
            // Filter products that have been bought from this supplier and have remaining stock
            sql += ` AND EXISTS (
                SELECT 1 FROM purchase_items pi 
                JOIN purchases pur ON pi.purchase_id = pur.id 
                WHERE pi.product_id = p.id 
                AND pur.supplier_id = ? 
                AND (pi.current_stock > 0 OR pi.current_stock IS NULL)
             )`;
            params.push(req.query.supplier_id);
        }

        if (search) {
            console.log('[DEBUG] Search Query:', search);
            const terms = search.split(' ').filter(t => t.trim() !== '');
            terms.forEach(term => {
                sql += ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
                params.push(`%${term}%`, `%${term}%`, `%${term}%`);
            });
        }

        // Logic for Low Stock Filter
        if (low_stock === 'true') {
            if (branchId) { // Filter based on Branch Stock
                sql += ' AND COALESCE(bs.stock, 0) <= COALESCE(bs.min_stock, p.min_stock)';
            } else { // Filter based on Global Stock
                sql += ' AND p.stock <= p.min_stock';
            }
        }

        // Dynamic sorting
        let orderBy = 'p.name ASC';
        switch (sort) {
            case 'name_asc': orderBy = 'p.name ASC'; break;
            case 'name_desc': orderBy = 'p.name DESC'; break;
            case 'stock_asc': orderBy = branchId ? 'branch_stock ASC' : 'p.stock ASC'; break;
            case 'stock_desc': orderBy = branchId ? 'branch_stock DESC' : 'p.stock DESC'; break;
            case 'price_asc': orderBy = 'base_price ASC'; break;
            case 'price_desc': orderBy = 'base_price DESC'; break;
        }

        sql += ` ORDER BY ${orderBy} LIMIT ? OFFSET ? `;
        params.push(parseInt(limit), offset);

        const [products] = await req.tenantDb.execute(sql, params);

        // Process products: Override 'stock' with 'branch_stock' if in branch context
        const processedProducts = products.map(p => {
            // Set sell_price from base_price for POS compatibility
            const sellPrice = p.base_price || 0;

            if (branchId) {
                return {
                    ...p,
                    stock: p.branch_stock, // UI sees this as "Stock"
                    sell_price: sellPrice,
                    is_local_stock: true
                };
            }
            return {
                ...p,
                sell_price: sellPrice,
                is_local_stock: false
            };
        });

        // Fetch units for each product
        for (let product of processedProducts) {
            const [units] = await req.tenantDb.execute(`
                SELECT pu.*, u.name as unit_name 
                FROM product_units pu 
                JOIN units u ON pu.unit_id = u.id 
                WHERE pu.product_id = ?
    ORDER BY pu.sort_order ASC
            `, [product.id]);
            product.units = units;
            if (product.branch_stock !== undefined) {
                product.stock = parseFloat(product.branch_stock);
            }
            // Set sell_price from base unit if available
            const baseUnit = units.find(u => u.is_base_unit);
            if (baseUnit && baseUnit.sell_price > 0) {
                product.sell_price = parseFloat(baseUnit.sell_price);
            }
            // Set buy_price from base unit for Total Asset calculation
            if (baseUnit && baseUnit.buy_price > 0) {
                product.buy_price = parseFloat(baseUnit.buy_price);
            } else if (!product.buy_price) {
                product.buy_price = 0; // Default to 0 if not set
            }
        }

        // Get total count
        let countSql = 'SELECT COUNT(*) as total FROM products p';
        const countParams = [];

        // Add joins if needed
        if (branchId) {
            countSql += ' LEFT JOIN branch_stock bs ON p.id = bs.product_id AND bs.branch_id = ?';
            countParams.push(branchId);
        }

        countSql += ' WHERE p.is_active = true';

        if (category_id) {
            countSql += ' AND p.category_id = ?';
            countParams.push(category_id);
        }
        if (search) {
            countSql += ' AND (p.name LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
            countParams.push(`%${search}%`, `%${search}%`, `%${search}%`);
        }
        if (low_stock === 'true') {
            if (branchId) {
                countSql += ' AND COALESCE(bs.stock, 0) <= COALESCE(bs.min_stock, p.min_stock)';
            } else {
                countSql += ' AND p.stock <= p.min_stock';
            }
        }

        const [[{ total }]] = await req.tenantDb.execute(countSql, countParams);

        res.json({
            success: true,
            data: {
                products: processedProducts,
                pagination: {
                    page: parseInt(page),
                    limit: parseInt(limit),
                    total,
                    totalPages: Math.ceil(total / limit)
                }
            }
        });
    } catch (error) {
        console.error('Get products error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get products'
        });
    }
});

/**
 * GET /api/products/units
 * Get all available units
 */
router.get('/units', async (req, res) => {
    try {
        const [units] = await req.tenantDb.execute(
            'SELECT * FROM units ORDER BY sort_order ASC'
        );

        res.json({
            success: true,
            data: units
        });
    } catch (error) {
        console.error('Get units error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get units'
        });
    }
});

/**
 * POST /api/products/units
 * Create new unit
 */
router.post('/units', async (req, res) => {
    try {
        const { name } = req.body;
        if (!name) return res.status(400).json({ success: false, message: 'Unit name is required' });

        const [existing] = await req.tenantDb.execute('SELECT id FROM units WHERE name = ?', [name]);
        if (existing.length > 0) {
            return res.status(400).json({ success: false, message: 'Unit already exists' });
        }

        const [result] = await req.tenantDb.execute(
            'INSERT INTO units (name, sort_order) VALUES (?, 99)',
            [name]
        );

        res.json({ success: true, data: { id: result.insertId, name } });
    } catch (error) {
        console.error('Create unit error:', error);
        res.status(500).json({ success: false, message: 'Failed to create unit' });
    }
});

/**
 * GET /api/products/generate-sku/:category_id
 * Generate SKU based on category
 */
router.get('/generate-sku/:category_id', async (req, res) => {
    try {
        const categoryId = req.params.category_id;
        const currentYear = new Date().getFullYear();

        // Get category prefix
        const [categories] = await req.tenantDb.execute(
            'SELECT prefix, name FROM categories WHERE id = ?',
            [categoryId]
        );

        if (categories.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Category not found'
            });
        }

        // Use provided prefix or generate from name (first 2 chars uppercase)
        const prefix = categories[0].prefix || categories[0].name.substring(0, 2).toUpperCase();

        // Get or create sequence
        const [sequences] = await req.tenantDb.execute(
            'SELECT last_number FROM sku_sequences WHERE category_id = ? AND year = ?',
            [categoryId, currentYear]
        );

        let nextNumber;
        if (sequences.length === 0) {
            // Create new sequence for this year
            await req.tenantDb.execute(
                'INSERT INTO sku_sequences (category_id, year, last_number) VALUES (?, ?, 1)',
                [categoryId, currentYear]
            );
            nextNumber = 1;
        } else {
            nextNumber = sequences[0].last_number + 1;
        }

        const sku = `${prefix} -${currentYear} -${nextNumber} `;

        res.json({
            success: true,
            data: { sku, prefix, year: currentYear, number: nextNumber }
        });
    } catch (error) {
        console.error('Generate SKU error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to generate SKU'
        });
    }
});

/**
 * GET /api/products/low-stock
 * Get products with low stock alert
 */
router.get('/low-stock', async (req, res) => {
    try {
        const [products] = await req.tenantDb.execute(`
      SELECT p.*, c.name as category_name 
      FROM products p 
      LEFT JOIN categories c ON p.category_id = c.id 
      WHERE p.stock <= p.min_stock AND p.is_active = true
      ORDER BY p.stock ASC
    `);

        res.json({
            success: true,
            data: products
        });
    } catch (error) {
        console.error('Get low stock error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get low stock products'
        });
    }
});

/**
 * GET /api/products/categories
 * Get all categories with product count
 */
router.get('/categories', async (req, res) => {
    try {
        const [categories] = await req.tenantDb.execute(`
            SELECT c.*,
    (SELECT COUNT(*) FROM products p WHERE p.category_id = c.id AND p.is_active = true) as product_count
            FROM categories c 
            WHERE c.is_active = true 
            ORDER BY c.name
    `);

        res.json({
            success: true,
            data: categories
        });
    } catch (error) {
        console.error('Get categories error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get categories'
        });
    }
});

/**
 * DELETE /api/products/delete-all
 * Delete all products (for data reset)
 */
router.delete('/delete-all', async (req, res) => {
    try {
        // Count products first
        const [[count]] = await req.tenantDb.execute('SELECT COUNT(*) as total FROM products');

        if (count.total === 0) {
            return res.json({
                success: true,
                message: 'No products to delete',
                data: { deleted: 0 }
            });
        }

        // Delete product units first (foreign key)
        await req.tenantDb.execute('DELETE FROM product_units');

        // Delete all related tables with foreign key constraints
        // Order matters: delete children before parents
        try { await req.tenantDb.execute('DELETE FROM transaction_items'); } catch (e) { }
        try { await req.tenantDb.execute('DELETE FROM transactions'); } catch (e) { }
        try { await req.tenantDb.execute('DELETE FROM purchase_items'); } catch (e) { }
        try { await req.tenantDb.execute('DELETE FROM purchases'); } catch (e) { }
        try { await req.tenantDb.execute('DELETE FROM purchase_return_items'); } catch (e) { }
        try { await req.tenantDb.execute('DELETE FROM purchase_returns'); } catch (e) { }
        try { await req.tenantDb.execute('DELETE FROM branch_stock'); } catch (e) { }

        // Delete products
        await req.tenantDb.execute('DELETE FROM products');

        // Reset SKU sequences
        await req.tenantDb.execute('DELETE FROM sku_sequences');

        res.json({
            success: true,
            message: `${count.total} products deleted`,
            data: { deleted: count.total }
        });
    } catch (error) {
        console.error('Delete all products error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete products: ' + error.message
        });
    }
});

/**
 * GET /api/products/:id
 * Get product by ID with units
 */
router.get('/:id', async (req, res) => {
    try {
        const [products] = await req.tenantDb.execute(`
      SELECT p.*, c.name as category_name, u.name as base_unit_name
      FROM products p 
      LEFT JOIN categories c ON p.category_id = c.id 
      LEFT JOIN units u ON p.base_unit_id = u.id
      WHERE p.id = ?
    `, [req.params.id]);

        if (products.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Product not found'
            });
        }

        // Get product units
        const [units] = await req.tenantDb.execute(`
            SELECT pu.*, u.name as unit_name 
            FROM product_units pu 
            JOIN units u ON pu.unit_id = u.id 
            WHERE pu.product_id = ?
    ORDER BY pu.sort_order ASC
        `, [req.params.id]);

        const product = products[0];
        product.units = units;

        res.json({
            success: true,
            data: product
        });
    } catch (error) {
        console.error('Get product error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get product'
        });
    }
});

/**
 * POST /api/products
 * Create new product with units
 */
router.post('/', [
    body('name').notEmpty().withMessage('Product name is required'),
    body('category_id').notEmpty().withMessage('Category is required'),
    body('base_unit_id').notEmpty().withMessage('Base unit is required'),
    body('units').isArray({ min: 1 }).withMessage('At least one unit is required')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { name, category_id, base_unit_id, stock, min_stock, image_url, units, barcode } = req.body;

        await ensureProductColumns(req.tenantDb);

        // Generate SKU
        const currentYear = new Date().getFullYear();
        const [categories] = await req.tenantDb.execute(
            'SELECT prefix, name FROM categories WHERE id = ?',
            [category_id]
        );

        if (categories.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Invalid category'
            });
        }

        const prefix = categories[0].prefix || categories[0].name.substring(0, 2).toUpperCase();

        // Get next sequence number
        const [sequences] = await req.tenantDb.execute(
            'SELECT last_number FROM sku_sequences WHERE category_id = ? AND year = ? FOR UPDATE',
            [category_id, currentYear]
        );

        let nextNumber;
        if (sequences.length === 0) {
            await req.tenantDb.execute(
                'INSERT INTO sku_sequences (category_id, year, last_number) VALUES (?, ?, 1)',
                [category_id, currentYear]
            );
            nextNumber = 1;
        } else {
            nextNumber = sequences[0].last_number + 1;
            await req.tenantDb.execute(
                'UPDATE sku_sequences SET last_number = ? WHERE category_id = ? AND year = ?',
                [nextNumber, category_id, currentYear]
            );
        }

        const sku = `${prefix} -${currentYear} -${nextNumber} `;

        // Insert product
        const [result] = await req.tenantDb.execute(`
      INSERT INTO products(name, sku, category_id, base_unit_id, stock, min_stock, barcode)
VALUES(?, ?, ?, ?, ?, ?, ?)
    `, [name, sku, category_id, base_unit_id, stock || 0, min_stock || 5, barcode || null]);

        const productId = result.insertId;

        // Insert product units
        for (let i = 0; i < units.length; i++) {
            const unit = units[i];
            await req.tenantDb.execute(`
                INSERT INTO product_units(product_id, unit_id, conversion_qty, buy_price, sell_price, weight, is_base_unit, sort_order)
VALUES(?, ?, ?, ?, ?, ?, ?, ?)
            `, [productId, unit.unit_id, unit.conversion_qty || 1, unit.buy_price || 0, unit.sell_price || 0, unit.weight || 0, unit.is_base_unit || false, i]);
        }

        // Add stock to specified branch, or creator's branch, or first branch
        // Priority: 1) request body, 2) user's branch, 3) first branch
        let branchIdToUse = req.body.branch_id || req.user?.branch_id;
        console.log('[DEBUG] Product creation - body.branch_id:', req.body.branch_id, 'user.branch_id:', req.user?.branch_id);

        if (!branchIdToUse) {
            // Get first/main branch if no branch specified
            const [branches] = await req.tenantDb.execute(
                'SELECT id FROM branches WHERE is_active = true ORDER BY is_main DESC, id ASC LIMIT 1'
            );
            if (branches.length > 0) {
                branchIdToUse = branches[0].id;
                console.log('[DEBUG] No branch specified, using first branch:', branchIdToUse);
            }
        }

        console.log('[DEBUG] Final branchIdToUse:', branchIdToUse, 'productId:', productId, 'stock:', stock);

        if (branchIdToUse) {
            await req.tenantDb.execute(`
                INSERT INTO branch_stock (branch_id, product_id, stock, min_stock)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE stock = VALUES(stock)
            `, [branchIdToUse, productId, stock || 0, min_stock || 5]);
            console.log('[DEBUG] Inserted into branch_stock successfully');
        }

        res.status(201).json({
            success: true,
            message: 'Product created successfully',
            data: { id: productId, sku }
        });
    } catch (error) {
        console.error('Create product error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create product'
        });
    }
});

/**
 * PUT /api/products/:id
 * Update product with units
 */
router.put('/:id', async (req, res) => {
    try {
        const { name, category_id, base_unit_id, stock, min_stock, image_url, is_active, units, barcode } = req.body;
        const productId = req.params.id;

        await ensureProductColumns(req.tenantDb);

        const fields = [];
        const values = [];

        if (name !== undefined) { fields.push('name = ?'); values.push(name); }
        if (category_id !== undefined) { fields.push('category_id = ?'); values.push(category_id); }
        if (base_unit_id !== undefined) { fields.push('base_unit_id = ?'); values.push(base_unit_id); }
        if (stock !== undefined) { fields.push('stock = ?'); values.push(stock); }
        if (min_stock !== undefined) { fields.push('min_stock = ?'); values.push(min_stock); }
        if (image_url !== undefined) { fields.push('image_url = ?'); values.push(image_url); }
        if (is_active !== undefined) { fields.push('is_active = ?'); values.push(is_active); }
        if (barcode !== undefined) { fields.push('barcode = ?'); values.push(barcode); }

        if (fields.length > 0) {
            values.push(productId);
            await req.tenantDb.execute(
                `UPDATE products SET ${fields.join(', ')} WHERE id = ? `,
                values
            );

            // If stock is updated, also update branch_stock if branch context exists
            if (stock !== undefined) {
                let branchIdToUse = req.body.branch_id || req.user?.branch_id;
                console.log('[DEBUG] Update product - stock:', stock, 'branch:', branchIdToUse);

                if (branchIdToUse) {
                    await req.tenantDb.execute(`
                        INSERT INTO branch_stock (branch_id, product_id, stock, min_stock)
                        VALUES (?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE stock = VALUES(stock)
                    `, [branchIdToUse, productId, stock, min_stock || 5]);
                    console.log('[DEBUG] Updated branch_stock successfully');
                }
            }
        }

        // Update units if provided
        if (units && Array.isArray(units)) {
            // --- AUDIT PRICE CHANGE: Fetch old prices before delete ---
            const [oldUnits] = await req.tenantDb.execute(
                'SELECT unit_id, sell_price FROM product_units WHERE product_id = ?',
                [productId]
            );
            const oldPriceMap = {};
            oldUnits.forEach(u => oldPriceMap[u.unit_id] = parseFloat(u.sell_price));
            // ----------------------------------------------------------

            // Delete existing units
            await req.tenantDb.execute('DELETE FROM product_units WHERE product_id = ?', [productId]);

            // Insert new units
            for (let i = 0; i < units.length; i++) {
                const unit = units[i];

                // --- AUDIT PRICE CHANGE: Check and log ---
                const newPrice = parseFloat(unit.sell_price || 0);
                const oldPrice = oldPriceMap[unit.unit_id];

                if (oldPrice !== undefined && Math.abs(oldPrice - newPrice) > 0.01) {
                    const userId = req.user ? req.user.id : null;
                    try {
                        await req.tenantDb.execute(`
                            INSERT INTO price_logs (product_id, unit_id, user_id, old_price, new_price)
                            VALUES (?, ?, ?, ?, ?)
                         `, [productId, unit.unit_id, userId, oldPrice, newPrice]);
                    } catch (logErr) {
                        console.error('Failed to insert price log:', logErr);
                    }
                }
                // -----------------------------------------
                await req.tenantDb.execute(`
                    INSERT INTO product_units(product_id, unit_id, conversion_qty, buy_price, sell_price, weight, is_base_unit, sort_order)
VALUES(?, ?, ?, ?, ?, ?, ?, ?)
                `, [productId, unit.unit_id, unit.conversion_qty || 1, unit.buy_price || 0, unit.sell_price || 0, unit.weight || 0, unit.is_base_unit || false, i]);
            }
        }

        res.json({
            success: true,
            message: 'Product updated successfully'
        });
    } catch (error) {
        console.error('Update product error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update product'
        });
    }
});

/**
 * DELETE /api/products/delete-all
 * Delete all products (requires confirmation from frontend)
 */
router.delete('/delete-all', async (req, res) => {
    try {
        // First delete all product_units
        await req.tenantDb.execute('DELETE FROM product_units');

        // Then delete all products
        const [result] = await req.tenantDb.execute('DELETE FROM products');

        // Reset SKU sequences
        await req.tenantDb.execute('UPDATE sku_sequences SET last_number = 0');

        res.json({
            success: true,
            message: `Deleted ${result.affectedRows} products`,
            data: { deleted: result.affectedRows }
        });
    } catch (error) {
        console.error('Delete all products error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete products: ' + error.message
        });
    }
});

/**
 * DELETE /api/products/:id
 * Delete product (soft delete by setting is_active = false)
 */
router.delete('/:id', async (req, res) => {
    try {
        await req.tenantDb.execute(
            'UPDATE products SET is_active = false WHERE id = ?',
            [req.params.id]
        );

        res.json({
            success: true,
            message: 'Product deleted successfully'
        });
    } catch (error) {
        console.error('Delete product error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete product'
        });
    }
});

/**
 * POST /api/products/adjust-stock/:id
 * Adjust stock for a specific product
 */
router.post('/adjust-stock/:id', async (req, res) => {
    try {
        const productId = req.params.id;
        const { type, quantity, reason } = req.body;

        if (!type || !quantity || quantity <= 0) {
            return res.status(400).json({
                success: false,
                message: 'Type and positive quantity are required'
            });
        }

        const branchId = req.query.branch_id || req.body.branch_id || req.user.branch_id;

        let currentStock = 0;
        let newStock = 0;

        if (branchId) {
            // Handle Branch Stock
            const [existing] = await req.tenantDb.execute(
                'SELECT stock FROM branch_stock WHERE branch_id = ? AND product_id = ?',
                [branchId, productId]
            );

            if (existing.length > 0) currentStock = parseFloat(existing[0].stock);

            newStock = type === 'add'
                ? currentStock + parseFloat(quantity)
                : currentStock - parseFloat(quantity);
            newStock = Math.max(0, newStock);

            // Execute Parametrized Query for Branch Stock
            await req.tenantDb.execute(`
                INSERT INTO branch_stock (branch_id, product_id, stock, min_stock)
                VALUES (?, ?, ?, 5)
                ON DUPLICATE KEY UPDATE stock = ?
             `, [branchId, productId, newStock, newStock]);

            // Update Master Stock (Global Aggregation or Just tracking)
            await req.tenantDb.execute(
                'UPDATE products SET stock = stock + ? WHERE id = ?',
                [type === 'add' ? quantity : -quantity, productId]
            );

        } else {
            // Global Stock (Legacy)
            const [products] = await req.tenantDb.execute(
                'SELECT stock FROM products WHERE id = ? AND is_active = true',
                [productId]
            );

            if (products.length === 0) {
                return res.status(404).json({
                    success: false,
                    message: 'Product not found'
                });
            }

            currentStock = parseFloat(products[0].stock) || 0;
            newStock = type === 'add'
                ? currentStock + parseFloat(quantity)
                : currentStock - parseFloat(quantity);
            newStock = Math.max(0, newStock);

            await req.tenantDb.execute(
                'UPDATE products SET stock = ? WHERE id = ?',
                [newStock, productId]
            );
        }

        // --- FIFO / BATCH SYNC LOGIC ---
        // Ensure purchase_items (batches) stay in sync with global stock changes
        if (type === 'subtract') {
            // FIFO Deduction (Logic copied from transactions.js)
            const [batches] = await req.tenantDb.execute(`
                SELECT id, current_stock, quantity 
                FROM purchase_items 
                WHERE product_id = ? AND (current_stock > 0 OR current_stock IS NULL)
                ORDER BY (expiry_date IS NULL), expiry_date ASC, id ASC
            `, [productId]);

            let qtyToDeduct = parseFloat(quantity);

            for (const batch of batches) {
                if (qtyToDeduct <= 0) break;
                const currentBatchStock = batch.current_stock !== null ? parseFloat(batch.current_stock) : parseFloat(batch.quantity);
                const deduction = Math.min(currentBatchStock, qtyToDeduct);
                const batchNewStock = currentBatchStock - deduction;

                await req.tenantDb.execute(
                    'UPDATE purchase_items SET current_stock = ? WHERE id = ?',
                    [batchNewStock, batch.id]
                );
                qtyToDeduct -= deduction;
            }
        } else if (type === 'add') {
            // Create a new "Adjustment" batch so this added stock is trackable
            // We don't have expiry from the Adjust Modal, so we set it to NULL (Non-perishable/Unknown)
            // or we could assume it's a correction of the latest batch. 
            // Ideally: Insert a new record.
            await req.tenantDb.execute(`
                INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, subtotal, unit_id, expiry_date, current_stock) 
                VALUES (NULL, ?, ?, 0, 0, NULL, NULL, ?)
             `, [productId, quantity, quantity]);
        }
        // -------------------------------

        res.json({
            success: true,
            message: `Stock ${type === 'add' ? 'ditambah' : 'dikurangi'} ${quantity}`,
            data: {
                previousStock: currentStock,
                newStock: newStock,
                adjustment: parseFloat(quantity),
                type: type,
                reason: reason || ''
            }
        });
    } catch (error) {
        console.error('Adjust stock error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to adjust stock: ' + error.message
        });
    }
});

/**
 * POST /api/products/reset-all-stock
 * Reset all product stock to 0 (both global and branch stock)
 */
router.post('/reset-all-stock', async (req, res) => {
    try {
        // Reset global product stock
        const [result] = await req.tenantDb.execute(
            'UPDATE products SET stock = 0 WHERE is_active = true'
        );

        // Also reset branch_stock table if it exists
        try {
            await req.tenantDb.execute('UPDATE branch_stock SET stock = 0');
        } catch (e) {
            // Table might not exist, ignore error
            console.log('branch_stock table not updated:', e.message);
        }

        // Reset purchase_items current_stock to 0 (to sync with FIFO logic)
        try {
            await req.tenantDb.execute('UPDATE purchase_items SET current_stock = 0');
        } catch (e) {
            console.log('purchase_items current_stock not updated:', e.message);
        }

        res.json({
            success: true,
            message: `Stock for ${result.affectedRows} products reset to 0`
        });
    } catch (error) {
        console.error('Reset all stock error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to reset stock'
        });
    }
});

/**
 * POST /api/products/categories
 * Create new category with auto-generated prefix
 */
router.post('/categories', [
    body('name').notEmpty().withMessage('Category name is required')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { name, prefix } = req.body;
        const autoPrefix = prefix || name.substring(0, 2).toUpperCase();

        const [result] = await req.tenantDb.execute(
            'INSERT INTO categories (name, prefix) VALUES (?, ?)',
            [name, autoPrefix]
        );

        res.status(201).json({
            success: true,
            message: 'Category created successfully',
            data: { id: result.insertId, prefix: autoPrefix }
        });
    } catch (error) {
        console.error('Create category error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create category'
        });
    }
});

/**
 * PUT /api/products/categories/:id
 * Update category
 */
router.put('/categories/:id', [
    body('name').notEmpty().withMessage('Category name is required')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { name, prefix } = req.body;
        const categoryId = req.params.id;

        // Check if category exists
        const [existing] = await req.tenantDb.execute(
            'SELECT id FROM categories WHERE id = ?',
            [categoryId]
        );

        if (existing.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Category not found'
            });
        }

        // Update category
        if (prefix) {
            await req.tenantDb.execute(
                'UPDATE categories SET name = ?, prefix = ? WHERE id = ?',
                [name, prefix, categoryId]
            );
        } else {
            await req.tenantDb.execute(
                'UPDATE categories SET name = ? WHERE id = ?',
                [name, categoryId]
            );
        }

        res.json({
            success: true,
            message: 'Category updated successfully'
        });
    } catch (error) {
        console.error('Update category error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update category'
        });
    }
});

/**
 * DELETE /api/products/categories/:id
 * Delete category (soft delete - set is_active to false)
 */
router.delete('/categories/:id', async (req, res) => {
    try {
        const categoryId = req.params.id;

        // Check if category has products
        const [[productCount]] = await req.tenantDb.execute(
            'SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = true',
            [categoryId]
        );

        if (productCount.count > 0) {
            return res.status(400).json({
                success: false,
                message: `Cannot delete: ${productCount.count} product(s) still using this category`
            });
        }

        // Soft delete
        await req.tenantDb.execute(
            'UPDATE categories SET is_active = false WHERE id = ?',
            [categoryId]
        );

        res.json({
            success: true,
            message: 'Category deleted successfully'
        });
    } catch (error) {
        console.error('Delete category error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete category'
        });
    }
});

/**
 * GET /api/products/reports/price-logs
 * Get recent price changes
 */
router.get('/reports/price-logs', async (req, res) => {
    try {
        const limit = parseInt(req.query.limit) || 10;

        const [logs] = await req.tenantDb.execute(`
            SELECT pl.*, p.name as product_name, u.name as unit_name, users.username as user_name
            FROM price_logs pl
            JOIN products p ON pl.product_id = p.id
            JOIN units u ON pl.unit_id = u.id
            LEFT JOIN users ON pl.user_id = users.id
            ORDER BY pl.created_at DESC
            LIMIT ?
        `, [limit]);

        res.json({
            success: true,
            data: logs
        });
    } catch (error) {
        console.error('Get price logs error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get price logs'
        });
    }
});

module.exports = router;

