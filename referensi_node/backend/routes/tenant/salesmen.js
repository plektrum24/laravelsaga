const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const { body, validationResult } = require('express-validator');

// GET /api/salesmen
router.get('/', async (req, res) => {
    try {
        const { search, limit = 50, page = 1 } = req.query;
        const offset = (page - 1) * limit;

        let sql = `SELECT id, name, phone, area, username, is_active, created_at FROM salesmen WHERE 1=1`;
        const params = [];

        if (search) {
            sql += ` AND (name LIKE ? OR area LIKE ? OR username LIKE ?)`;
            params.push(`%${search}%`, `%${search}%`, `%${search}%`);
        }

        sql += ` ORDER BY name ASC LIMIT ? OFFSET ?`;
        params.push(parseInt(limit), parseInt(offset));

        const [rows] = await req.tenantDb.execute(sql, params);

        // Count total
        let countSql = `SELECT COUNT(*) as total FROM salesmen WHERE 1=1`;
        const countParams = [];
        if (search) {
            countSql += ` AND (name LIKE ? OR area LIKE ? OR username LIKE ?)`;
            countParams.push(`%${search}%`, `%${search}%`, `%${search}%`);
        }
        const [countResult] = await req.tenantDb.execute(countSql, countParams);

        res.json({
            success: true,
            data: rows,
            pagination: {
                total: countResult[0].total,
                page: parseInt(page),
                limit: parseInt(limit)
            }
        });
    } catch (error) {
        console.error('Get salesmen error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// POST /api/salesmen
router.post('/', [
    body('name').notEmpty().withMessage('Nama wajib diisi'),
    body('username').notEmpty().withMessage('Username wajib diisi'),
    body('password').notEmpty().withMessage('Password wajib diisi').isLength({ min: 6 })
], async (req, res) => {
    const errors = validationResult(req);
    if (!errors.isEmpty()) return res.status(400).json({ success: false, errors: errors.array() });

    try {
        const { name, phone, area, username, password } = req.body;

        // Check if username exists
        const [existing] = await req.tenantDb.execute('SELECT id FROM salesmen WHERE username = ?', [username]);
        if (existing.length > 0) return res.status(400).json({ success: false, message: 'Username sudah digunakan' });

        const hashedPassword = await bcrypt.hash(password, 10);

        await req.tenantDb.execute(
            'INSERT INTO salesmen (tenant_id, name, phone, area, username, password_hash) VALUES (?, ?, ?, ?, ?, ?)',
            [req.user.tenant_id, name, phone, area, username, hashedPassword]
        );

        res.json({ success: true, message: 'Salesman berhasil ditambahkan' });
    } catch (error) {
        console.error('Create salesman error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// PUT /api/salesmen/:id
router.put('/:id', async (req, res) => {
    try {
        const { name, phone, area, password, is_active } = req.body;
        const { id } = req.params;

        let sql = 'UPDATE salesmen SET name=?, phone=?, area=?, is_active=?';
        const params = [name, phone, area, is_active];

        if (password) {
            const hashedPassword = await bcrypt.hash(password, 10);
            sql += ', password_hash=?';
            params.push(hashedPassword);
        }

        sql += ' WHERE id=?';
        params.push(id);

        await req.tenantDb.execute(sql, params);

        res.json({ success: true, message: 'Data salesman diperbarui' });
    } catch (error) {
        console.error('Update salesman error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

// DELETE /api/salesmen/:id
router.delete('/:id', async (req, res) => {
    try {
        // Check dependencies (transactions)
        const [tx] = await req.tenantDb.execute('SELECT id FROM transactions WHERE salesman_id = ? LIMIT 1', [req.params.id]);
        if (tx.length > 0) {
            return res.status(400).json({ success: false, message: 'Tidak dapat menghapus salesman yang memiliki transaksi. Nonaktifkan saja akunnya.' });
        }

        await req.tenantDb.execute('DELETE FROM salesmen WHERE id = ?', [req.params.id]);
        res.json({ success: true, message: 'Salesman dihapus' });
    } catch (error) {
        console.error('Delete salesman error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

module.exports = router;
