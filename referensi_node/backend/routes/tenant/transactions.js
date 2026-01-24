const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

/**
 * Generate invoice number
 */
const generateInvoiceNumber = () => {
    const date = new Date();
    const dateStr = date.toISOString().slice(0, 10).replace(/-/g, '');
    const random = Math.floor(Math.random() * 10000).toString().padStart(4, '0');
    return `INV-${dateStr}-${random}`;
};

/**
 * GET /api/transactions
 * Get transactions with filters
 */
router.get('/', async (req, res) => {
    try {
        const { status, date_from, date_to, shift_id, page = 1, limit = 50 } = req.query;
        const offset = (page - 1) * limit;

        let sql = `
      SELECT t.*, s.user_id, b.name as branch_name, c.name as customer_name
      FROM transactions t
      LEFT JOIN shifts s ON t.shift_id = s.id
      LEFT JOIN branches b ON t.branch_id = b.id
      LEFT JOIN customers c ON t.customer_id = c.id
      WHERE 1=1
    `;
        const params = [];

        if (status) {
            sql += ' AND t.status = ?';
            params.push(status);
        }

        if (date_from) {
            sql += ' AND DATE(t.created_at) >= ?';
            params.push(date_from);
        }

        if (date_to) {
            sql += ' AND DATE(t.created_at) <= ?';
            params.push(date_to);
        }

        if (shift_id) {
            sql += ' AND t.shift_id = ?';
            params.push(shift_id);
        }

        // Branch filtering: manager/cashier only see their branch
        if (req.user.branch_id) {
            sql += ' AND t.branch_id = ?';
            params.push(req.user.branch_id);
        }

        sql += ' ORDER BY t.created_at DESC LIMIT ? OFFSET ?';
        params.push(parseInt(limit), offset);

        const [transactions] = await req.tenantDb.execute(sql, params);

        res.json({
            success: true,
            data: transactions
        });
    } catch (error) {
        console.error('Get transactions error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get transactions'
        });
    }
});

/**
 * GET /api/transactions/:id
 * Get transaction detail with items
 */
router.get('/:id', async (req, res) => {
    try {
        const [transactions] = await req.tenantDb.execute(`
            SELECT t.*, 
                   c.name as customer_name,
                   b.name as branch_name
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN branches b ON t.branch_id = b.id
            WHERE t.id = ?`,
            [req.params.id]
        );

        if (transactions.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Transaction not found'
            });
        }

        const [items] = await req.tenantDb.execute(
            'SELECT * FROM transaction_items WHERE transaction_id = ?',
            [req.params.id]
        );

        res.json({
            success: true,
            data: {
                ...transactions[0],
                items
            }
        });
    } catch (error) {
        console.error('Get transaction error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get transaction'
        });
    }
});

/**
 * DELETE /api/transactions/:id
 * Delete a transaction permanently and restore stock
 */
router.delete('/:id', async (req, res) => {
    const connection = req.tenantDb;

    try {
        const transactionId = req.params.id;

        // 1. Get existing transaction
        const [existing] = await connection.execute(
            'SELECT id, invoice_number, status, branch_id FROM transactions WHERE id = ?',
            [transactionId]
        );

        if (existing.length === 0) {
            return res.status(404).json({ success: false, message: 'Transaction not found' });
        }

        const branchId = existing[0].branch_id;

        // 2. Get transaction items for stock restore
        const [items] = await connection.execute(
            'SELECT product_id, quantity FROM transaction_items WHERE transaction_id = ?',
            [transactionId]
        );

        // 3. Restore stock for each item (only if transaction was completed)
        if (existing[0].status === 'completed') {
            for (const item of items) {
                // Update global products.stock
                await connection.execute(
                    'UPDATE products SET stock = stock + ? WHERE id = ?',
                    [item.quantity, item.product_id]
                );

                // Update branch_stock if branch exists
                if (branchId) {
                    await connection.execute(`
                        UPDATE branch_stock SET stock = stock + ? 
                        WHERE branch_id = ? AND product_id = ?`,
                        [item.quantity, branchId, item.product_id]
                    );
                }
            }
            console.log(`[DELETE] Restored stock for ${items.length} items (branch=${branchId})`);
        }

        // 4. Delete transaction (items deleted by CASCADE or manually)
        await connection.execute('DELETE FROM transaction_items WHERE transaction_id = ?', [transactionId]);
        await connection.execute('DELETE FROM transactions WHERE id = ?', [transactionId]);

        console.log(`[DELETE] Transaction ${existing[0].invoice_number} deleted by ${req.user?.name}`);

        res.json({
            success: true,
            message: 'Transaction deleted and stock restored',
            itemsRestored: items.length
        });
    } catch (error) {
        console.error('Delete transaction error:', error.message);
        res.status(500).json({ success: false, message: 'Failed to delete transaction: ' + error.message });
    }
});

