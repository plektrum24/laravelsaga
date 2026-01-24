const express = require('express');
const router = express.Router();

/**
 * GET /api/debts/suppliers
 * List all unpaid/partial purchases (supplier debts)
 */
router.get('/suppliers', async (req, res) => {
    try {
        const [debts] = await req.tenantDb.execute(`
            SELECT 
                p.id,
                p.invoice_number,
                p.date,
                p.total_amount as amount,
                COALESCE(p.paid_amount, 0) as paid,
                p.due_date,
                p.payment_status as status,
                s.name as supplier,
                s.phone
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.payment_status IN ('unpaid', 'partial', 'paid')
            ORDER BY 
                CASE WHEN p.payment_status = 'paid' THEN 1 ELSE 0 END,
                p.due_date ASC
        `);

        res.json({ success: true, data: debts });
    } catch (error) {
        console.error('Error fetching supplier debts:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * POST /api/debts/suppliers/:id/pay
 * Record payment for a supplier debt
 */
router.post('/suppliers/:id/pay', async (req, res) => {
    const { id } = req.params;
    const { amount } = req.body;

    if (!amount || amount <= 0) {
        return res.status(400).json({ success: false, message: 'Invalid payment amount' });
    }

    const conn = await req.tenantDb.getConnection();

    try {
        await conn.beginTransaction();

        // Get current purchase
        const [purchases] = await conn.execute(
            'SELECT * FROM purchases WHERE id = ?', [id]
        );

        if (purchases.length === 0) {
            await conn.rollback();
            conn.release();
            return res.status(404).json({ success: false, message: 'Purchase not found' });
        }

        const purchase = purchases[0];
        // Parse all amounts as float to avoid decimal precision issues
        const currentPaid = parseFloat(purchase.paid_amount) || 0;
        const paymentAmount = parseFloat(amount);
        const totalAmount = parseFloat(purchase.total_amount);

        const newPaidAmount = currentPaid + paymentAmount;
        const remaining = totalAmount - newPaidAmount;

        console.log('[SUPPLIER PAYMENT] Calculation:', { currentPaid, paymentAmount, newPaidAmount, remaining });

        // Determine new status
        let newStatus = 'partial';
        if (remaining <= 0) {
            newStatus = 'paid';
        }

        // Update purchase
        await conn.execute(
            'UPDATE purchases SET paid_amount = ?, payment_status = ? WHERE id = ?',
            [newPaidAmount, newStatus, id]
        );

        await conn.commit();
        conn.release();

        res.json({
            success: true,
            message: `Payment of Rp ${amount.toLocaleString()} recorded. Status: ${newStatus}`,
            data: { newPaidAmount, remaining, status: newStatus }
        });

    } catch (error) {
        await conn.rollback();
        conn.release();
        console.error('Error recording payment:', error);
        res.status(500).json({ success: false, message: 'Failed to record payment' });
    }
});

/**
 * GET /api/debts/customers
 * List all unpaid/partial transactions (customer receivables)
 */
router.get('/customers', async (req, res) => {
    try {
        const [receivables] = await req.tenantDb.execute(`
            SELECT 
                t.id,
                t.invoice_number,
                t.created_at as date,
                t.total_amount as amount,
                COALESCE(t.payment_amount, 0) as paid,
                t.due_date,
                t.payment_status as status,
                c.name as customer,
                c.phone
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            WHERE t.payment_status IN ('unpaid', 'partial', 'debt', 'paid')
            ORDER BY 
                CASE WHEN t.payment_status = 'paid' THEN 1 ELSE 0 END,
                t.due_date ASC
        `);

        res.json({ success: true, data: receivables });
    } catch (error) {
        console.error('Error fetching receivables:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * POST /api/debts/customers/:id/pay
 * Record payment for a customer receivable
 */
router.post('/customers/:id/pay', async (req, res) => {
    const { id } = req.params;
    const { amount } = req.body;

    if (!amount || amount <= 0) {
        return res.status(400).json({ success: false, message: 'Invalid payment amount' });
    }

    const conn = await req.tenantDb.getConnection();

    try {
        await conn.beginTransaction();

        // Get current transaction
        const [transactions] = await conn.execute(
            'SELECT * FROM transactions WHERE id = ?', [id]
        );

        if (transactions.length === 0) {
            await conn.rollback();
            conn.release();
            return res.status(404).json({ success: false, message: 'Transaction not found' });
        }

        const transaction = transactions[0];
        // Parse all amounts as float to avoid decimal precision issues
        const currentPaid = parseFloat(transaction.payment_amount) || 0;
        const paymentAmount = parseFloat(amount);
        const totalAmount = parseFloat(transaction.total_amount);

        const newPaidAmount = currentPaid + paymentAmount;
        const remaining = totalAmount - newPaidAmount;

        console.log('[PAYMENT] Calculation:', { currentPaid, paymentAmount, newPaidAmount, remaining });

        // Determine new status
        let newStatus = 'partial';
        if (remaining <= 0) {
            newStatus = 'paid';
        }

        // Update transaction
        await conn.execute(
            'UPDATE transactions SET payment_amount = ?, payment_status = ? WHERE id = ?',
            [newPaidAmount, newStatus, id]
        );

        await conn.commit();
        conn.release();

        res.json({
            success: true,
            message: `Payment of Rp ${amount.toLocaleString()} recorded. Status: ${newStatus}`,
            data: { newPaidAmount, remaining, status: newStatus }
        });

    } catch (error) {
        await conn.rollback();
        conn.release();
        console.error('Error recording payment:', error);
        res.status(500).json({ success: false, message: 'Failed to record payment' });
    }
});

/**
 * GET /api/debts/summary
 * Get summary of debts and receivables for dashboard
 */
router.get('/summary', async (req, res) => {
    try {
        const today = new Date().toISOString().split('T')[0];
        const nextWeek = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

        // Supplier debts summary
        const [debtStats] = await req.tenantDb.execute(`
            SELECT 
                SUM(total_amount - COALESCE(paid_amount, 0)) as totalOutstanding,
                SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END) as overdueCount,
                SUM(CASE WHEN due_date >= ? AND due_date <= ? THEN 1 ELSE 0 END) as upcomingCount
            FROM purchases
            WHERE payment_status IN ('unpaid', 'partial')
        `, [today, today, nextWeek]);

        // Customer receivables summary
        const [recStats] = await req.tenantDb.execute(`
            SELECT 
                SUM(total_amount - COALESCE(payment_amount, 0)) as totalOutstanding,
                SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END) as overdueCount,
                SUM(CASE WHEN due_date >= ? AND due_date <= ? THEN 1 ELSE 0 END) as upcomingCount
            FROM transactions
            WHERE payment_status IN ('unpaid', 'partial', 'debt')
        `, [today, today, nextWeek]);

        res.json({
            success: true,
            data: {
                debts: {
                    totalOutstanding: debtStats[0]?.totalOutstanding || 0,
                    overdueCount: debtStats[0]?.overdueCount || 0,
                    upcomingCount: debtStats[0]?.upcomingCount || 0
                },
                receivables: {
                    totalOutstanding: recStats[0]?.totalOutstanding || 0,
                    overdueCount: recStats[0]?.overdueCount || 0,
                    upcomingCount: recStats[0]?.upcomingCount || 0
                }
            }
        });
    } catch (error) {
        console.error('Error fetching debt summary:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * GET /api/debts/customers/summary
 * Get consolidated receivables grouped by customer
 */
router.get('/customers/summary', async (req, res) => {
    try {
        const [summary] = await req.tenantDb.execute(`
            SELECT 
                c.id as customer_id,
                c.name as customer_name,
                c.phone,
                COUNT(t.id) as invoice_count,
                SUM(t.total_amount) as total_amount,
                SUM(COALESCE(t.payment_amount, 0)) as total_paid,
                SUM(t.total_amount - COALESCE(t.payment_amount, 0)) as outstanding,
                MIN(t.due_date) as nearest_due_date
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            WHERE t.payment_status IN ('unpaid', 'partial', 'debt')
              AND t.customer_id IS NOT NULL
            GROUP BY c.id, c.name, c.phone
            HAVING outstanding > 0
            ORDER BY outstanding DESC
        `);

        res.json({ success: true, data: summary });
    } catch (error) {
        console.error('Error fetching customer summary:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * GET /api/debts/customers/:customerId/transactions
 * Get all transactions for a specific customer (for modal view)
 */
router.get('/customers/:customerId/transactions', async (req, res) => {
    try {
        const { customerId } = req.params;

        const [transactions] = await req.tenantDb.execute(`
            SELECT 
                t.id,
                t.invoice_number,
                t.created_at as date,
                t.total_amount as amount,
                COALESCE(t.payment_amount, 0) as paid,
                t.due_date,
                t.payment_status as status
            FROM transactions t
            WHERE t.customer_id = ?
              AND t.payment_status IN ('unpaid', 'partial', 'debt', 'paid')
            ORDER BY 
                CASE WHEN t.payment_status = 'paid' THEN 1 ELSE 0 END,
                t.created_at DESC
        `, [customerId]);

        res.json({ success: true, data: transactions });
    } catch (error) {
        console.error('Error fetching customer transactions:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * GET /api/debts/suppliers/summary
 * Get consolidated debts grouped by supplier
 */
router.get('/suppliers/summary', async (req, res) => {
    try {
        const [summary] = await req.tenantDb.execute(`
            SELECT 
                s.id as supplier_id,
                s.name as supplier_name,
                s.phone,
                COUNT(p.id) as invoice_count,
                SUM(p.total_amount) as total_amount,
                SUM(COALESCE(p.paid_amount, 0)) as total_paid,
                SUM(p.total_amount - COALESCE(p.paid_amount, 0)) as outstanding,
                MIN(p.due_date) as nearest_due_date
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.payment_status IN ('unpaid', 'partial')
              AND p.supplier_id IS NOT NULL
            GROUP BY s.id, s.name, s.phone
            HAVING outstanding > 0
            ORDER BY outstanding DESC
        `);

        res.json({ success: true, data: summary });
    } catch (error) {
        console.error('Error fetching supplier summary:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * GET /api/debts/suppliers/:supplierId/transactions
 * Get all purchases for a specific supplier (for modal view)
 */
router.get('/suppliers/:supplierId/transactions', async (req, res) => {
    try {
        const { supplierId } = req.params;

        const [purchases] = await req.tenantDb.execute(`
            SELECT 
                p.id,
                p.invoice_number,
                p.date,
                p.total_amount as amount,
                COALESCE(p.paid_amount, 0) as paid,
                p.due_date,
                p.payment_status as status
            FROM purchases p
            WHERE p.supplier_id = ?
              AND p.payment_status IN ('unpaid', 'partial', 'paid')
            ORDER BY 
                CASE WHEN p.payment_status = 'paid' THEN 1 ELSE 0 END,
                p.date DESC
        `, [supplierId]);

        res.json({ success: true, data: purchases });
    } catch (error) {
        console.error('Error fetching supplier purchases:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

module.exports = router;
