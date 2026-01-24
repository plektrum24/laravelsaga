const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

/**
 * Generate transfer number: TRF-YYYYMMDD-XXXX
 */
async function generateTransferNumber(conn) {
    const today = new Date();
    const dateStr = today.toISOString().slice(0, 10).replace(/-/g, '');
    const prefix = `TRF-${dateStr}-`;

    const [result] = await conn.execute(`
        SELECT transfer_number FROM stock_transfers 
        WHERE transfer_number LIKE ? 
        ORDER BY id DESC LIMIT 1
    `, [`${prefix}%`]);

    let nextNum = 1;
    if (result.length > 0) {
        const lastNum = parseInt(result[0].transfer_number.split('-').pop()) || 0;
        nextNum = lastNum + 1;
    }

    return `${prefix}${nextNum.toString().padStart(4, '0')}`;
}

/**
 * GET /api/transfers
 * Get all transfers with filters
 */
router.get('/', async (req, res) => {
    try {
        const { status, from_branch_id, to_branch_id, limit = 50 } = req.query;

        let query = `
            SELECT t.*, 
                   fb.name as from_branch_name, fb.code as from_branch_code,
                   tb.name as to_branch_name, tb.code as to_branch_code,
                   (SELECT COUNT(*) FROM stock_transfer_items WHERE transfer_id = t.id) as item_count,
                   (SELECT COALESCE(SUM(qty_requested), 0) FROM stock_transfer_items WHERE transfer_id = t.id) as total_qty
            FROM stock_transfers t
            JOIN branches fb ON t.from_branch_id = fb.id
            JOIN branches tb ON t.to_branch_id = tb.id
            WHERE 1=1
        `;
        const params = [];

        // Auto-filter by user's branch if they have one
        // User only sees transfers related to their branch
        if (req.user?.branch_id) {
            query += ' AND (t.from_branch_id = ? OR t.to_branch_id = ?)';
            params.push(req.user.branch_id, req.user.branch_id);
        }

        if (status) {
            query += ' AND t.status = ?';
            params.push(status);
        }

        if (from_branch_id) {
            query += ' AND t.from_branch_id = ?';
            params.push(from_branch_id);
        }

        if (to_branch_id) {
            query += ' AND t.to_branch_id = ?';
            params.push(to_branch_id);
        }

        query += ' ORDER BY t.created_at DESC LIMIT ?';
        params.push(parseInt(limit));

        const [transfers] = await req.tenantDb.execute(query, params);

        res.json({
            success: true,
            data: transfers
        });
    } catch (error) {
        console.error('Get transfers error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get transfers'
        });
    }
});

/**
 * GET /api/transfers/:id
 * Get transfer detail with items
 */
router.get('/:id', async (req, res) => {
    try {
        const [transfers] = await req.tenantDb.execute(`
            SELECT t.*, 
                   fb.name as from_branch_name, fb.code as from_branch_code,
                   tb.name as to_branch_name, tb.code as to_branch_code
            FROM stock_transfers t
            JOIN branches fb ON t.from_branch_id = fb.id
            JOIN branches tb ON t.to_branch_id = tb.id
            WHERE t.id = ?
        `, [req.params.id]);

        if (transfers.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Transfer not found'
            });
        }

        // Get items (Mapping qty_requested -> quantity for frontend)
        const [items] = await req.tenantDb.execute(`
            SELECT ti.*, ti.qty_requested as quantity, p.name as product_name, p.sku, u.name as unit_name
            FROM stock_transfer_items ti
            JOIN products p ON ti.product_id = p.id
            LEFT JOIN units u ON ti.unit_id = u.id
            WHERE ti.transfer_id = ?
        `, [req.params.id]);

        res.json({
            success: true,
            data: {
                ...transfers[0],
                items
            }
        });
    } catch (error) {
        console.error('Get transfer error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get transfer'
        });
    }
});

/**
 * POST /api/transfers
 * Create new transfer request
 */
