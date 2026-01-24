const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

// Middleware to ensure tables exist (Lazy Migration for Customer Returns)
const ensureTablesExist = async (req, res, next) => {
    try {
        await req.tenantDb.query(`
            CREATE TABLE IF NOT EXISTS customer_returns (
                id INT PRIMARY KEY AUTO_INCREMENT,
                customer_id INT NULL,
                code VARCHAR(50) NOT NULL,
                date DATE NOT NULL,
                reason VARCHAR(255),
                notes TEXT,
                status ENUM('pending', 'approved', 'completed', 'rejected') DEFAULT 'pending',
                total_amount DECIMAL(15,2) DEFAULT 0,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (customer_id) REFERENCES customers(id)
            )
        `);

        await req.tenantDb.query(`
            CREATE TABLE IF NOT EXISTS customer_return_items (
                id INT PRIMARY KEY AUTO_INCREMENT,
                return_id INT NOT NULL,
                product_id INT NOT NULL,
                quantity INT NOT NULL,
                unit_price DECIMAL(15,2) NOT NULL,
                subtotal DECIMAL(15,2) NOT NULL,
                FOREIGN KEY (return_id) REFERENCES customer_returns(id) ON DELETE CASCADE,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        `);
        next();
    } catch (error) {
        console.error('Table creation error:', error);
        res.status(500).json({ success: false, message: 'Database initialization error' });
    }
};

router.use(ensureTablesExist);

/**
 * GET /api/returns
 * List all customer returns
 */
router.get('/', async (req, res) => {
    try {
        const [returns] = await req.tenantDb.execute(`
            SELECT cr.*, c.name as customer_name,
            (SELECT COUNT(*) FROM customer_return_items WHERE return_id = cr.id) as items_count
            FROM customer_returns cr
            LEFT JOIN customers c ON cr.customer_id = c.id
            ORDER BY cr.date DESC, cr.created_at DESC
        `);

        // Transform for frontend
        const formatted = returns.map(r => ({
            id: r.id,
            code: r.code,
            customer: r.customer_name || 'Walk-in Customer',
            items: r.items_count,
            total: r.total_amount,
            status: r.status,
            date: r.date,
            reason: r.reason,
            notes: r.notes
        }));

        res.json({ success: true, data: formatted });
    } catch (error) {
        console.error('Fetch returns error:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * POST /api/returns
 * Create new customer return
 */
router.post('/', [
    body('items').isArray({ min: 1 }).withMessage('At least one item is required'),
], async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) return res.status(400).json({ success: false, errors: errors.array() });

    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const { customer_id, reason, notes, items, date } = req.body;
        const code = 'CRET-' + new Date().toISOString().split('T')[0].replace(/-/g, '') + '-' + Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        const returnDate = date || new Date().toISOString().split('T')[0];

        const total_amount = items.reduce((sum, item) => sum + (parseFloat(item.subtotal) || (item.quantity * item.cost_price)), 0);

        // 1. Create Return Record
        const [result] = await connection.execute(
            `INSERT INTO customer_returns (customer_id, code, date, reason, notes, status, total_amount) 
             VALUES (?, ?, ?, ?, ?, 'pending', ?)`,
            [customer_id || null, code, returnDate, reason, notes || null, total_amount]
        );
        const returnId = result.insertId;

        // 2. Insert Items
        for (const item of items) {
            const itemSubtotal = parseFloat(item.subtotal) || (item.quantity * item.cost_price);
            await connection.execute(
                `INSERT INTO customer_return_items (return_id, product_id, quantity, unit_price, subtotal) 
                 VALUES (?, ?, ?, ?, ?)`,
                [returnId, item.product_id, item.quantity, item.cost_price, itemSubtotal]
            );
        }

        await connection.commit();
        res.json({ success: true, message: 'Return created successfully (Pending approval)' });

    } catch (error) {
        await connection.rollback();
        console.error('Create return error:', error);
        res.status(500).json({ success: false, message: 'Failed to create return' });
    } finally {
        connection.release();
    }
});

/**
 * GET /api/returns/:id
 * Get return detail
 */
router.get('/:id', async (req, res) => {
    try {
        const [returns] = await req.tenantDb.execute(`
            SELECT cr.*, c.name as customer_name
            FROM customer_returns cr
            LEFT JOIN customers c ON cr.customer_id = c.id
            WHERE cr.id = ?
        `, [req.params.id]);

        if (returns.length === 0) {
            return res.status(404).json({ success: false, message: 'Return not found' });
        }

        const [items] = await req.tenantDb.execute(`
            SELECT cri.*, p.name as product_name, p.sku
            FROM customer_return_items cri
            JOIN products p ON cri.product_id = p.id
            WHERE cri.return_id = ?
        `, [req.params.id]);

        const returnData = returns[0];
        returnData.items = items;

        res.json({ success: true, data: returnData });
    } catch (error) {
        console.error('Get return detail error:', error);
        res.status(500).json({ success: false, message: 'Failed to get return detail' });
    }
});

/**
 * PUT /api/returns/:id/status
 * Update status (Approve = Increase Stock)
 */
router.put('/:id/status', async (req, res) => {
    const { status } = req.body;
    const validStatuses = ['pending', 'approved', 'completed', 'rejected'];

    if (!validStatuses.includes(status)) {
        return res.status(400).json({ success: false, message: 'Invalid status' });
    }

    const userRole = req.user?.role || 'cashier';
    const canApprove = ['manager', 'tenant_owner', 'superadmin'].includes(userRole);
    if ((status === 'approved' || status === 'rejected') && !canApprove) {
        return res.status(403).json({ success: false, message: 'Only manager or above can approve returns' });
    }

    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const [currentReturn] = await connection.execute(
            'SELECT status FROM customer_returns WHERE id = ?',
            [req.params.id]
        );

        if (currentReturn.length === 0) {
            await connection.rollback();
            return res.status(404).json({ success: false, message: 'Return not found' });
        }

        const oldStatus = currentReturn[0].status;

        await connection.execute(
            'UPDATE customer_returns SET status = ? WHERE id = ?',
            [status, req.params.id]
        );

        const branchId = req.body.branch_id || req.user?.branch_id;

        // Approved: INCREASE Stock
        if (status === 'approved' && oldStatus === 'pending') {
            const [items] = await connection.execute(
                'SELECT product_id, quantity FROM customer_return_items WHERE return_id = ?',
                [req.params.id]
            );

            for (const item of items) {
                if (branchId) {
                    await connection.execute(
                        'UPDATE branch_stock SET stock = stock + ? WHERE branch_id = ? AND product_id = ?',
                        [item.quantity, branchId, item.product_id]
                    );
                }
                await connection.execute(
                    'UPDATE products SET stock = stock + ? WHERE id = ?',
                    [item.quantity, item.product_id]
                );
            }
        }

        // Rejected (after approved): DECREASE Stock (Revert)
        if (status === 'rejected' && oldStatus === 'approved') {
            const [items] = await connection.execute(
                'SELECT product_id, quantity FROM customer_return_items WHERE return_id = ?',
                [req.params.id]
            );

            for (const item of items) {
                if (branchId) {
                    await connection.execute(
                        'UPDATE branch_stock SET stock = stock - ? WHERE branch_id = ? AND product_id = ?',
                        [item.quantity, branchId, item.product_id]
                    );
                }
                await connection.execute(
                    'UPDATE products SET stock = stock - ? WHERE id = ?',
                    [item.quantity, item.product_id]
                );
            }
        }

        await connection.commit();
        res.json({ success: true, message: `Return status updated to ${status}` });

    } catch (error) {
        await connection.rollback();
        console.error('Update return status error:', error);
        res.status(500).json({ success: false, message: 'Failed to update return status' });
    } finally {
        connection.release();
    }
});

