const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

/**
 * GET /api/customers
 * List all customers with their current debt
 */
router.get('/', async (req, res) => {
    try {
        const [customers] = await req.tenantDb.execute(`
            SELECT c.*, 
                   COALESCE((
                       SELECT SUM(t.total_amount - COALESCE(t.payment_amount, 0))
                       FROM transactions t 
                       WHERE t.customer_id = c.id 
                         AND t.payment_status IN ('unpaid', 'partial', 'debt')
                   ), 0) as current_debt
            FROM customers c 
            ORDER BY c.name
        `);
        res.json({ success: true, data: customers });
    } catch (error) {
        console.error('Error fetching customers:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * GET /api/customers/:id
 * Get customer by ID
 */
router.get('/:id', async (req, res) => {
    try {
        const [customers] = await req.tenantDb.execute('SELECT * FROM customers WHERE id = ?', [req.params.id]);
        if (customers.length === 0) {
            return res.status(404).json({ success: false, message: 'Customer not found' });
        }
        res.json({ success: true, data: customers[0] });
    } catch (error) {
        console.error('Error fetching customer:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * POST /api/customers
 * Create new customer
 */
router.post('/', [
    body('name').notEmpty().withMessage('Name is required')
], async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ success: false, errors: errors.array() });
    }

    try {
        const { name, email, phone, address, credit_limit } = req.body;
        const [result] = await req.tenantDb.execute(
            'INSERT INTO customers (name, email, phone, address, credit_limit) VALUES (?, ?, ?, ?, ?)',
            [name, email, phone, address, credit_limit || 0]
        );
        res.status(201).json({ success: true, message: 'Customer created', data: { id: result.insertId, ...req.body } });
    } catch (error) {
        console.error('Error creating customer:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * PUT /api/customers/:id
 * Update customer
 */
router.put('/:id', [
    body('name').notEmpty().withMessage('Name is required')
], async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ success: false, errors: errors.array() });
    }

    try {
        const { name, email, phone, address, credit_limit } = req.body;
        await req.tenantDb.execute(
            'UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, credit_limit = ? WHERE id = ?',
            [name, email, phone, address, credit_limit || 0, req.params.id]
        );
        res.json({ success: true, message: 'Customer updated' });
    } catch (error) {
        console.error('Error updating customer:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * DELETE /api/customers/:id
 * Delete customer
 */
router.delete('/:id', async (req, res) => {
    try {
        await req.tenantDb.execute('DELETE FROM customers WHERE id = ?', [req.params.id]);
        res.json({ success: true, message: 'Customer deleted' });
    } catch (error) {
        console.error('Error deleting customer:', error);
        if (error.code === 'ER_ROW_IS_REFERENCED_2') {
            return res.status(400).json({ success: false, message: 'Customer cannot be deleted because they have associated transactions.' });
        }
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * GET /api/customers/:id/debt-history
 * Get unpaid transactions for a customer
 */
router.get('/:id/debt-history', async (req, res) => {
    try {
        const [transactions] = await req.tenantDb.execute(`
            SELECT id, invoice_number, created_at as date, due_date, total_amount, payment_amount, (total_amount - payment_amount) as remaining_debt, payment_status
            FROM transactions 
            WHERE customer_id = ? AND payment_status IN ('unpaid', 'partial', 'debt')
            ORDER BY due_date ASC
        `, [req.params.id]);

        res.json({ success: true, data: transactions });
    } catch (error) {
        console.error('Error fetching debt history:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

/**
 * POST /api/customers/import (Bulk Import)
 * Import customers from JSON array
 */
router.post('/import', async (req, res) => {
    try {
        const { customers } = req.body;

        if (!customers || !Array.isArray(customers) || customers.length === 0) {
            return res.status(400).json({ success: false, message: 'No customers data provided' });
        }

        let imported = 0;
        let skipped = 0;
        const errors = [];

        for (const customer of customers) {
            try {
                if (!customer.name || !customer.name.trim()) {
                    skipped++;
                    errors.push(`Skipped: Empty name`);
                    continue;
                }

                // Check if customer already exists by name or phone
                let existing = [];
                if (customer.phone) {
                    [existing] = await req.tenantDb.execute(
                        'SELECT id FROM customers WHERE name = ? OR phone = ?',
                        [customer.name.trim(), customer.phone.trim()]
                    );
                } else {
                    [existing] = await req.tenantDb.execute(
                        'SELECT id FROM customers WHERE name = ?',
                        [customer.name.trim()]
                    );
                }

                if (existing.length > 0) {
                    skipped++;
                    continue; // Already exists, skip
                }

                await req.tenantDb.execute(
                    'INSERT INTO customers (name, email, phone, address, credit_limit) VALUES (?, ?, ?, ?, ?)',
                    [
                        customer.name.trim(),
                        customer.email || null,
                        customer.phone || null,
                        customer.address || null,
                        customer.credit_limit || 0
                    ]
                );
                imported++;
            } catch (err) {
                skipped++;
                errors.push(`Error importing ${customer.name}: ${err.message}`);
            }
        }

        res.json({
            success: true,
            message: `Import completed: ${imported} imported, ${skipped} skipped`,
            data: { imported, skipped, errors: errors.slice(0, 10) }
        });
    } catch (error) {
        console.error('Error importing customers:', error);
        res.status(500).json({ success: false, message: 'Import failed: ' + error.message });
    }
});

module.exports = router;
