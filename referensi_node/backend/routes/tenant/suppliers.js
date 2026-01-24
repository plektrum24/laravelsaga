const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

// Validation rules
const supplierValidation = [
    body('name').notEmpty().withMessage('Name is required').trim(),
    body('phone').optional().trim(),
    body('contact_person').optional().trim(),
    body('address').optional().trim()
];

// GET /api/suppliers
router.get('/', async (req, res) => {
    try {
        const { search, page = 1, limit = 50 } = req.query;
        const offset = (page - 1) * limit;
        const params = [];

        let sql = 'SELECT * FROM suppliers WHERE 1=1';

        if (search) {
            sql += ' AND (name LIKE ? OR contact_person LIKE ? OR phone LIKE ?)';
            params.push(`%${search}%`, `%${search}%`, `%${search}%`);
        }

        sql += ' ORDER BY created_at DESC LIMIT ? OFFSET ?';
        params.push(parseInt(limit), offset);

        const [suppliers] = await req.tenantDb.execute(sql, params);

        // Get total count
        const [countResult] = await req.tenantDb.execute('SELECT COUNT(*) as total FROM suppliers');

        res.json({
            success: true,
            data: suppliers,
            pagination: {
                total: countResult[0].total,
                page: parseInt(page),
                limit: parseInt(limit)
            }
        });
    } catch (error) {
        console.error('Error fetching suppliers:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

// GET /api/suppliers/:id
router.get('/:id', async (req, res) => {
    try {
        const [suppliers] = await req.tenantDb.execute('SELECT * FROM suppliers WHERE id = ?', [req.params.id]);

        if (suppliers.length === 0) {
            return res.status(404).json({ success: false, message: 'Supplier not found' });
        }

        res.json({ success: true, data: suppliers[0] });
    } catch (error) {
        console.error('Error fetching supplier:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

// POST /api/suppliers
router.post('/', supplierValidation, async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ success: false, errors: errors.array() });
    }

    try {
        const { name, contact_person, phone, address } = req.body;

        const [result] = await req.tenantDb.execute(
            'INSERT INTO suppliers (name, contact_person, phone, address) VALUES (?, ?, ?, ?)',
            [name, contact_person, phone, address]
        );

        res.status(201).json({
            success: true,
            message: 'Supplier created successfully',
            data: { id: result.insertId, ...req.body }
        });
    } catch (error) {
        console.error('Error creating supplier:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

// PUT /api/suppliers/:id
router.put('/:id', supplierValidation, async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) {
        return res.status(400).json({ success: false, errors: errors.array() });
    }

    try {
        const { name, contact_person, phone, address } = req.body;

        const [result] = await req.tenantDb.execute(
            'UPDATE suppliers SET name = ?, contact_person = ?, phone = ?, address = ? WHERE id = ?',
            [name, contact_person, phone, address, req.params.id]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Supplier not found' });
        }

        res.json({
            success: true,
            message: 'Supplier updated successfully',
            data: { id: req.params.id, ...req.body }
        });
    } catch (error) {
        console.error('Error updating supplier:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

// DELETE /api/suppliers/:id
router.delete('/:id', async (req, res) => {
    try {
        const [result] = await req.tenantDb.execute('DELETE FROM suppliers WHERE id = ?', [req.params.id]);

        if (result.affectedRows === 0) {
            return res.status(404).json({ success: false, message: 'Supplier not found' });
        }

        res.json({ success: true, message: 'Supplier deleted successfully' });
    } catch (error) {
        // Check for foreign key constraint errors (e.g. if supplier has products)
        if (error.code === 'ER_ROW_IS_REFERENCED_2') {
            return res.status(400).json({ success: false, message: 'Cannot delete supplier because it has associated data' });
        }
        console.error('Error deleting supplier:', error);
        res.status(500).json({ success: false, message: 'Internal server error' });
    }
});

// POST /api/suppliers/import (Bulk Import)
router.post('/import', async (req, res) => {
    try {
        const { suppliers } = req.body;

        if (!suppliers || !Array.isArray(suppliers) || suppliers.length === 0) {
            return res.status(400).json({ success: false, message: 'No suppliers data provided' });
        }

        let imported = 0;
        let skipped = 0;
        const errors = [];

        for (const supplier of suppliers) {
            try {
                if (!supplier.name || !supplier.name.trim()) {
                    skipped++;
                    errors.push(`Skipped: Empty name`);
                    continue;
                }

                // Check if supplier already exists by name
                const [existing] = await req.tenantDb.execute(
                    'SELECT id FROM suppliers WHERE name = ?',
                    [supplier.name.trim()]
                );

                if (existing.length > 0) {
                    skipped++;
                    continue; // Already exists, skip
                }

                await req.tenantDb.execute(
                    'INSERT INTO suppliers (name, contact_person, phone, address) VALUES (?, ?, ?, ?)',
                    [
                        supplier.name.trim(),
                        supplier.contact_person || null,
                        supplier.phone || null,
                        supplier.address || null
                    ]
                );
                imported++;
            } catch (err) {
                skipped++;
                errors.push(`Error importing ${supplier.name}: ${err.message}`);
            }
        }

        res.json({
            success: true,
            message: `Import completed: ${imported} imported, ${skipped} skipped`,
            data: { imported, skipped, errors: errors.slice(0, 10) }
        });
    } catch (error) {
        console.error('Error importing suppliers:', error);
        res.status(500).json({ success: false, message: 'Import failed: ' + error.message });
    }
});

module.exports = router;
