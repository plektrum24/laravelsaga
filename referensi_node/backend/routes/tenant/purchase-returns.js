const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

/**
 * Lazy Migration: Ensure purchase_returns tables exist
 */
async function ensureReturnTables(db) {
    try {
        await db.execute(`
            CREATE TABLE IF NOT EXISTS purchase_returns (
                id INT PRIMARY KEY AUTO_INCREMENT,
                branch_id INT,
                supplier_id INT NOT NULL,
                return_number VARCHAR(50) NOT NULL,
                date DATE NOT NULL,
                total_amount DECIMAL(15,2) DEFAULT 0,
                status ENUM('draft', 'completed', 'cancelled') DEFAULT 'draft',
                reason ENUM('expired', 'damaged', 'wrong_item', 'quality_issue', 'other') DEFAULT 'other',
                notes TEXT,
                created_by INT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        `);
        await db.execute(`
            CREATE TABLE IF NOT EXISTS purchase_return_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                return_id INT NOT NULL,
                purchase_item_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity DECIMAL(15,4) NOT NULL,
                unit_id INT,
                unit_price DECIMAL(15,2) NOT NULL,
                subtotal DECIMAL(15,2) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        `);
    } catch (e) {
        console.error('[MIGRATION] purchase_returns tables error:', e.message);
    }
}

/**
 * Generate return number
 */
function generateReturnNumber() {
    const date = new Date();
    const dateStr = date.toISOString().slice(0, 10).replace(/-/g, '');
    const random = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
    return `RET-${dateStr}-${random}`;
}

/**
 * GET /api/purchase-returns
 * List all purchase returns with filters
 */
router.get('/', async (req, res) => {
    try {
        await ensureReturnTables(req.tenantDb);

        const { supplier_id, status, date_from, date_to, limit = 50 } = req.query;

        let sql = `
            SELECT pr.*, s.name as supplier_name, b.name as branch_name
            FROM purchase_returns pr
            LEFT JOIN suppliers s ON pr.supplier_id = s.id
            LEFT JOIN branches b ON pr.branch_id = b.id
            WHERE 1=1
        `;
        const params = [];

        if (supplier_id) {
            sql += ` AND pr.supplier_id = ?`;
            params.push(supplier_id);
        }
        if (status) {
            sql += ` AND pr.status = ?`;
            params.push(status);
        }
        if (date_from) {
            sql += ` AND DATE(pr.date) >= ?`;
            params.push(date_from);
        }
        if (date_to) {
            sql += ` AND DATE(pr.date) <= ?`;
            params.push(date_to);
        }

        sql += ` ORDER BY pr.date DESC, pr.created_at DESC LIMIT ?`;
        params.push(parseInt(limit));

        const [returns] = await req.tenantDb.execute(sql, params);
        res.json({ success: true, data: returns });
    } catch (error) {
        console.error('Get purchase returns error:', error);
        res.status(500).json({ success: false, message: 'Failed to get purchase returns' });
    }
});

/**
 * GET /api/purchase-returns/batches/:productId
 * Get available batches for return (filtered by supplier if provided)
 */
router.get('/batches/:productId', async (req, res) => {
    try {
        await ensureReturnTables(req.tenantDb);

        const { productId } = req.params;
        const { supplier_id } = req.query;
        console.log(`[DEBUG] Get Batches. ProductID: ${productId} (${typeof productId}), SupplierID: ${supplier_id} (${typeof supplier_id})`);

        let sql = `
            SELECT 
                pi.id as batch_id,
                pi.purchase_id,
                pi.product_id,
                pi.quantity as initial_qty,
                COALESCE(pi.current_stock, pi.quantity) as current_stock,
                pi.unit_price,
                pi.unit_id,
                pi.expiry_date,
                pi.created_at as batch_date,
                p.name as product_name,
                p.sku,
                pur.invoice_number,
                pur.date as purchase_date,
                pur.supplier_id,
                s.name as supplier_name,
                u.name as unit_name
            FROM purchase_items pi
            JOIN products p ON pi.product_id = p.id
            JOIN purchases pur ON pi.purchase_id = pur.id
            LEFT JOIN suppliers s ON pur.supplier_id = s.id
            LEFT JOIN units u ON pi.unit_id = u.id
            WHERE pi.product_id = ?
            AND (pi.current_stock > 0 OR pi.current_stock IS NULL)
        `;
        const params = [productId];

        if (supplier_id) {
            sql += ` AND pur.supplier_id = ?`;
            params.push(supplier_id);
        }

        sql += ` ORDER BY pi.expiry_date ASC, pi.id ASC`;

        const [batches] = await req.tenantDb.execute(sql, params);
        res.json({
            success: true,
            data: batches,
            debug_meta: {
                received_productId: productId,
                received_supplierId: supplier_id,
                generated_sql: sql,
                sql_params: params
            }
        });
    } catch (error) {
        console.error('Get batches for return error:', error);
        res.status(500).json({ success: false, message: 'Failed to get batches' });
    }
});

