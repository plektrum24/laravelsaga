const express = require('express');
const router = express.Router();
const User = require('../../models/main/User');
const Tenant = require('../../models/main/Tenant');

// Get all users
router.get('/', async (req, res) => {
    try {
        const users = await User.findAll();
        res.json({
            success: true,
            data: users
        });
    } catch (error) {
        console.error('Get users error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to fetch users'
        });
    }
});

// Create new user (Super Admin creating Tenant Owner or Staff)
router.post('/', async (req, res) => {
    try {
        const { name, email, password, role, tenant_id } = req.body;

        // Validation
        if (!name || !email || !password || !role) {
            return res.status(400).json({
                success: false,
                message: 'All fields are required'
            });
        }

        // Check if user exists
        const existingUser = await User.findByEmail(email);
        if (existingUser) {
            return res.status(400).json({
                success: false,
                message: 'Email already registered'
            });
        }

        // If tenant_id provided, verify it exists
        if (tenant_id) {
            const tenant = await Tenant.findById(tenant_id);
            if (!tenant) {
                return res.status(400).json({
                    success: false,
                    message: 'Invalid tenant ID'
                });
            }
        }

        // Sanitize tenant_id (handle empty string or "null" string)
        let finalTenantId = null;
        if (tenant_id && tenant_id !== '' && tenant_id !== 'null') {
            finalTenantId = tenant_id;
        }

        const newUser = await User.create({
            name,
            email,
            password,
            role,
            tenant_id: finalTenantId,
            is_active: true
        });

        res.status(201).json({
            success: true,
            message: 'User created successfully',
            data: newUser
        });
    } catch (error) {
        console.error('Create user error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to create user'
        });
    }
});

// Update user
router.put('/:id', async (req, res) => {
    try {
        const { id } = req.params;
        const updateData = req.body;

        const updatedUser = await User.update(id, updateData);

        if (!updatedUser) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        res.json({
            success: true,
            message: 'User updated successfully',
            data: updatedUser
        });
    } catch (error) {
        console.error('Update user error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update user'
        });
    }
});

// Delete user
router.delete('/:id', async (req, res) => {
    try {
        const { id } = req.params;
        const success = await User.delete(id);

        if (!success) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

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
