const express = require('express');
const router = express.Router();

/**
 * GET /api/sales-orders
 * List pending orders (Canvas)
 */
router.get('/', async (req, res) => {
    try {
        const { status = 'pending', start_date, end_date, salesman_id } = req.query;
        let sql = `
            SELECT t.*, s.name as salesman_name, c.name as customer_name, b.name as branch_name 
            FROM transactions t
            LEFT JOIN salesmen s ON t.salesman_id = s.id
            LEFT JOIN customers c ON t.customer_id = c.id
            LEFT JOIN branches b ON t.branch_id = b.id
            WHERE t.status = ?
        `;
        const params = [status];

        if (start_date) {
            sql += ' AND DATE(t.created_at) >= ?';
            params.push(start_date);
        }
        if (end_date) {
            sql += ' AND DATE(t.created_at) <= ?';
            params.push(end_date);
        }
        if (salesman_id) {
            sql += ' AND t.salesman_id = ?';
            params.push(salesman_id);
        }

        sql += ' ORDER BY t.created_at DESC';

        const [orders] = await req.tenantDb.execute(sql, params);
        res.json({ success: true, data: orders });
    } catch (error) {
        console.error('Get sales orders error:', error);
        res.status(500).json({ success: false, message: 'Failed to fetch sales orders' });
    }
});

/**
 * POST /api/sales-orders/:id/approve
 * Approve pending order -> deduct stock -> set status 'completed' (or delivering)
 */
router.post('/:id/approve', async (req, res) => {
    const connection = await req.tenantDb.getConnection();
    try {
        await connection.beginTransaction();

        // 1. Get transaction and items
        const [trans] = await connection.execute('SELECT * FROM transactions WHERE id = ?', [req.params.id]);
        if (trans.length === 0) throw new Error('Transaction not found');
        const transaction = trans[0];

        if (transaction.status !== 'pending') {
            throw new Error(`Transaction status is ${transaction.status}, cannot approve`);
        }

        const [items] = await connection.execute('SELECT * FROM transaction_items WHERE transaction_id = ?', [req.params.id]);

        // 2. STOCK DEDUCTION (Re-using logic from transactions.js roughly)
        // Note: Ideally extract stock logic to a shared service/helper
        const branchId = transaction.branch_id;

        for (const item of items) {
            const conversionQty = parseFloat(item.conversion_qty) || 1;
            const requiredStock = item.quantity * conversionQty;

            // Check stock
            const [prods] = await connection.execute('SELECT name, stock FROM products WHERE id = ?', [item.product_id]);
            const product = prods[0];

            let effectiveStock = parseFloat(product.stock);
            // Branch check if applicable
            if (branchId) {
                const [bs] = await connection.execute('SELECT stock FROM branch_stock WHERE branch_id = ? AND product_id = ?', [branchId, item.product_id]);
                if (bs.length > 0) effectiveStock = parseFloat(bs[0].stock);
            }

            if (effectiveStock < requiredStock) {
                throw new Error(`Insufficient stock for ${product.name} (Need: ${requiredStock}, Have: ${effectiveStock})`);
            }

            // Deduct Main Stock
            await connection.execute('UPDATE products SET stock = stock - ? WHERE id = ?', [requiredStock, item.product_id]);

            // Deduct Branch Stock
            if (branchId) {
                await connection.execute('UPDATE branch_stock SET stock = stock - ? WHERE branch_id = ? AND product_id = ?', [requiredStock, branchId, item.product_id]);
            }

            // FIFO
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
                await connection.execute('UPDATE purchase_items SET current_stock = ? WHERE id = ?', [currentBatchStock - deduction, batch.id]);
                qtyToDeduct -= deduction;
            }
        }

        // 3. Update Status
        await connection.execute('UPDATE transactions SET status = "completed" WHERE id = ?', [req.params.id]);

        await connection.commit();
        res.json({ success: true, message: 'Order approved and stock deducted' });

    } catch (error) {
        await connection.rollback();
        res.status(400).json({ success: false, message: error.message });
    } finally {
        connection.release();
    }
});

/**
 * POST /api/sales-orders/:id/reject
 * Reject pending order -> set status 'cancelled'
 */
router.post('/:id/reject', async (req, res) => {
    try {
        const [result] = await req.tenantDb.execute(
            'UPDATE transactions SET status = "cancelled" WHERE id = ? AND status = "pending"',
            [req.params.id]
        );
        if (result.affectedRows === 0) {
            return res.status(400).json({ success: false, message: 'Order not found or not pending' });
        }
        res.json({ success: true, message: 'Order rejected' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

module.exports = router;