/**
 * GET /api/purchase-returns/suppliers-with-batches
 * Get suppliers that have returnable batches
 */
router.get('/suppliers-with-batches', async (req, res) => {
    try {
        await ensureReturnTables(req.tenantDb);

        const [suppliers] = await req.tenantDb.execute(`
            SELECT DISTINCT s.id, s.name, s.phone, s.address,
                (SELECT COUNT(*) FROM purchase_items pi2 
                 JOIN purchases pur2 ON pi2.purchase_id = pur2.id 
                 WHERE pur2.supplier_id = s.id 
                 AND (pi2.current_stock > 0 OR pi2.current_stock IS NULL)) as batch_count
            FROM suppliers s
            JOIN purchases pur ON pur.supplier_id = s.id
            JOIN purchase_items pi ON pi.purchase_id = pur.id
            WHERE (pi.current_stock > 0 OR pi.current_stock IS NULL)
            ORDER BY s.name
        `);

        res.json({ success: true, data: suppliers });
    } catch (error) {
        console.error('Get suppliers with batches error:', error);
        res.status(500).json({ success: false, message: 'Failed to get suppliers' });
    }
});

/**
 * GET /api/purchase-returns/:id
 * Get single return with items
 */
router.get('/:id', async (req, res) => {
    try {
        await ensureReturnTables(req.tenantDb);

        const [returns] = await req.tenantDb.execute(`
            SELECT pr.*, s.name as supplier_name, b.name as branch_name
            FROM purchase_returns pr
            LEFT JOIN suppliers s ON pr.supplier_id = s.id
            LEFT JOIN branches b ON pr.branch_id = b.id
            WHERE pr.id = ?
        `, [req.params.id]);

        if (returns.length === 0) {
            return res.status(404).json({ success: false, message: 'Return not found' });
        }

        const [items] = await req.tenantDb.execute(`
            SELECT pri.*, 
                   p.name as product_name, 
                   p.sku,
                   u.name as unit_name,
                   pi.expiry_date,
                   pur.invoice_number as original_invoice
            FROM purchase_return_items pri
            JOIN products p ON pri.product_id = p.id
            LEFT JOIN units u ON pri.unit_id = u.id
            LEFT JOIN purchase_items pi ON pri.purchase_item_id = pi.id
            LEFT JOIN purchases pur ON pi.purchase_id = pur.id
            WHERE pri.return_id = ?
        `, [req.params.id]);

        res.json({ success: true, data: { ...returns[0], items } });
    } catch (error) {
        console.error('Get purchase return error:', error);
        res.status(500).json({ success: false, message: 'Failed to get purchase return' });
    }
});

/**
 * POST /api/purchase-returns
 * Create new purchase return
 */