/**
 * PUT /api/transactions/:id
 * Edit an existing transaction (update items, quantities)
 * With stock adjustment
 */
router.put('/:id', async (req, res) => {
    const connection = req.tenantDb;

    try {
        const { items, discount = 0, notes } = req.body;
        const transactionId = req.params.id;

        // 1. Get existing transaction
        const [existing] = await connection.execute(
            'SELECT * FROM transactions WHERE id = ?',
            [transactionId]
        );

        if (existing.length === 0) {
            return res.status(404).json({ success: false, message: 'Transaction not found' });
        }

        const [oldItems] = await connection.execute(
            'SELECT * FROM transaction_items WHERE transaction_id = ?',
            [transactionId]
        );
        console.log('[EDIT] Old items count:', oldItems.length);

        // Get branch_id from existing transaction for branch_stock update
        const branchId = existing[0].branch_id;

        // 3. Restore stock from old items (add back to inventory)
        for (const oldItem of oldItems) {
            const restoreQty = parseFloat(oldItem.quantity) || 0;
            console.log(`[EDIT] Restoring stock: product_id=${oldItem.product_id}, qty=+${restoreQty}, branch=${branchId}`);

            // Update global products.stock
            await connection.execute(
                'UPDATE products SET stock = stock + ? WHERE id = ?',
                [restoreQty, oldItem.product_id]
            );

            // Update branch_stock if branch exists
            if (branchId) {
                await connection.execute(`
                    UPDATE branch_stock SET stock = stock + ? 
                    WHERE branch_id = ? AND product_id = ?`,
                    [restoreQty, branchId, oldItem.product_id]
                );
            }
        }

        // 4. Delete old transaction items
        await connection.execute(
            'DELETE FROM transaction_items WHERE transaction_id = ?',
            [transactionId]
        );

        // 5. Insert new items and deduct stock
        console.log('[EDIT] New items count:', items.length);
        let subtotal = 0;
        for (const item of items) {
            const itemSubtotal = item.quantity * item.unit_price;
            subtotal += itemSubtotal;

            // Insert new item (matching POST format - 8 columns)
            await connection.execute(`
                INSERT INTO transaction_items 
                (transaction_id, product_id, product_name, quantity, unit_price, subtotal, unit_name, buy_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)`,
                [
                    transactionId,
                    item.product_id,
                    item.product_name || '',
                    item.quantity,
                    item.unit_price,
                    itemSubtotal,
                    item.unit_name || 'Pcs',
                    item.buy_price || 0
                ]
            );

            // Deduct stock for new items
            const deductQty = parseFloat(item.quantity) || 0;
            console.log(`[EDIT] Deducting stock: product_id=${item.product_id}, qty=-${deductQty}, branch=${branchId}`);

            // Update global products.stock
            await connection.execute(
                'UPDATE products SET stock = stock - ? WHERE id = ?',
                [deductQty, item.product_id]
            );

            // Update branch_stock if branch exists
            if (branchId) {
                await connection.execute(`
                    UPDATE branch_stock SET stock = stock - ? 
                    WHERE branch_id = ? AND product_id = ?`,
                    [deductQty, branchId, item.product_id]
                );
            }
        }

        // 6. Calculate new total
        const totalAmount = subtotal - (parseFloat(discount) || 0);

        // 7. Update transaction totals only
        await connection.execute(`
            UPDATE transactions 
            SET subtotal = ?, discount = ?, total_amount = ?
            WHERE id = ?`,
            [subtotal, discount || 0, totalAmount, transactionId]
        );

        // 8. Fetch updated transaction
        const [updated] = await connection.execute(
            'SELECT * FROM transactions WHERE id = ?',
            [transactionId]
        );
        const [updatedItems] = await connection.execute(
            'SELECT * FROM transaction_items WHERE transaction_id = ?',
            [transactionId]
        );

        console.log(`[EDIT] Transaction ${existing[0].invoice_number} edited by ${req.user?.name}`);

        res.json({
            success: true,
            message: 'Transaction updated successfully',
            debug: {
                oldItemsCount: oldItems.length,
                newItemsCount: items.length,
                oldProductIds: oldItems.map(i => i.product_id),
                newProductIds: items.map(i => i.product_id)
            },
            data: {
                ...updated[0],
                items: updatedItems
            }
        });

    } catch (error) {
        console.error('Edit transaction error:', error.message);
        console.error('SQL Error details:', error.sql || error);
        res.status(500).json({ success: false, message: 'Failed to update transaction: ' + error.message });
    }
});