// Restore logic
router.post('/', [
    body('from_branch_id').isInt().withMessage('From branch is required'),
    body('to_branch_id').isInt().withMessage('To branch is required'),
    body('items').isArray({ min: 1 }).withMessage('At least one item is required')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { from_branch_id, to_branch_id, items } = req.body;

        if (from_branch_id === to_branch_id) {
            return res.status(400).json({
                success: false,
                message: 'Cannot transfer to the same branch'
            });
        }

        const [branches] = await req.tenantDb.execute(
            'SELECT id FROM branches WHERE id IN (?, ?) AND is_active = true',
            [from_branch_id, to_branch_id]
        );

        if (branches.length !== 2) {
            return res.status(400).json({
                success: false,
                message: 'Invalid branch selected'
            });
        }

        const conn = await req.tenantDb.getConnection();
        await conn.beginTransaction();

        try {
            // const transferNumber = await generateTransferNumber(conn);
            const transferNumber = "TRF-DEBUG-" + Date.now();

            const sql = `
                INSERT INTO stock_transfers (transfer_number, from_branch_id, to_branch_id, created_by, status)
                VALUES (?, ?, ?, ?, 'pending')
            `;
            console.log("EXECUTING SQL:", sql);
            const [result] = await conn.execute(sql, [transferNumber, from_branch_id, to_branch_id, req.user?.id || null]);

            const transferId = result.insertId;

            for (const item of items) {
                const [stock] = await conn.execute(
                    'SELECT stock FROM branch_stock WHERE branch_id = ? AND product_id = ?',
                    [from_branch_id, item.product_id]
                );

                if (stock.length === 0 || parseFloat(stock[0].stock) < parseFloat(item.quantity)) {
                    throw new Error(`Insufficient stock for product ID ${item.product_id}`);
                }

                await conn.execute(`
                    INSERT INTO stock_transfer_items (transfer_id, product_id, qty_requested, qty_approved, unit_id)
                    VALUES (?, ?, ?, 0, ?)
                `, [transferId, item.product_id, item.quantity, item.unit_id || null]);
            }

            await conn.commit();

            res.status(201).json({
                success: true,
                message: 'Transfer created successfully',
                data: {
                    id: transferId,
                    transfer_number: transferNumber
                }
            });
        } catch (err) {
            await conn.rollback();
            throw err;
        } finally {
            conn.release();
        }
    } catch (error) {
        console.error('Create transfer error:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Failed to create transfer'
        });
    }
});

/**
 * PATCH /api/transfers/:id/approve
 * Approve and send transfer (deduct from source branch)
 */
router.patch('/:id/approve', async (req, res) => {
    try {
        const transferId = req.params.id;

        const [transfers] = await req.tenantDb.execute(
            'SELECT * FROM stock_transfers WHERE id = ? AND status = ?',
            [transferId, 'pending']
        );

        if (transfers.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Transfer not found or cannot be approved'
            });
        }

        const transfer = transfers[0];

        // Use qty_requested as the source of truth for quantity
        const [items] = await req.tenantDb.execute(
            'SELECT * FROM stock_transfer_items WHERE transfer_id = ?',
            [transferId]
        );

        const conn = await req.tenantDb.getConnection();
        await conn.beginTransaction();

        try {
            // Deduct stock from source branch
            for (const item of items) {
                // Use qty_requested
                const qty = item.qty_requested;

                await conn.execute(`
                    UPDATE branch_stock 
                    SET stock = stock - ? 
                    WHERE branch_id = ? AND product_id = ?
                `, [qty, transfer.from_branch_id, item.product_id]);

                // Update qty_approved to match requested (auto-approval logic)
                await conn.execute(`
                    UPDATE stock_transfer_items
                    SET qty_approved = ?
                    WHERE id = ?
                `, [qty, item.id]);
            }

            await conn.execute(`
                UPDATE stock_transfers 
                SET status = 'shipped', approved_by = ?, approved_at = NOW()
                WHERE id = ?
            `, [req.user?.id || null, transferId]);

            await conn.commit();

            res.json({
                success: true,
                message: 'Transfer approved and sent'
            });
        } catch (err) {
            await conn.rollback();
            throw err;
        } finally {
            conn.release();
        }
    } catch (error) {
        console.error('Approve transfer error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to approve transfer'
        });
    }
});

