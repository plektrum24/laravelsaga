const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const Tenant = require('../../models/main/Tenant');
const User = require('../../models/main/User');
const { requireRole } = require('../../middleware/auth');
const { createTenantDatabase } = require('../../utils/dbGenerator');

// All routes require super_admin role
router.use(requireRole('super_admin'));

/**
 * GET /api/admin/tenants
 * Get all tenants with stats
 */
router.get('/', async (req, res) => {
    try {
        const tenants = await Tenant.findAllWithStats();
        res.json({
            success: true,
            data: tenants
        });
    } catch (error) {
        console.error('Get tenants error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get tenants'
        });
    }
});

/**
 * GET /api/admin/tenants/:id
 * Get tenant by ID
 */
router.get('/:id', async (req, res) => {
    try {
        const tenant = await Tenant.findById(req.params.id);
        if (!tenant) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found'
            });
        }

        // Get users for this tenant
        const users = await User.findByTenant(tenant.id);

        res.json({
            success: true,
            data: { ...tenant, users }
        });
    } catch (error) {
        console.error('Get tenant error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get tenant'
        });
    }
});

/**
 * POST /api/admin/tenants
 * Create new tenant (with database)
 */
router.post('/', [
    body('name').notEmpty().withMessage('Tenant name is required'),
    body('code').notEmpty().isAlphanumeric().withMessage('Tenant code must be alphanumeric'),
    body('ownerEmail').isEmail().withMessage('Valid owner email is required'),
    body('ownerName').notEmpty().withMessage('Owner name is required'),
    body('ownerPassword').isLength({ min: 6 }).withMessage('Password must be at least 6 characters')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { name, code, address, phone, logo_url, ownerEmail, ownerName, ownerPassword } = req.body;

        // Check if code already exists
        const existingTenant = await Tenant.findByCode(code);
        if (existingTenant) {
            return res.status(400).json({
                success: false,
                message: 'Tenant code already exists'
            });
        }

        // Check if owner email already exists
        const existingUser = await User.findByEmail(ownerEmail);
        if (existingUser) {
            return res.status(400).json({
                success: false,
                message: 'Email already registered'
            });
        }

        // Create tenant
        const tenant = await Tenant.create({ name, code, address, phone, logo_url });

        // Create tenant database
        await createTenantDatabase(tenant.database_name);

        // Create tenant owner user
        const owner = await User.create({
            email: ownerEmail,
            password: ownerPassword,
            name: ownerName,
            role: 'tenant_owner',
            tenant_id: tenant.id
        });

        res.status(201).json({
            success: true,
            message: 'Tenant created successfully',
            data: {
                tenant,
                owner: { id: owner.id, email: owner.email, name: owner.name }
            }
        });
    } catch (error) {
        console.error('Create tenant error:', error);

        // DEBUG: Write error to file to help AI diagnose
        const fs = require('fs');
        const path = require('path');
        const logPath = path.join(__dirname, '../../server_error.log');
        const logContent = `[${new Date().toISOString()}] ${error.message}\n${error.stack}\n\n`;
        fs.appendFileSync(logPath, logContent);

        res.status(500).json({
            success: false,
            message: 'Failed to create tenant: ' + error.message
        });
    }
});

/**
 * PUT /api/admin/tenants/:id
 * Update tenant
 */
router.put('/:id', async (req, res) => {
    try {
        const tenant = await Tenant.update(req.params.id, req.body);
        if (!tenant) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found or no changes made'
            });
        }

        res.json({
            success: true,
            message: 'Tenant updated successfully',
            data: tenant
        });
    } catch (error) {
        console.error('Update tenant error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update tenant'
        });
    }
});

/**
 * PATCH /api/admin/tenants/:id/status
 * Change tenant status (active/suspended/inactive)
 */
router.patch('/:id/status', [
    body('status').isIn(['active', 'suspended', 'inactive']).withMessage('Invalid status')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const tenant = await Tenant.update(req.params.id, { status: req.body.status });
        if (!tenant) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found'
            });
        }

        res.json({
            success: true,
            message: `Tenant ${req.body.status === 'active' ? 'activated' : 'suspended'} successfully`,
            data: tenant
        });
    } catch (error) {
        console.error('Update tenant status error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update tenant status'
        });
    }
});

/**
 * POST /api/admin/tenants/:id/reset-password
 * Reset tenant owner password
 */
router.post('/:id/reset-password', [
    body('newPassword').isLength({ min: 6 }).withMessage('Password must be at least 6 characters')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const tenant = await Tenant.findById(req.params.id);
        if (!tenant) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found'
            });
        }

        // Find tenant owner
        const users = await User.findByTenant(tenant.id);
        const owner = users.find(u => u.role === 'tenant_owner');

        if (!owner) {
            return res.status(404).json({
                success: false,
                message: 'Tenant owner not found'
            });
        }

        // Update password
        await User.update(owner.id, { password: req.body.newPassword });

        res.json({
            success: true,
            message: 'Password reset successfully'
        });
    } catch (error) {
        console.error('Reset password error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to reset password'
        });
    }
});

/**
 * DELETE /api/admin/tenants/:id
 * Delete tenant (soft delete - sets status to inactive)
 */
router.delete('/:id', async (req, res) => {
    try {
        const tenantId = req.params.id;

        const tenant = await Tenant.findById(tenantId);
        if (!tenant) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found'
            });
        }

        // Get all users for this tenant
        const users = await User.findByTenant(tenantId);

        // Delete all users belonging to this tenant
        for (const user of users) {
            await User.delete(user.id);
        }

        // Delete/deactivate the tenant (soft delete by updating status to 'deleted')
        await Tenant.update(tenantId, { status: 'inactive' });

        res.json({
            success: true,
            message: `Tenant "${tenant.name}" and ${users.length} associated users have been deactivated`
        });
    } catch (error) {
        console.error('Delete tenant error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to delete tenant'
        });
    }
});

/**
 * PATCH /api/admin/tenants/:id/subscription
 * Extend tenant subscription
 */
router.patch('/:id/subscription', [
    body('plan_type').isIn(['trial', '1_month', '3_months', '6_months', '1_year', 'lifetime', 'custom']).withMessage('Invalid plan type')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { plan_type, custom_end_date } = req.body;
        let tenant;

        if (plan_type === 'custom') {
            // Handle custom date
            if (!custom_end_date) {
                return res.status(400).json({
                    success: false,
                    message: 'Custom end date is required'
                });
            }
            tenant = await Tenant.update(req.params.id, {
                subscription_start: new Date().toISOString().split('T')[0],
                subscription_end: custom_end_date,
                plan_type: 'custom'
            });
        } else {
            tenant = await Tenant.extendSubscription(req.params.id, plan_type);
        }

        if (!tenant) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found'
            });
        }

        // Calculate friendly message
        const planNames = {
            'trial': 'Trial (30 days)',
            '1_month': '1 Month',
            '3_months': '3 Months',
            '6_months': '6 Months',
            '1_year': '1 Year',
            'lifetime': 'Lifetime',
            'custom': `Custom (until ${custom_end_date})`
        };

        res.json({
            success: true,
            message: `Subscription extended to ${planNames[plan_type]}`,
            data: tenant
        });
    } catch (error) {
        console.error('Extend subscription error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to extend subscription'
        });
    }
});

module.exports = router;