router.post('/', [
    body('supplier_id').notEmpty().withMessage('Supplier is required'),
    body('date').notEmpty().withMessage('Date is required'),
    body('items').isArray({ min: 1 }).withMessage('At least one item is required')
], async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ success: false, errors: errors.array() });
    }

    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        await ensureReturnTables(connection);

        const { supplier_id, date, items, reason, notes, status = 'draft' } = req.body;
        const branchId = req.body.branch_id || req.user?.branch_id || null;
        const createdBy = req.user?.id || null;
        const returnNumber = generateReturnNumber();

        // Calculate total amount
        const totalAmount = items.reduce((sum, item) => sum + (item.subtotal || (item.quantity * item.unit_price)), 0);

        // Insert return header
        const [result] = await connection.execute(`
            INSERT INTO purchase_returns (branch_id, supplier_id, return_number, date, total_amount, status, reason, notes, created_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        `, [branchId, supplier_id, returnNumber, date, totalAmount, status, reason || 'other', notes || null, createdBy]);

        const returnId = result.insertId;

        // Insert return items
        for (const item of items) {
            const itemSubtotal = item.subtotal || (item.quantity * item.unit_price);

            await connection.execute(`
                INSERT INTO purchase_return_items (return_id, purchase_item_id, product_id, quantity, unit_id, unit_price, subtotal)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            `, [returnId, item.purchase_item_id, item.product_id, item.quantity, item.unit_id || null, item.unit_price, itemSubtotal]);
        }

        // If status is 'completed', process stock deduction immediately
        if (status === 'completed') {
            await processReturnCompletion(connection, returnId, items, branchId);
        }

        await connection.commit();
        res.status(201).json({
            success: true,
            message: 'Purchase return created successfully',
            data: { id: returnId, return_number: returnNumber }
        });

    } catch (error) {
        await connection.rollback();
        console.error('Create purchase return error:', error);
        res.status(500).json({ success: false, message: 'Failed to create purchase return: ' + error.message });
    } finally {
        connection.release();
    }
});

/**
 * PATCH /api/purchase-returns/:id/complete
 * Complete a draft return (deduct stock)
 */
router.patch('/:id/complete', async (req, res) => {
    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const returnId = req.params.id;

        // Get return and verify status
        const [returns] = await connection.execute(
            'SELECT * FROM purchase_returns WHERE id = ?',
            [returnId]
        );

        if (returns.length === 0) {
            await connection.rollback();
            return res.status(404).json({ success: false, message: 'Return not found' });
        }

        if (returns[0].status !== 'draft') {
            await connection.rollback();
            return res.status(400).json({ success: false, message: 'Can only complete draft returns' });
        }

        const branchId = returns[0].branch_id;

        // Get return items
        const [items] = await connection.execute(
            'SELECT * FROM purchase_return_items WHERE return_id = ?',
            [returnId]
        );

        // Process stock deduction
        await processReturnCompletion(connection, returnId, items, branchId);

        // Update status
        await connection.execute(
            'UPDATE purchase_returns SET status = "completed", updated_at = NOW() WHERE id = ?',
            [returnId]
        );

        await connection.commit();
        res.json({ success: true, message: 'Return completed successfully' });

    } catch (error) {
        await connection.rollback();
        console.error('Complete return error:', error);
        res.status(500).json({ success: false, message: 'Failed to complete return: ' + error.message });
    } finally {
        connection.release();
    }
});

/**
 * Helper: Process return completion (deduct stock from all places)
 */
async function processReturnCompletion(connection, returnId, items, branchId) {
    for (const item of items) {
        const qty = parseFloat(item.quantity);

        // 1. Get current batch stock and conversion info
        const [batch] = await connection.execute(
            'SELECT pi.*, COALESCE(pu.conversion_qty, 1) as conversion_qty FROM purchase_items pi LEFT JOIN product_units pu ON pi.product_id = pu.product_id AND pi.unit_id = pu.unit_id WHERE pi.id = ?',
            [item.purchase_item_id]
        );

        if (batch.length === 0) {
            throw new Error(`Batch ${item.purchase_item_id} not found`);
        }

        const batchData = batch[0];
        const currentStock = batchData.current_stock !== null ? parseFloat(batchData.current_stock) : parseFloat(batchData.quantity);

        if (qty > currentStock) {
            throw new Error(`Return quantity (${qty}) exceeds batch stock (${currentStock})`);
        }

        // Calculate base unit qty for products.stock and branch_stock
        const conversionQty = parseFloat(batchData.conversion_qty) || 1;
        const baseUnitQty = qty * conversionQty;

        // 2. Deduct from purchase_items.current_stock
        await connection.execute(
            'UPDATE purchase_items SET current_stock = current_stock - ? WHERE id = ?',
            [qty, item.purchase_item_id]
        );

        // 3. Deduct from products.stock
        await connection.execute(
            'UPDATE products SET stock = stock - ? WHERE id = ?',
            [baseUnitQty, item.product_id]
        );

        // 4. Deduct from branch_stock if applicable
        if (branchId) {
            await connection.execute(
                'UPDATE branch_stock SET stock = stock - ? WHERE branch_id = ? AND product_id = ?',
                [baseUnitQty, branchId, item.product_id]
            );
        }
    }
}

