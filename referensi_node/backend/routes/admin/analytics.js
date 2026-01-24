const express = require('express');
const router = express.Router();
const Tenant = require('../../models/main/Tenant');
const { requireRole } = require('../../middleware/auth');
const { getMainPool, getTenantPool } = require('../../config/database');

// All routes require super_admin role
router.use(requireRole('super_admin'));

/**
 * GET /api/admin/analytics/overview
 * Get global overview statistics
 */
router.get('/overview', async (req, res) => {
    try {
        const pool = await getMainPool();

        // Get counts
        const [[tenantCount]] = await pool.execute(
            'SELECT COUNT(*) as total, SUM(status = "active") as active FROM tenants'
        );

        const [[userCount]] = await pool.execute(
            'SELECT COUNT(*) as total FROM users WHERE role != "super_admin"'
        );

        res.json({
            success: true,
            data: {
                tenants: {
                    total: tenantCount.total,
                    active: parseInt(tenantCount.active) || 0
                },
                users: {
                    total: userCount.total
                }
            }
        });
    } catch (error) {
        console.error('Get overview error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get overview'
        });
    }
});

/**
 * GET /api/admin/analytics/revenue
 * Get combined revenue from all tenants
 */
router.get('/revenue', async (req, res) => {
    try {
        const { period = 'week' } = req.query;
        const tenants = await Tenant.findAll();

        let totalRevenue = 0;
        let totalTransactions = 0;
        const revenueByTenant = [];
        const dailyRevenue = {};
        const productSales = {}; // Aggregate top products

        // Calculate date range
        const now = new Date();
        let startDate;
        if (period === 'week') {
            startDate = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
        } else if (period === 'month') {
            startDate = new Date(now.getFullYear(), now.getMonth(), 1);
        } else {
            startDate = new Date(now.getFullYear(), 0, 1);
        }

        for (const tenant of tenants) {
            if (tenant.status !== 'active') continue;

            try {
                const tenantPool = await getTenantPool(tenant.database_name);

                // Get total revenue and transaction count
                const [[stats]] = await tenantPool.execute(`
                    SELECT COALESCE(SUM(total_amount), 0) as total_revenue,
                           COUNT(*) as transaction_count
                    FROM transactions 
                    WHERE status = 'completed' AND created_at >= ?
                `, [startDate]);

                const tenantRevenue = parseFloat(stats.total_revenue);
                const tenantTransactions = parseInt(stats.transaction_count);
                totalRevenue += tenantRevenue;
                totalTransactions += tenantTransactions;

                revenueByTenant.push({
                    tenant_id: tenant.id,
                    tenant_name: tenant.name,
                    revenue: tenantRevenue,
                    transactions: tenantTransactions
                });

                // Get top products from this tenant
                try {
                    const [products] = await tenantPool.execute(`
                        SELECT p.name, SUM(ti.quantity) as total_qty
                        FROM transaction_items ti
                        JOIN products p ON ti.product_id = p.id
                        JOIN transactions t ON ti.transaction_id = t.id
                        WHERE t.status = 'completed' AND t.created_at >= ?
                        GROUP BY p.id, p.name
                        ORDER BY total_qty DESC
                        LIMIT 10
                    `, [startDate]);

                    products.forEach(p => {
                        productSales[p.name] = (productSales[p.name] || 0) + parseInt(p.total_qty);
                    });
                } catch (prodErr) {
                    // Products table might not exist or be empty
                }

                // Get daily breakdown
                const [dailyData] = await tenantPool.execute(`
                    SELECT DATE(created_at) as date, SUM(total_amount) as amount
                    FROM transactions 
                    WHERE status = 'completed' AND created_at >= ?
                    GROUP BY DATE(created_at)
                `, [startDate]);

                dailyData.forEach(row => {
                    const dateKey = row.date.toISOString().split('T')[0];
                    dailyRevenue[dateKey] = (dailyRevenue[dateKey] || 0) + parseFloat(row.amount);
                });
            } catch (err) {
                console.error(`Error getting revenue for tenant ${tenant.code}:`, err);
            }
        }

        // Convert daily revenue to array
        const dailyRevenueArray = Object.entries(dailyRevenue)
            .map(([date, amount]) => ({ date, amount }))
            .sort((a, b) => a.date.localeCompare(b.date));

        // Convert product sales to sorted array (top 10)
        const topProducts = Object.entries(productSales)
            .map(([name, quantity]) => ({ name, quantity }))
            .sort((a, b) => b.quantity - a.quantity)
            .slice(0, 10);

        res.json({
            success: true,
            data: {
                totalRevenue,
                totalTransactions,
                revenueByTenant: revenueByTenant.sort((a, b) => b.revenue - a.revenue),
                dailyRevenue: dailyRevenueArray,
                topProducts,
                period
            }
        });
    } catch (error) {
        console.error('Get revenue error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get revenue analytics'
        });
    }
});

/**
 * GET /api/admin/analytics/tenants-map
 * Get tenant locations for map display
 */
router.get('/tenants-map', async (req, res) => {
    try {
        const pool = await getMainPool();
        const [tenants] = await pool.execute(`
      SELECT id, name, code, address, status
      FROM tenants
      WHERE status = 'active'
    `);

        res.json({
            success: true,
            data: tenants
        });
    } catch (error) {
        console.error('Get tenants map error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get tenant locations'
        });
    }
});

module.exports = router;