// Lazy Migration Helper
async function ensureTransactionColumns(db) {
    try {
        // Check/Add unit_name
        const [unitCols] = await db.execute("SHOW COLUMNS FROM transaction_items LIKE 'unit_name'");
        if (unitCols.length === 0) {
            console.log('[MIGRATION] Adding unit_name column to transaction_items table...');
            await db.execute("ALTER TABLE transaction_items ADD COLUMN unit_name VARCHAR(50) DEFAULT 'Pcs'");
        }

        // Check/Add buy_price
        const [buyPriceCols] = await db.execute("SHOW COLUMNS FROM transaction_items LIKE 'buy_price'");
        if (buyPriceCols.length === 0) {
            console.log('[MIGRATION] Adding buy_price column to transaction_items table...');
            await db.execute("ALTER TABLE transaction_items ADD COLUMN buy_price DECIMAL(10, 2) DEFAULT 0");
        }

        // Check/Add conversion_qty (just in case)
        const [convCols] = await db.execute("SHOW COLUMNS FROM transaction_items LIKE 'conversion_qty'");
        if (convCols.length === 0) {
            // console.log('[MIGRATION] Adding conversion_qty column to transaction_items table...');
            // await db.execute("ALTER TABLE transaction_items ADD COLUMN conversion_qty DECIMAL(10,2) DEFAULT 1");
        }
    } catch (error) {
        console.error('[MIGRATION] Failed to check/add transaction columns:', error);
    }
}

/**
 * POST /api/transactions
 * Create new transaction (from POS)
 * Supports: Cash, Credit Card, QRIS, Debt (customer credit)
 */
