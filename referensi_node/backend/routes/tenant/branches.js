const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');

/**
 * GET /api/branches
 * Get all branches
 */
router.get('/', async (req, res) => {
    try {
        const [branches] = await req.tenantDb.execute(`
            SELECT b.*, 
                   (SELECT COUNT(*) FROM branch_stock bs WHERE bs.branch_id = b.id) as product_count,
                   (SELECT COALESCE(SUM(bs.stock), 0) FROM branch_stock bs WHERE bs.branch_id = b.id) as total_stock
            FROM branches b
            WHERE b.is_active = true
            ORDER BY b.is_main DESC, b.name ASC
        `);

        res.json({
            success: true,
            data: branches
        });
    } catch (error) {
        console.error('Get branches error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get branches'
        });
    }
});

/**
 * GET /api/branches/:id
 * Get branch by ID with stock details
 */
router.get('/:id', async (req, res) => {
    try {
        const [branches] = await req.tenantDb.execute(
            'SELECT * FROM branches WHERE id = ? AND is_active = true',
            [req.params.id]
        );

        if (branches.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Branch not found'
            });
        }

        // Get stock summary for this branch
        const [stockSummary] = await req.tenantDb.execute(`
            SELECT 
                COUNT(*) as total_products,
                COALESCE(SUM(bs.stock), 0) as total_stock,
                COUNT(CASE WHEN bs.stock <= 0 THEN 1 END) as out_of_stock,
                COUNT(CASE WHEN bs.stock > 0 AND bs.stock <= bs.min_stock THEN 1 END) as low_stock
            FROM branch_stock bs
            WHERE bs.branch_id = ?
        `, [req.params.id]);

        res.json({
            success: true,
            data: {
                ...branches[0],
                stock_summary: stockSummary[0]
            }
        });
    } catch (error) {
        console.error('Get branch error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get branch'
        });
    }
});

/**
 * POST /api/branches
 * Create new branch
 */
router.post('/', [
    body('name').notEmpty().withMessage('Branch name is required')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { name, address, phone } = req.body;

        const connection = await req.tenantDb.getConnection();
        try {
            await connection.beginTransaction();

            // Generate Branch Code (CAB-XXX)
            // Find latest code pattern to determine starting number
            const [lastBranch] = await connection.execute(
                "SELECT code FROM branches WHERE code LIKE 'CAB-%' ORDER BY id DESC LIMIT 1"
            );

            let nextNum = 1;
            if (lastBranch.length > 0) {
                const lastCode = lastBranch[0].code;
                const match = lastCode.match(/CAB-(\d+)/);
                if (match) {
                    nextNum = parseInt(match[1]) + 1;
                }
            }

            let code = `CAB-${String(nextNum).padStart(3, '0')}`;

            // Ensure uniqueness (simple loop)
            let isUnique = false;
            let attempts = 0;

            while (!isUnique && attempts < 10) {
                const [existing] = await connection.execute(
                    'SELECT id FROM branches WHERE code = ?',
                    [code]
                );

                if (existing.length === 0) {
                    isUnique = true;
                } else {
                    nextNum++;
                    code = `CAB-${String(nextNum).padStart(3, '0')}`;
                    attempts++;
                }
            }

            if (!isUnique) {
                throw new Error('Failed to generate unique branch code after multiple attempts');
            }

            const [result] = await connection.execute(`
                INSERT INTO branches (name, code, address, phone, is_main)
                VALUES (?, ?, ?, ?, FALSE)
            `, [name, code, address || null, phone || null]);

            // Copy all products to new branch with 0 stock
            // FIX: Don't select min_stock from products as it might not exist there. Use default 5.
            await connection.execute(`
                INSERT INTO branch_stock (branch_id, product_id, stock, min_stock)
                SELECT ?, id, 0, 5 FROM products WHERE is_active = true
            `, [result.insertId]);

            await connection.commit();

            res.status(201).json({
                success: true,
                message: 'Branch created successfully',
                data: {
                    id: result.insertId,
                    name,
                    code: code,
                    address,
                    phone
                }
            });
        } catch (error) {
            await connection.rollback();
            throw error;
        } finally {
            connection.release();
        }
    } catch (error) {
        console.error('Create branch error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create branch: ' + error.message
        });
    }
});

/**
 * PUT /api/branches/:id
 * Update branch
 */
router.put('/:id', async (req, res) => {
    try {
        const { name, address, phone } = req.body;
        const branchId = req.params.id;

        // Check if branch exists
        const [existing] = await req.tenantDb.execute(
            'SELECT * FROM branches WHERE id = ?',
            [branchId]
        );

        if (existing.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Branch not found'
            });
        }

        await req.tenantDb.execute(`
            UPDATE branches 
            SET name = COALESCE(?, name),
                address = COALESCE(?, address),
                phone = COALESCE(?, phone)
            WHERE id = ?
        `, [name, address, phone, branchId]);

        const [updated] = await req.tenantDb.execute(
            'SELECT * FROM branches WHERE id = ?',
            [branchId]
        );

        res.json({
            success: true,
            message: 'Branch updated successfully',
            data: updated[0]
        });
    } catch (error) {
        console.error('Update branch error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update branch'
        });
    }
});

/**
 * DELETE /api/branches/:id
 * Deactivate branch (soft delete)
 */
router.delete('/:id', async (req, res) => {
    try {
        const branchId = req.params.id;

        // Check if it's main branch
        const [branch] = await req.tenantDb.execute(
            'SELECT * FROM branches WHERE id = ?',
            [branchId]
        );

        if (branch.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Branch not found'
            });
        }

        if (branch[0].is_main) {
            return res.status(400).json({
                success: false,
                message: 'Cannot delete main branch'
            });
        }

        // Check for pending transfers
        const [pendingTransfers] = await req.tenantDb.execute(`
            SELECT COUNT(*) as count FROM stock_transfers 
            WHERE (from_branch_id = ? OR to_branch_id = ?) 
            AND status IN ('pending', 'in_transit')
        `, [branchId, branchId]);

        if (pendingTransfers[0].count > 0) {
            return res.status(400).json({
                success: false,
                message: 'Cannot delete branch with pending transfers'
            });
        }

        // Soft delete
        await req.tenantDb.execute(
            'UPDATE branches SET is_active = false WHERE id = ?',
            [branchId]
        );

        res.json({
            success: true,
            message: 'Branch deleted successfully'
        });
    } catch (error) {
        console.error('Delete branch error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete branch'
        });
    }
});

/**
 * GET /api/branches/:id/stock
 * Get stock for specific branch
 */
router.get('/:id/stock', async (req, res) => {
    try {
        const branchId = req.params.id;
        const { search, category_id, low_stock } = req.query;

        let query = `
            SELECT bs.*, p.name, p.sku, p.category_id, c.name as category_name,
                   u.name as base_unit_name
            FROM branch_stock bs
            JOIN products p ON bs.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN units u ON p.base_unit_id = u.id
            WHERE bs.branch_id = ? AND p.is_active = true
        `;
        const params = [branchId];

        if (search) {
            query += ' AND (p.name LIKE ? OR p.sku LIKE ?)';
            params.push(`%${search}%`, `%${search}%`);
        }

        if (category_id) {
            query += ' AND p.category_id = ?';
            params.push(category_id);
        }

        if (low_stock === 'true') {
            query += ' AND bs.stock <= bs.min_stock';
        }

        query += ' ORDER BY p.name ASC';

        const [stock] = await req.tenantDb.execute(query, params);

        res.json({
            success: true,
            data: stock
        });
    } catch (error) {
        console.error('Get branch stock error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get branch stock'
        });
    }
});

module.exports = router;
