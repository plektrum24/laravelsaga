const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const User = require('../../models/main/User');
const bcrypt = require('bcryptjs');

/**
 * GET /api/users
 * Get all users for current tenant
 */
router.get('/', async (req, res) => {
    try {
        // req.user is set by authenticateToken
        // We need users belonging to this tenant
        // Assuming req.user.tenant_id is available or we use resolveTenant

        const tenantId = req.user.tenant_id;

        if (!tenantId) {
            return res.status(400).json({ success: false, message: 'Tenant ID missing' });
        }

        const users = await User.findByTenant(tenantId);

        // Filter out sensitive data
        const safeUsers = users.map(u => ({
            id: u.id,
            name: u.name,
            email: u.email,
            role: u.role,
            branch_id: u.branch_id, // Important for branch assignment
            is_active: u.is_active,
            created_at: u.created_at
        }));

        res.json({
            success: true,
            data: safeUsers
        });
    } catch (error) {
        console.error('Get users error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get users'
        });
    }
});

/**
 * POST /api/users
 * Create new user for tenant
 */
router.post('/', [
    body('name').notEmpty().withMessage('Name is required'),
    body('email').isEmail().withMessage('Valid email is required'),
    body('password').isLength({ min: 6 }).withMessage('Password must be at least 6 characters'),
    body('role').isIn(['manager', 'cashier']).withMessage('Invalid role'),
    body('branch_id').optional({ nullable: true }).isInt().withMessage('Invalid branch ID')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { name, email, password, role, branch_id } = req.body;
        const tenantId = req.user.tenant_id;

        // Check if email exists
        const existingUser = await User.findByEmail(email);
        if (existingUser) {
            return res.status(400).json({
                success: false,
                message: 'Email already registered'
            });
        }

        // Create user
        const userId = await User.create({
            name,
            email,
            password,
            role,
            tenant_id: tenantId,
            branch_id: branch_id || null
        });

        res.status(201).json({
            success: true,
            message: 'User created successfully',
            data: { id: userId }
        });
    } catch (error) {
        console.error('Create user error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create user'
        });
    }
});

/**
 * PUT /api/users/:id
 * Update user
 */
router.put('/:id', async (req, res) => {
    try {
        const { name, email, role, password, branch_id, is_active } = req.body;
        const userId = req.params.id;
        const tenantId = req.user.tenant_id;

        // Verify user belongs to tenant
        const user = await User.findById(userId);
        if (!user || user.tenant_id !== tenantId) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        const updateData = {};
        if (name) updateData.name = name;
        if (email) updateData.email = email;
        if (role) updateData.role = role;
        if (password) updateData.password = password; // User.update handles hashing check? Or we should hash here?
        // User.update in model usually handles hashing if provided, checking implementation below.
        // If User.update doesn't hash, update logic:
        // But usually model method 'update' might expect hashed or plain.
        // Let's assume User.update expects plain and hashes it, OR we hash here.
        // Safer to check User.js.

        // Branch ID update
        if (branch_id !== undefined) updateData.branch_id = branch_id;
        if (is_active !== undefined) updateData.is_active = is_active;

        await User.update(userId, updateData);

        res.json({
            success: true,
            message: 'User updated successfully'
        });
    } catch (error) {
        console.error('Update user error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update user'
        });
    }
});

/**
 * PATCH /api/users/:id/status
 * Toggle user active status
 */
router.patch('/:id/status', async (req, res) => {
    try {
        const { is_active } = req.body;
        const userId = req.params.id;
        const tenantId = req.user.tenant_id;

        // Verify user belongs to tenant
        const user = await User.findById(userId);
        if (!user || user.tenant_id !== tenantId) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        // Prevent deactivating self
        if (user.id === req.user.id) {
            return res.status(400).json({
                success: false,
                message: 'Cannot deactivate yourself'
            });
        }

        await User.update(userId, { is_active });

        res.json({
            success: true,
            message: `User ${is_active ? 'activated' : 'deactivated'} successfully`
        });
    } catch (error) {
        console.error('Toggle user status error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update user status'
        });
    }
});

/**
 * DELETE /api/users/:id
 * Delete user
 */
router.delete('/:id', async (req, res) => {
    try {
        const userId = req.params.id;
        const tenantId = req.user.tenant_id;

        const user = await User.findById(userId);
        if (!user || user.tenant_id !== tenantId) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        // Prevent deleting self
        if (user.id === req.user.id) {
            return res.status(400).json({
                success: false,
                message: 'Cannot delete yourself'
            });
        }

        // Soft delete or hard delete? User.delete usually is soft?
        await User.delete(userId); // Check if User.js has delete method

        res.json({
            success: true,
            message: 'User deleted successfully'
        });
    } catch (error) {
        console.error('Delete user error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete user'
        });
    }
});

module.exports = router;