router.post('/', [
    body('shift_id').optional({ nullable: true }).isNumeric().withMessage('Shift ID must be numeric'), // Made optional temporarily
    body('items').isArray({ min: 1 }).withMessage('At least one item is required'),
    body('payment_method').isIn(['cash', 'debit', 'credit', 'qris', 'debt']).withMessage('Invalid payment method'),
    body('payment_amount').isNumeric().withMessage('Payment amount is required')
], async (req, res) => {
    const connection = await req.tenantDb.getConnection();

    try {
        // Ensure columns exist before starting transaction
        await ensureTransactionColumns(req.tenantDb);

        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        await connection.beginTransaction();

        const {
            shift_id, items, payment_method, payment_amount,
            discount = 0, tax = 0,
            customer_id = null, due_date = null
        } = req.body;

        // Calculate totals
        let subtotal = 0;
        for (const item of items) {
            subtotal += item.quantity * item.unit_price;
        }

        const total_amount = subtotal - discount + tax;

        // Determine payment status and change amount
        let payment_status = 'paid';
        let change_amount = 0;
        let actual_payment = payment_amount;

        if (payment_method === 'debt') {
            // Full credit - customer owes entire amount
            if (payment_amount <= 0) {
                payment_status = 'unpaid';
                actual_payment = 0;
            } else if (payment_amount < total_amount) {
                payment_status = 'partial';
                actual_payment = payment_amount;
            } else {
                payment_status = 'paid';
                actual_payment = total_amount;
            }
        } else if (payment_method === 'cash') {
            change_amount = payment_amount - total_amount;
            if (payment_amount < total_amount) {
                await connection.rollback();
                return res.status(400).json({
                    success: false,
                    message: 'Payment amount is less than total'
                });
            }
        } else {
            // Card/QRIS - assume exact payment
            actual_payment = total_amount;
        }

        // Check status - default to completed if not specified
        const status = req.body.status && ['pending', 'completed'].includes(req.body.status) ? req.body.status : 'completed';

        // Salesman & Visit (Optional - must be null not undefined for SQL)
        const salesman_id = req.body.salesman_id || null;
        const visit_id = req.body.visit_id || null;

        // Get branch_id from shift or User
        let branchId = null;
        if (shift_id) {
            const [[shiftInfo]] = await connection.execute(
                'SELECT branch_id FROM shifts WHERE id = ?',
                [shift_id]
            );
            branchId = shiftInfo?.branch_id || null;
        }
        if (!branchId && req.user.branch_id) branchId = req.user.branch_id;

        // Create transaction (including cashier_name for reprint)
        const invoice_number = generateInvoiceNumber();
        const cashier_name = req.user?.name || 'Admin';
        const [transResult] = await connection.execute(`
      INSERT INTO transactions (shift_id, branch_id, invoice_number, subtotal, discount, tax, total_amount, payment_method, payment_amount, change_amount, status, customer_id, payment_status, due_date, cashier_name)
      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    `, [shift_id, branchId, invoice_number, subtotal, discount, tax, total_amount, payment_method, actual_payment, change_amount, status, customer_id || null, payment_status, due_date || null, cashier_name]);

        const transactionId = transResult.insertId;

        // Create transaction items and update stock
        for (const item of items) {
            // Get product info
            const [products] = await connection.execute(
                'SELECT name, stock, sell_price, buy_price FROM products WHERE id = ?',
                [item.product_id]
            );

            if (products.length === 0) {
                await connection.rollback();
                return res.status(400).json({
                    success: false,
                    message: `Product ID ${item.product_id} not found`
                });
            }

            const product = products[0];

            // Use item unit_price if provided (for flexibility), else use product price
            // But usually we respect the requested price (with validation if needed)
            const unitPrice = item.unit_price;
            const unitName = item.unit_name || 'Pcs';

            // Get buy_price from product_units for this specific unit
            let buyPrice = product.buy_price || 0;
            if (item.unit_id) {
                const [unitInfo] = await connection.execute(
                    'SELECT buy_price FROM product_units WHERE product_id = ? AND unit_id = ?',
                    [item.product_id, item.unit_id]
                );
                if (unitInfo.length > 0 && unitInfo[0].buy_price) {
                    buyPrice = parseFloat(unitInfo[0].buy_price);
                }
            }
            console.log(`[DEBUG] Saving item: ${product.name}, unit_name: ${unitName}, buy_price: ${buyPrice}`);

            // Insert transaction item (including unit_name and buy_price for reprint and reports)
            await connection.execute(`
                INSERT INTO transaction_items (transaction_id, product_id, product_name, quantity, unit_price, subtotal, unit_name, buy_price)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            `, [transactionId, item.product_id, product.name, item.quantity, unitPrice, item.quantity * unitPrice, unitName, buyPrice]);

            // --- STOCK DEDUCTION LOGIC ---
            // Only deduct stock if status is 'completed' (or delivering etc)
            // 'pending' status (Canvaser order) DOES NOT deduct stock yet

            if (status !== 'pending') {
                const conversionQty = parseFloat(item.conversion_qty) || 1;
                const requiredStock = item.quantity * conversionQty;

                // Get effective stock check
                let effectiveStock = parseFloat(product.stock);
                if (branchId) {
                    const [branchStockResult] = await connection.execute(
                        'SELECT stock FROM branch_stock WHERE branch_id = ? AND product_id = ?',
                        [branchId, item.product_id]
                    );
                    if (branchStockResult.length > 0) {
                        effectiveStock = parseFloat(branchStockResult[0].stock) || 0;
                    }
                }

                if (effectiveStock < requiredStock) {
                    await connection.rollback();
                    return res.status(400).json({
                        success: false,
                        message: `Insufficient stock for ${product.name}. Need ${requiredStock}, only have ${effectiveStock}`
                    });
                }

                // Reduce stock
                await connection.execute(
                    'UPDATE products SET stock = stock - ? WHERE id = ?',
                    [requiredStock, item.product_id]
                );

                if (branchId) {
                    await connection.execute(
                        'UPDATE branch_stock SET stock = stock - ? WHERE branch_id = ? AND product_id = ?',
                        [requiredStock, branchId, item.product_id]
                    );
                }

                // --- FIFO MECHANISM START ---
                // 1. Fetch batches with remaining stock
                const [batches] = await connection.execute(`
                    SELECT id, current_stock, quantity 
                    FROM purchase_items 
                    WHERE product_id = ? AND (current_stock > 0 OR current_stock IS NULL)
                    ORDER BY (expiry_date IS NULL), expiry_date ASC, id ASC
                `, [item.product_id]);

                let qtyToDeduct = requiredStock;

                for (const batch of batches) {
                    if (qtyToDeduct <= 0) break;
                    const currentBatchStock = batch.current_stock !== null ? parseFloat(batch.current_stock) : parseFloat(batch.quantity);
                    const deduction = Math.min(currentBatchStock, qtyToDeduct);
                    const newStock = currentBatchStock - deduction;

                    await connection.execute(
                        'UPDATE purchase_items SET current_stock = ? WHERE id = ?',
                        [newStock, batch.id]
                    );
                    qtyToDeduct -= deduction;
                }
                // --- FIFO MECHANISM END ---
            }
        }

        await connection.commit();

        res.status(201).json({
            success: true,
            message: 'Transaction completed',
            data: {
                id: transactionId,
                invoice_number,
                total_amount,
                change_amount
            }
        });
    } catch (error) {
        await connection.rollback();
        console.error('Create transaction error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create transaction: ' + error.message
        });
    } finally {
        connection.release();
    }
});