/**
 * PATCH /api/transfers/:id/receive
 * Receive transfer (add to destination branch)
 */
router.patch('/:id/receive', async (req, res) => {
    try {
        const transferId = req.params.id;

        const [transfers] = await req.tenantDb.execute(
            'SELECT * FROM stock_transfers WHERE id = ? AND status = ?',
            [transferId, 'shipped']
        );

        if (transfers.length === 0) {
            return res.status(400).json({
                success: false,
                message: 'Transfer not found or cannot be received (Must be Shipped)'
            });
        }

        const transfer = transfers[0];

        const [items] = await req.tenantDb.execute(
            'SELECT * FROM stock_transfer_items WHERE transfer_id = ?',
            [transferId]
        );

        const conn = await req.tenantDb.getConnection();
        await conn.beginTransaction();

        try {
            for (const item of items) {
                // Use qty_approved (or requested if approval didn't update it, but our logic does)
                const qty = item.qty_approved || item.qty_requested;

                const [existing] = await conn.execute(
                    'SELECT id FROM branch_stock WHERE branch_id = ? AND product_id = ?',
                    [transfer.to_branch_id, item.product_id]
                );

                if (existing.length === 0) {
                    await conn.execute(`
                        INSERT INTO branch_stock (branch_id, product_id, stock, min_stock)
                        VALUES (?, ?, ?, 5)
                    `, [transfer.to_branch_id, item.product_id, qty]);
                } else {
                    await conn.execute(`
                        UPDATE branch_stock 
                        SET stock = stock + ? 
                        WHERE branch_id = ? AND product_id = ?
                    `, [qty, transfer.to_branch_id, item.product_id]);
                }

                // Update qty_received
                await conn.execute(`
                    UPDATE stock_transfer_items
                    SET qty_received = ?
                    WHERE id = ?
                `, [qty, item.id]);
            }

            await conn.execute(`
                UPDATE stock_transfers 
                SET status = 'received', received_by = ?, received_at = NOW()
                WHERE id = ?
            `, [req.user?.id || null, transferId]);

            await conn.commit();

            res.json({
                success: true,
                message: 'Transfer received successfully'
            });
        } catch (err) {
            await conn.rollback();
            throw err;
        } finally {
            conn.release();
        }
    } catch (error) {
        console.error('Receive transfer error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to receive transfer: ' + error.message
        });
    }
});

/**
 * PATCH /api/transfers/:id/cancel
 * Cancel transfer
 */
router.patch('/:id/cancel', async (req, res) => {
    try {
        const transferId = req.params.id;

        const [transfers] = await req.tenantDb.execute(
            'SELECT * FROM stock_transfers WHERE id = ?',
            [transferId]
        );

        if (transfers.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Transfer not found'
            });
        }

        const transfer = transfers[0];

        if (transfer.status === 'received' || transfer.status === 'cancelled') {
            return res.status(400).json({
                success: false,
                message: 'Cannot cancel this transfer'
            });
        }

        const conn = await req.tenantDb.getConnection();
        await conn.beginTransaction();

        try {
            // If shipped, restore stock to source branch
            if (transfer.status === 'shipped') {
                const [items] = await conn.execute(
                    'SELECT * FROM stock_transfer_items WHERE transfer_id = ?',
                    [transferId]
                );

                for (const item of items) {
                    // Use qty_approved or requested
                    const qty = item.qty_approved || item.qty_requested;

                    await conn.execute(`
                        UPDATE branch_stock 
                        SET stock = stock + ? 
                        WHERE branch_id = ? AND product_id = ?
                    `, [qty, transfer.from_branch_id, item.product_id]);
                }
            }

            await conn.execute(
                'UPDATE stock_transfers SET status = ? WHERE id = ?',
                ['cancelled', transferId]
            );

            await conn.commit();

            res.json({
                success: true,
                message: 'Transfer cancelled'
            });
        } catch (err) {
            await conn.rollback();
            throw err;
        } finally {
            conn.release();
        }
    } catch (error) {
        console.error('Cancel transfer error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to cancel transfer'
        });
    }
});

module.exports = router;