/**
 * DELETE /api/returns/:id
 */
router.delete('/:id', async (req, res) => {
    const connection = await req.tenantDb.getConnection();
    await connection.beginTransaction();

    try {
        const [returns] = await connection.execute(
            'SELECT status FROM customer_returns WHERE id = ?',
            [req.params.id]
        );

        if (returns.length === 0) {
            await connection.rollback();
            return res.status(404).json({ success: false, message: 'Return not found' });
        }

        const returnStatus = returns[0].status;

        // If Approved, Revert Stock (Decrease) before deleting
        if (returnStatus === 'approved') {
            const [items] = await connection.execute(
                'SELECT product_id, quantity FROM customer_return_items WHERE return_id = ?',
                [req.params.id]
            );

            const branchId = req.user?.branch_id;
            for (const item of items) {
                if (branchId) {
                    await connection.execute(
                        'UPDATE branch_stock SET stock = stock - ? WHERE branch_id = ? AND product_id = ?',
                        [item.quantity, branchId, item.product_id]
                    );
                }
                await connection.execute(
                    'UPDATE products SET stock = stock - ? WHERE id = ?',
                    [item.quantity, item.product_id]
                );
            }
        }

        await connection.execute('DELETE FROM customer_returns WHERE id = ?', [req.params.id]);

        await connection.commit();
        res.json({ success: true, message: 'Return deleted successfully' });

    } catch (error) {
        await connection.rollback();
        console.error('Delete return error:', error);
        res.status(500).json({ success: false, message: 'Failed to delete return' });
    } finally {
        connection.release();
    }
});

module.exports = router;
