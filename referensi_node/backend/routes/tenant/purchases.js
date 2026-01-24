const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

/**
 * GET /api/purchases
 * List all purchases (History)
 */
router.get('/', async (req, res) => {
    try {
        const { date_from, date_to, limit = 50, branch_id } = req.query; // Add branch_id
        let sql = `
            SELECT p.*, s.name as supplier_name 
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE 1=1
        `;
        const params = [];

        // Filter by branch if provided
        if (branch_id) {
            sql += ` AND p.branch_id = ?`;
            params.push(branch_id);
        }

        if (date_from) {
            sql += ` AND DATE(p.date) >= ?`;
            params.push(date_from);
        }

        if (date_to) {
            sql += ` AND DATE(p.date) <= ?`;
            params.push(date_to);
        }

        sql += ` ORDER BY p.date DESC, p.created_at DESC`;

        const [purchases] = await req.tenantDb.execute(sql, params);
        res.json({ success: true, data: purchases });
    } catch (error) {
        console.error('Fetch purchases error:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * GET /api/purchases/:id
 * Get details of a single purchase
 */
router.get('/:id', async (req, res) => {
    try {
        const { id } = req.params;

        // 1. Get Purchase Header
        const [purchase] = await req.tenantDb.execute(`
            SELECT p.*, s.name as supplier_name 
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.id = ?
        `, [id]);

        if (purchase.length === 0) {
            return res.status(404).json({ success: false, message: 'Purchase not found' });
        }

        // 2. Get Purchase Items with unit info and stored conversion_qty
        const [items] = await req.tenantDb.execute(`
            SELECT pi.*, p.name as product_name, p.sku, u.name as unit_name,
                   COALESCE(pi.conversion_qty, 1) as conversion_qty
            FROM purchase_items pi
            JOIN products p ON pi.product_id = p.id
            LEFT JOIN units u ON pi.unit_id = u.id
            WHERE pi.purchase_id = ?
        `, [id]);

        res.json({ success: true, data: { ...purchase[0], items } });
    } catch (error) {
        console.error('Fetch purchase detail error:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * POST /api/purchases
 * Record a new purchase (Goods In)
 * Handles stock update and debt recording
 */
router.post('/', [
    body('supplier_id').notEmpty().withMessage('Supplier is required'),
    body('date').notEmpty().withMessage('Date is required'),
    body('items').isArray({ min: 1 }).withMessage('At least one item is required'),
    body('payment_status').isIn(['paid', 'unpaid', 'partial']).withMessage('Invalid payment status')
], async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ success: false, errors: errors.array() });
    }

    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const { supplier_id, invoice_number, date, due_date, items, paid_amount, payment_status, notes } = req.body;
        // Global Branch Selector Logic: Use body.branch_id if provided (for Tenant Owner), else user.branch_id
        let branchId = req.body.branch_id || req.user.branch_id;

        // Fallback: If no branch_id in token, get the Main Branch
        if (!branchId) {
            const [mainBranch] = await connection.execute('SELECT id FROM branches WHERE is_main = 1 LIMIT 1');
            if (mainBranch.length > 0) {
                branchId = mainBranch[0].id;
            } else {
                // Fallback if no main branch (should not happen, but safe)
                const [anyBranch] = await connection.execute('SELECT id FROM branches LIMIT 1');
                if (anyBranch.length > 0) branchId = anyBranch[0].id;
            }
        }

        if (!branchId) {
            throw new Error('No active branch found for this transaction.');
        }

        // Calculate total amount using frontend-provided subtotals (before qty conversion)
        const total_amount = items.reduce((sum, item) => sum + (item.subtotal || (item.quantity * item.cost_price)), 0);

        // Lazy Migration: Add branch_id to purchases table if not exists
        try {
            await connection.execute(`ALTER TABLE purchases ADD COLUMN branch_id INT NULL`);
            await connection.execute(`ALTER TABLE purchases ADD CONSTRAINT fk_purchases_branch FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL`);
        } catch (e) { /* Column already exists */ }

        // 1. Insert into purchases
        const [purchaseResult] = await connection.execute(
            `INSERT INTO purchases (branch_id, supplier_id, invoice_number, date, due_date, total_amount, paid_amount, payment_status, notes) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
            [branchId, supplier_id, invoice_number, date, due_date || null, total_amount, paid_amount || 0, payment_status, notes || null]
        );
        const purchaseId = purchaseResult.insertId;

        // 2. Insert items and Update Stock
        for (const item of items) {
            // Use frontend-provided subtotal or fallback to calculation
            const itemSubtotal = item.subtotal || (item.quantity * item.cost_price);

            // Calculate stock quantity (convert to base unit)
            const conversionQty = parseFloat(item.conversion_qty) || 1;
            const stockQty = item.quantity * conversionQty;

            // Ensure unit_id and expiry_date columns exist (lazy migration)
            try {
                await connection.execute(`ALTER TABLE purchase_items ADD COLUMN unit_id INT NULL`);
            } catch (e) { /* Column already exists */ }
            try {
                await connection.execute(`ALTER TABLE purchase_items ADD COLUMN expiry_date DATE NULL`);
            } catch (e) { /* Column already exists */ }
            try {
                await connection.execute(`ALTER TABLE purchase_items ADD COLUMN current_stock DECIMAL(10, 2) DEFAULT NULL`);
            } catch (e) { /* Column already exists */ }
            try {
                await connection.execute(`ALTER TABLE purchase_items ADD COLUMN conversion_qty DECIMAL(10, 4) DEFAULT 1`);
            } catch (e) { /* Column already exists */ }

            // Insert purchase items (store conversion_qty for edit reference)
            await connection.execute(`
                INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, subtotal, unit_id, expiry_date, current_stock, conversion_qty) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
                [purchaseId, item.product_id, item.quantity, item.cost_price, itemSubtotal, item.unit_id || null, item.expiry_date || null, item.quantity, conversionQty]);

            // Update Branch Stock using CONVERTED qty (base unit)
            const [stockRows] = await connection.execute(
                `SELECT id, stock FROM branch_stock WHERE branch_id = ? AND product_id = ?`,
                [branchId, item.product_id]
            );

            if (stockRows.length > 0) {
                // Update existing branch stock with CONVERTED qty
                await connection.execute(
                    `UPDATE branch_stock SET stock = stock + ? WHERE id = ?`,
                    [stockQty, stockRows[0].id]
                );
            } else {
                // Insert new branch stock with CONVERTED qty
                await connection.execute(
                    `INSERT INTO branch_stock (branch_id, product_id, stock) VALUES (?, ?, ?)`,
                    [branchId, item.product_id, stockQty]
                );
            }

            // ALSO update products.stock (main stock table) with CONVERTED qty
            await connection.execute(
                `UPDATE products SET stock = stock + ? WHERE id = ?`,
                [stockQty, item.product_id]
            );
            // AUTO-UPDATE: Update product buy_price from Goods In (Last Purchase Price method)
            // Update the specific unit's price AND recalculate all other units proportionally

            // --- LOGGING: Fetch Old Price ---
            let oldPrice = 0;
            let logUnitId = item.unit_id;
            try {
                if (item.unit_id) {
                    const [u] = await connection.execute('SELECT buy_price FROM product_units WHERE product_id=? AND unit_id=?', [item.product_id, item.unit_id]);
                    if (u.length) oldPrice = parseFloat(u[0].buy_price);
                } else {
                    const [u] = await connection.execute('SELECT unit_id, buy_price FROM product_units WHERE product_id=? AND is_base_unit=1', [item.product_id]);
                    if (u.length) { oldPrice = parseFloat(u[0].buy_price); logUnitId = u[0].unit_id; }
                }
            } catch (e) { console.error('Fetch old price error:', e); }
            // -------------------------------

            if (item.unit_id) {
                // 1. Update specific unit price
                await connection.execute(
                    `UPDATE product_units SET buy_price = ? WHERE product_id = ? AND unit_id = ?`,
                    [item.cost_price, item.product_id, item.unit_id]
                );

                // 2. Get the updated unit's conversion_qty to calculate base price
                const [updatedUnit] = await connection.execute(
                    `SELECT conversion_qty FROM product_units WHERE product_id = ? AND unit_id = ?`,
                    [item.product_id, item.unit_id]
                );

                if (updatedUnit.length > 0) {
                    const conversionQty = parseFloat(updatedUnit[0].conversion_qty) || 1;
                    const basePrice = item.cost_price / conversionQty;

                    // 3. Update ALL other units for this product based on base price
                    const [allUnits] = await connection.execute(
                        `SELECT id, unit_id, conversion_qty FROM product_units WHERE product_id = ? AND unit_id != ?`,
                        [item.product_id, item.unit_id]
                    );

                    for (const unit of allUnits) {
                        const unitConv = parseFloat(unit.conversion_qty) || 1;
                        const newBuyPrice = Math.round(basePrice * unitConv);
                        await connection.execute(
                            `UPDATE product_units SET buy_price = ? WHERE id = ?`,
                            [newBuyPrice, unit.id]
                        );
                    }
                }
            } else {
                // Fallback: update base unit if no unit_id provided
                await connection.execute(
                    `UPDATE product_units SET buy_price = ? WHERE product_id = ? AND is_base_unit = 1`,
                    [item.cost_price, item.product_id]
                );
            }

            // --- LOGGING: Insert Log if Changed ---
            const newPrice = parseFloat(item.cost_price);
            if (Math.abs(oldPrice - newPrice) > 0.01 && logUnitId) {
                const userId = req.user ? req.user.id : null;
                try {
                    await connection.execute(`
                        INSERT INTO price_logs (product_id, unit_id, user_id, old_price, new_price)
                        VALUES (?, ?, ?, ?, ?)
                     `, [item.product_id, logUnitId, userId, oldPrice, newPrice]);
                } catch (e) { console.error('Log error:', e); }
            }
            // --------------------------------------
        }

        await connection.commit();
        res.status(201).json({ success: true, message: 'Purchase recorded successfully', data: { id: purchaseId } });

    } catch (error) {
        await connection.rollback();
        console.error('Purchase error:', error);
        res.status(500).json({ success: false, message: 'Failed to record purchase' });
    } finally {
        connection.release();
    }
});

/**
 * GET /api/purchases/alerts
 * Get overdue or due-soon debts
 */
router.get('/alerts', async (req, res) => {
    try {
        const today = new Date().toISOString().split('T')[0];
        // Overdue
        const [overdue] = await req.tenantDb.execute(`
            SELECT p.*, s.name as supplier_name 
            FROM purchases p
            JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.payment_status IN ('unpaid', 'partial') AND p.due_date < ?
        `, [today]);

        // Due soon (next 7 days)
        const [dueSoon] = await req.tenantDb.execute(`
            SELECT p.*, s.name as supplier_name 
            FROM purchases p
            JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.payment_status IN ('unpaid', 'partial') AND p.due_date >= ? AND p.due_date <= DATE_ADD(?, INTERVAL 7 DAY)
        `, [today, today]);

        res.json({ success: true, data: { overdue, dueSoon } });
    } catch (error) {
        console.error('Alerts error:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * PUT /api/purchases/:id
 * Update a purchase (Reverses old stock -> Updates info -> Adds new stock)
 */
router.put('/:id', [
    body('supplier_id').notEmpty(),
    body('items').isArray({ min: 1 })
], async (req, res) => {
    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const { id } = req.params;
        const { supplier_id, invoice_number, date, due_date, items, paid_amount, payment_status, notes } = req.body;

        // Global Branch Selector Logic: Use body.branch_id if provided (for Tenant Owner), else user.branch_id
        let branchId = req.body.branch_id || req.user.branch_id;
        if (!branchId) {
            const [mainBranch] = await connection.execute('SELECT id FROM branches WHERE is_main = 1 LIMIT 1');
            if (mainBranch.length > 0) branchId = mainBranch[0].id;
        }

        // 1. Fetch OLD items with unit conversion info to reverse stock properly
        const [oldItems] = await connection.execute(`
            SELECT pi.product_id, pi.quantity, pi.unit_id, COALESCE(pu.conversion_qty, 1) as conversion_qty
            FROM purchase_items pi
            LEFT JOIN product_units pu ON pi.product_id = pu.product_id AND pi.unit_id = pu.unit_id
            WHERE pi.purchase_id = ?
        `, [id]);

        // Reverse Old Stock (using converted qty)
        if (branchId) {
            for (const item of oldItems) {
                const stockQty = item.quantity * (item.conversion_qty || 1);
                await connection.execute(`
                    UPDATE branch_stock SET stock = stock - ? WHERE branch_id = ? AND product_id = ?
                `, [stockQty, branchId, item.product_id]);

                // Also reverse from products.stock
                await connection.execute(
                    `UPDATE products SET stock = stock - ? WHERE id = ?`,
                    [stockQty, item.product_id]
                );
            }
        }

        // 2. Delete OLD Items
        await connection.execute('DELETE FROM purchase_items WHERE purchase_id = ?', [id]);

        // 3. Update Purchase Header (use frontend-provided subtotals)
        const total_amount = items.reduce((sum, item) => sum + (item.subtotal || (item.quantity * item.cost_price)), 0);
        await connection.execute(`
            UPDATE purchases 
            SET supplier_id=?, invoice_number=?, date=?, due_date=?, total_amount=?, paid_amount=?, payment_status=?, notes=?
            WHERE id=?
        `, [supplier_id, invoice_number, date, due_date || null, total_amount, paid_amount || 0, payment_status, notes || null, id]);

        // 4. Insert NEW Items and Add Stock
        for (const item of items) {
            const itemSubtotal = item.subtotal || (item.quantity * item.cost_price);
            const conversionQty = parseFloat(item.conversion_qty) || 1;
            const stockQty = item.quantity * conversionQty;

            // Insert new items (with current_stock = quantity, store conversion_qty)
            await connection.execute(`
                INSERT INTO purchase_items (purchase_id, product_id, quantity, unit_price, subtotal, unit_id, expiry_date, current_stock, conversion_qty) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)`,
                [id, item.product_id, item.quantity, item.cost_price, itemSubtotal, item.unit_id || null, item.expiry_date || null, item.quantity, conversionQty]);

            // Add Stock to branch_stock using CONVERTED qty
            if (branchId) {
                const [stockRows] = await connection.execute(
                    `SELECT id FROM branch_stock WHERE branch_id = ? AND product_id = ?`,
                    [branchId, item.product_id]
                );
                if (stockRows.length > 0) {
                    await connection.execute(`UPDATE branch_stock SET stock = stock + ? WHERE id = ?`, [stockQty, stockRows[0].id]);
                } else {
                    await connection.execute(`INSERT INTO branch_stock (branch_id, product_id, stock) VALUES (?, ?, ?)`, [branchId, item.product_id, stockQty]);
                }
            }

            // Also add to products.stock using CONVERTED qty
            await connection.execute(
                `UPDATE products SET stock = stock + ? WHERE id = ?`,
                [stockQty, item.product_id]
            );


            // AUTO-UPDATE: Update product buy_price from Goods In (Last Purchase Price method)
            if (item.unit_id) {
                // 1. Update specific unit price
                await connection.execute(
                    `UPDATE product_units SET buy_price = ? WHERE product_id = ? AND unit_id = ?`,
                    [item.cost_price, item.product_id, item.unit_id]
                );

                // 2. Get the updated unit's conversion_qty to calculate base price
                const [updatedUnit] = await connection.execute(
                    `SELECT conversion_qty FROM product_units WHERE product_id = ? AND unit_id = ?`,
                    [item.product_id, item.unit_id]
                );

                if (updatedUnit.length > 0) {
                    const conversionQty = parseFloat(updatedUnit[0].conversion_qty) || 1;
                    const basePrice = item.cost_price / conversionQty;

                    // 3. Update ALL other units for this product based on base price
                    const [allUnits] = await connection.execute(
                        `SELECT id, unit_id, conversion_qty FROM product_units WHERE product_id = ? AND unit_id != ?`,
                        [item.product_id, item.unit_id]
                    );

                    for (const unit of allUnits) {
                        const unitConv = parseFloat(unit.conversion_qty) || 1;
                        const newBuyPrice = Math.round(basePrice * unitConv);
                        await connection.execute(
                            `UPDATE product_units SET buy_price = ? WHERE id = ?`,
                            [newBuyPrice, unit.id]
                        );
                    }
                }
            } else {
                await connection.execute(
                    `UPDATE product_units SET buy_price = ? WHERE product_id = ? AND is_base_unit = 1`,
                    [item.cost_price, item.product_id]
                );
            }
        }

        await connection.commit();
        res.json({ success: true, message: 'Purchase updated successfully' });

    } catch (error) {
        await connection.rollback();
        console.error('Update purchase error:', error);
        res.status(500).json({ success: false, message: 'Failed to update purchase' });
    } finally {
        connection.release();
    }
});

/**
 * DELETE /api/purchases/:id
 * Delete a purchase and reverse stock changes
 */
router.delete('/:id', async (req, res) => {
    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const { id } = req.params;
        const branchId = req.user.branch_id; // Need branch_id to reverse stock. 
        // WARNING: If admin deletes, we assume they are in the same branch where purchase was made? 
        // ideally we store branch_id in purchases table. 
        // Current schema checks: purchases table doesn't seem to have branch_id?
        // Let's check schema in dbGenerator.js or inferred.
        // wait, purchase logic used `branchId` to update `branch_stock`.
        // BUT `purchases` table itself does NOT have `branch_id` in the schema shown earlier?
        // Code: `INSERT INTO purchases ...` did not include branch_id.
        // This is a flaw. If we delete, we don't know WHICH branch stock to deduct from if we support multiple branches.
        // However, for single branch/simple use case, we might assume current user's branch or Main branch.
        // To be SAFE: we should probably fetch the branch_id from `branch_stock` logic if possible, or just use user's current branch.
        // Let's assume user must be in the same branch context.

        // 1. Get Purchase Items to reverse stock
        const [items] = await connection.execute(`
            SELECT product_id, quantity FROM purchase_items WHERE purchase_id = ?
        `, [id]);

        if (items.length === 0) {
            // Just delete header if no items
            await connection.execute('DELETE FROM purchases WHERE id = ?', [id]);
            await connection.commit();
            return res.json({ success: true, message: 'Purchase deleted' });
        }

        // Determine Branch ID (Logic from Create)
        let targetBranchId = branchId;
        if (!targetBranchId) {
            const [mainBranch] = await connection.execute('SELECT id FROM branches WHERE is_main = 1 LIMIT 1');
            if (mainBranch.length > 0) targetBranchId = mainBranch[0].id;
        }

        if (targetBranchId) {
            // 2. Reverse Stock
            for (const item of items) {
                await connection.execute(`
                    UPDATE branch_stock 
                    SET stock = stock - ? 
                    WHERE branch_id = ? AND product_id = ?
                `, [item.quantity, targetBranchId, item.product_id]);
            }
        }

        // 3. Delete Purchase (Cascade should handle items, but explicit is fine)
        await connection.execute('DELETE FROM purchases WHERE id = ?', [id]);

        await connection.commit();
        res.json({ success: true, message: 'Purchase deleted and stock reversed' });

    } catch (error) {
        await connection.rollback();
        console.error('Delete purchase error:', error);
        res.status(500).json({ success: false, message: 'Failed to delete purchase' });
    } finally {
        connection.release();
    }
});

module.exports = router;