/**
 * PATCH /api/transactions/:id/cancel
 * Cancel a transaction
 */
router.patch('/:id/cancel', async (req, res) => {
    const connection = await req.tenantDb.getConnection();

    try {
        await connection.beginTransaction();

        // Get transaction
        const [transactions] = await connection.execute(
            'SELECT * FROM transactions WHERE id = ?',
            [req.params.id]
        );

        if (transactions.length === 0) {
            await connection.rollback();
            return res.status(404).json({
                success: false,
                message: 'Transaction not found'
            });
        }

        const transaction = transactions[0];

        if (transaction.status === 'cancelled') {
            await connection.rollback();
            return res.status(400).json({
                success: false,
                message: 'Transaction already cancelled'
            });
        }

        // Restore stock
        const [items] = await connection.execute(
            'SELECT * FROM transaction_items WHERE transaction_id = ?',
            [req.params.id]
        );

        for (const item of items) {
            await connection.execute(
                'UPDATE products SET stock = stock + ? WHERE id = ?',
                [item.quantity, item.product_id]
            );
        }

        // Update transaction status
        await connection.execute(
            'UPDATE transactions SET status = "cancelled" WHERE id = ?',
            [req.params.id]
        );

        await connection.commit();

        res.json({
            success: true,
            message: 'Transaction cancelled successfully'
        });
    } catch (error) {
        await connection.rollback();
        console.error('Cancel transaction error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to cancel transaction'
        });
    } finally {
        connection.release();
    }
});

/**
 * Shift Management
 */

/**
 * GET /api/transactions/shifts/current
 * Get current open shift for user
 */
router.get('/shifts/current', async (req, res) => {
    try {
        const [shifts] = await req.tenantDb.execute(
            'SELECT * FROM shifts WHERE user_id = ? AND status = "open" ORDER BY start_time DESC LIMIT 1',
            [req.user.id]
        );

        res.json({
            success: true,
            data: shifts[0] || null
        });
    } catch (error) {
        console.error('Get current shift error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get current shift'
        });
    }
});

/**
 * POST /api/transactions/shifts/open
 * Open a new shift
 */
router.post('/shifts/open', [
    body('opening_cash').isNumeric().withMessage('Opening cash amount is required')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        // Check if user has open shift
        const [existingShifts] = await req.tenantDb.execute(
            'SELECT id FROM shifts WHERE user_id = ? AND status = "open"',
            [req.user.id]
        );

        if (existingShifts.length > 0) {
            return res.status(400).json({
                success: false,
                message: 'You already have an open shift'
            });
        }

        // Get user's branch_id (if assigned)
        const branchId = req.user.branch_id || null;

        const [result] = await req.tenantDb.execute(
            'INSERT INTO shifts (user_id, branch_id, opening_cash) VALUES (?, ?, ?)',
            [req.user.id, branchId, req.body.opening_cash]
        );

        res.status(201).json({
            success: true,
            message: 'Shift opened successfully',
            data: { id: result.insertId }
        });
    } catch (error) {
        console.error('Open shift error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to open shift'
        });
    }
});

/**
 * POST /api/transactions/shifts/:id/close
 * Close a shift
 */
router.post('/shifts/:id/close', [
    body('closing_cash').isNumeric().withMessage('Closing cash amount is required')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        // Verify shift belongs to user
        const [shifts] = await req.tenantDb.execute(
            'SELECT * FROM shifts WHERE id = ? AND user_id = ?',
            [req.params.id, req.user.id]
        );

        if (shifts.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Shift not found'
            });
        }

        if (shifts[0].status === 'closed') {
            return res.status(400).json({
                success: false,
                message: 'Shift already closed'
            });
        }

        await req.tenantDb.execute(
            'UPDATE shifts SET closing_cash = ?, end_time = NOW(), status = "closed", notes = ? WHERE id = ?',
            [req.body.closing_cash, req.body.notes || null, req.params.id]
        );

        res.json({
            success: true,
            message: 'Shift closed successfully'
        });
    } catch (error) {
        console.error('Close shift error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to close shift'
        });
    }
});

module.exports = router;