/**
 * PATCH /api/purchase-returns/:id/cancel
 * Cancel a return (restore stock if was completed)
 */
router.patch('/:id/cancel', async (req, res) => {
    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const returnId = req.params.id;

        // Get return
        const [returns] = await connection.execute(
            'SELECT * FROM purchase_returns WHERE id = ?',
            [returnId]
        );

        if (returns.length === 0) {
            await connection.rollback();
            return res.status(404).json({ success: false, message: 'Return not found' });
        }

        if (returns[0].status === 'cancelled') {
            await connection.rollback();
            return res.status(400).json({ success: false, message: 'Return already cancelled' });
        }

        const wasCompleted = returns[0].status === 'completed';
        const branchId = returns[0].branch_id;

        // If was completed, restore stock
        if (wasCompleted) {
            const [items] = await connection.execute(
                'SELECT * FROM purchase_return_items WHERE return_id = ?',
                [returnId]
            );

            for (const item of items) {
                const qty = parseFloat(item.quantity);

                // Get conversion info
                const [batch] = await connection.execute(
                    'SELECT pi.*, COALESCE(pu.conversion_qty, 1) as conversion_qty FROM purchase_items pi LEFT JOIN product_units pu ON pi.product_id = pu.product_id AND pi.unit_id = pu.unit_id WHERE pi.id = ?',
                    [item.purchase_item_id]
                );

                const conversionQty = batch.length > 0 ? (parseFloat(batch[0].conversion_qty) || 1) : 1;
                const baseUnitQty = qty * conversionQty;

                // Restore to purchase_items.current_stock
                await connection.execute(
                    'UPDATE purchase_items SET current_stock = current_stock + ? WHERE id = ?',
                    [qty, item.purchase_item_id]
                );

                // Restore to products.stock
                await connection.execute(
                    'UPDATE products SET stock = stock + ? WHERE id = ?',
                    [baseUnitQty, item.product_id]
                );

                // Restore to branch_stock if applicable
                if (branchId) {
                    await connection.execute(
                        'UPDATE branch_stock SET stock = stock + ? WHERE branch_id = ? AND product_id = ?',
                        [baseUnitQty, branchId, item.product_id]
                    );
                }
            }
        }

        // Update status
        await connection.execute(
            'UPDATE purchase_returns SET status = "cancelled", updated_at = NOW() WHERE id = ?',
            [returnId]
        );

        await connection.commit();
        res.json({ success: true, message: 'Return cancelled successfully' });

    } catch (error) {
        await connection.rollback();
        console.error('Cancel return error:', error);
        res.status(500).json({ success: false, message: 'Failed to cancel return: ' + error.message });
    } finally {
        connection.release();
    }
});

/**
 * DELETE /api/purchase-returns/:id
 * Delete a draft return
 */
router.delete('/:id', async (req, res) => {
    try {
        const [returns] = await req.tenantDb.execute(
            'SELECT status FROM purchase_returns WHERE id = ?',
            [req.params.id]
        );

        if (returns.length === 0) {
            return res.status(404).json({ success: false, message: 'Return not found' });
        }

        if (returns[0].status !== 'draft') {
            return res.status(400).json({ success: false, message: 'Can only delete draft returns' });
        }

        await req.tenantDb.execute('DELETE FROM purchase_returns WHERE id = ?', [req.params.id]);

        res.json({ success: true, message: 'Return deleted successfully' });
    } catch (error) {
        console.error('Delete return error:', error);
        res.status(500).json({ success: false, message: 'Failed to delete return' });
    }
});

module.exports = router;
