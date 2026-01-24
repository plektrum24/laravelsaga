const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const User = require('../models/main/User');
const Tenant = require('../models/main/Tenant');
const { generateToken, authenticateToken } = require('../middleware/auth');

/**
 * POST /api/auth/login
 * User login endpoint
 */
router.post('/login', [
    body('email').isEmail().withMessage('Valid email is required'),
    body('password').notEmpty().withMessage('Password is required')
], async (req, res) => {
    try {
        // Validate input
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { email, password } = req.body;

        // Find user
        const user = await User.findByEmail(email);
        if (!user) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password'
            });
        }

        // Check if user is active
        if (!user.is_active) {
            return res.status(401).json({
                success: false,
                message: 'Account is deactivated'
            });
        }

        // Verify password
        const isValidPassword = await User.verifyPassword(password, user.password);
        if (!isValidPassword) {
            return res.status(401).json({
                success: false,
                message: 'Invalid email or password'
            });
        }

        // Get tenant info if applicable
        let tenant = null;
        if (user.tenant_id) {
            tenant = await Tenant.findById(user.tenant_id);
            if (tenant && tenant.status !== 'active') {
                return res.status(401).json({
                    success: false,
                    message: 'Tenant account is suspended'
                });
            }

            // Check subscription expiry
            if (tenant) {
                const subscription = Tenant.isSubscriptionActive(tenant);
                if (!subscription.active) {
                    return res.status(401).json({
                        success: false,
                        message: 'SUBSCRIPTION_EXPIRED',
                        detail: 'Subscription has expired. Please contact administrator to renew.'
                    });
                }
            }
        }

        // Generate token
        const token = generateToken(user);

        // Determine redirect path based on role
        let redirectPath = '/dashboard.html';
        if (user.role === 'super_admin') {
            redirectPath = '/admin-dashboard.html';
        } else if (user.role === 'cashier') {
            redirectPath = '/pos.html';
        }

        // Calculate subscription info for warning
        let subscriptionInfo = null;
        if (tenant) {
            const subStatus = Tenant.isSubscriptionActive(tenant);
            subscriptionInfo = {
                days_left: subStatus.daysLeft,
                subscription_end: tenant.subscription_end,
                plan_type: tenant.plan_type
            };
        }

        res.json({
            success: true,
            message: 'Login successful',
            data: {
                token,
                user: {
                    id: user.id,
                    email: user.email,
                    name: user.name,
                    role: user.role,
                    tenant_id: user.tenant_id,
                    branch_id: user.branch_id
                },
                tenant: tenant ? {
                    id: tenant.id,
                    name: tenant.name,
                    code: tenant.code,
                    logo_url: tenant.logo_url,
                    subscription: subscriptionInfo
                } : null,
                redirectPath
            }
        });
    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({
            success: false,
            message: 'Login failed: ' + error.message,
            originalError: error.message
        });
    }
});

/**
 * GET /api/auth/me
 * Get current user info
 */
router.get('/me', authenticateToken, async (req, res) => {
    try {
        const user = await User.findById(req.user.id);
        if (!user) {
            return res.status(404).json({
                success: false,
                message: 'User not found'
            });
        }

        let tenant = null;
        if (user.tenant_id) {
            tenant = await Tenant.findById(user.tenant_id);
        }

        res.json({
            success: true,
            data: {
                user,
                tenant: tenant ? {
                    id: tenant.id,
                    name: tenant.name,
                    code: tenant.code,
                    logo_url: tenant.logo_url
                } : null
            }
        });
    } catch (error) {
        console.error('Get user error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get user info'
        });
    }
});

/**
 * POST /api/auth/change-password
 * Change user password
 */
router.post('/change-password', authenticateToken, [
    body('currentPassword').notEmpty().withMessage('Current password is required'),
    body('newPassword').isLength({ min: 6 }).withMessage('New password must be at least 6 characters')
], async (req, res) => {
    try {
        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { currentPassword, newPassword } = req.body;
        const user = await User.findByEmail(req.user.email);

        // Verify current password
        const isValidPassword = await User.verifyPassword(currentPassword, user.password);
        if (!isValidPassword) {
            return res.status(400).json({
                success: false,
                message: 'Current password is incorrect'
            });
        }

        // Update password
        await User.update(req.user.id, { password: newPassword });

        res.json({
            success: true,
            message: 'Password changed successfully'
        });
    } catch (error) {
        console.error('Change password error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to change password'
        });
    }
});

module.exports = router;
