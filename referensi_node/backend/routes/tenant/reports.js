const express = require('express');
const router = express.Router();

/**
 * GET /api/reports/sales
 * Get sales report
 */
router.get('/sales', async (req, res) => {
    try {
        const { date_from, date_to, group_by = 'day', branch_id } = req.query;

        let dateFormat;
        switch (group_by) {
            case 'month':
                dateFormat = '%Y-%m';
                break;
            case 'week':
                dateFormat = '%Y-%u';
                break;
            default:
                dateFormat = '%Y-%m-%d';
        }

        let sql = `
      SELECT 
        DATE_FORMAT(created_at, '${dateFormat}') as period,
        COUNT(*) as total_transactions,
        SUM(total_amount) as total_sales,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_sales,
        SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as cancelled_amount
      FROM transactions
      WHERE 1=1
    `;
        const params = [];

        if (branch_id) {
            sql += ' AND branch_id = ?';
            params.push(branch_id);
        }

        if (date_from) {
            sql += ' AND DATE(created_at) >= ?';
            params.push(date_from);
        }

        if (date_to) {
            sql += ' AND DATE(created_at) <= ?';
            params.push(date_to);
        }

        sql += ` GROUP BY period ORDER BY period DESC`;

        const [data] = await req.tenantDb.execute(sql, params);

        // Get summary
        let summarySql = `
      SELECT 
        COUNT(*) as total_transactions,
        SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_sales,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_count,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count
      FROM transactions
      WHERE 1=1
    `;
        const summaryParams = [];

        if (branch_id) {
            summarySql += ' AND branch_id = ?';
            summaryParams.push(branch_id);
        }

        if (date_from) {
            summarySql += ' AND DATE(created_at) >= ?';
            summaryParams.push(date_from);
        }

        if (date_to) {
            summarySql += ' AND DATE(created_at) <= ?';
            summaryParams.push(date_to);
        }

        const [[summary]] = await req.tenantDb.execute(summarySql, summaryParams);

        res.json({
            success: true,
            data: {
                details: data,
                summary: {
                    total_transactions: parseInt(summary.total_transactions) || 0,
                    total_sales: parseFloat(summary.total_sales) || 0,
                    completed_count: parseInt(summary.completed_count) || 0,
                    cancelled_count: parseInt(summary.cancelled_count) || 0
                }
            }
        });
    } catch (error) {
        console.error('Get sales report error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get sales report'
        });
    }
});

/**
 * GET /api/reports/profit-loss
 * Get profit and loss report
 */
router.get('/profit-loss', async (req, res) => {
    try {
        const { date_from, date_to, branch_id } = req.query;

        // Profit-Loss Report:
        // - Qty = SUM of quantities sold (in base unit)
        // - Cost = SUM(ti.buy_price * ti.quantity) - buy_price stored per transaction
        // - Revenue = SUM(ti.subtotal) - actual sell amount
        // - Profit = Revenue - Cost
        let sql = `
      SELECT 
        ti.product_id,
        p.name as product_name,
        SUM(ti.quantity) as total_qty,
        SUM(ti.subtotal) as total_revenue,
        SUM(ti.quantity * COALESCE(ti.buy_price, 0)) as total_cost,
        SUM(ti.subtotal) - SUM(ti.quantity * COALESCE(ti.buy_price, 0)) as profit
      FROM transaction_items ti
      JOIN transactions t ON ti.transaction_id = t.id
      JOIN products p ON ti.product_id = p.id
      WHERE t.status = 'completed'
    `;
        const params = [];

        if (branch_id) {
            sql += ' AND t.branch_id = ?';
            params.push(branch_id);
        }

        if (date_from) {
            sql += ' AND DATE(t.created_at) >= ?';
            params.push(date_from);
        }

        if (date_to) {
            sql += ' AND DATE(t.created_at) <= ?';
            params.push(date_to);
        }

        sql += ' GROUP BY ti.product_id, p.name ORDER BY profit DESC';

        console.log('[PROFIT-LOSS] SQL:', sql);
        console.log('[PROFIT-LOSS] Params:', params);

        const [products] = await req.tenantDb.execute(sql, params);

        console.log('[PROFIT-LOSS] Results count:', products.length);
        if (products.length > 0) {
            console.log('[PROFIT-LOSS] First product:', products[0]);
        }

        // Calculate totals
        let totalRevenue = 0;
        let totalCost = 0;
        let totalProfit = 0;

        products.forEach(p => {
            totalRevenue += parseFloat(p.total_revenue) || 0;
            totalCost += parseFloat(p.total_cost) || 0;
            totalProfit += parseFloat(p.profit) || 0;
        });

        res.json({
            success: true,
            data: {
                products,
                summary: {
                    total_revenue: totalRevenue,
                    total_cost: totalCost,
                    total_profit: totalProfit,
                    profit_margin: totalRevenue > 0 ? ((totalProfit / totalRevenue) * 100).toFixed(2) : 0
                }
            }
        });
    } catch (error) {
        console.error('Get profit-loss report error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get profit-loss report'
        });
    }
});

/**
 * GET /api/reports/top-products
 * Get top selling products
 */
router.get('/top-products', async (req, res) => {
    try {
        const { date_from, date_to, limit = 10, branch_id } = req.query;

        let sql = `
      SELECT 
        ti.product_id,
        p.name as product_name,
        p.sell_price,
        SUM(ti.quantity) as total_qty,
        SUM(ti.subtotal) as total_sales
      FROM transaction_items ti
      JOIN transactions t ON ti.transaction_id = t.id
      JOIN products p ON ti.product_id = p.id
      WHERE t.status = 'completed'
    `;
        const params = [];

        if (branch_id) {
            sql += ' AND t.branch_id = ?';
            params.push(branch_id);
        }

        if (date_from) {
            sql += ' AND DATE(t.created_at) >= ?';
            params.push(date_from);
        }

        if (date_to) {
            sql += ' AND DATE(t.created_at) <= ?';
            params.push(date_to);
        }

        sql += ` GROUP BY ti.product_id, p.name, p.sell_price ORDER BY total_qty DESC LIMIT ?`;
        params.push(parseInt(limit));

        const [products] = await req.tenantDb.execute(sql, params);

        res.json({
            success: true,
            data: products
        });
    } catch (error) {
        console.error('Get top products error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get top products'
        });
    }
});

/**
 * GET /api/reports/dashboard
 * Get dashboard stats (filtered by branch for manager/cashier)
 */
router.get('/dashboard', async (req, res) => {
    try {
        const today = new Date().toISOString().split('T')[0];

        // Determine branch filter
        // - tenant_owner: can view all or filter by query param
        // - manager/cashier: always filter by their assigned branch
        let branchFilter = '';
        let branchId = null;

        if (req.user.role === 'tenant_owner' && req.query.branch_id) {
            branchId = parseInt(req.query.branch_id);
            branchFilter = ' AND branch_id = ?';
        } else if (req.user.branch_id) {
            branchId = req.user.branch_id;
            branchFilter = ' AND branch_id = ?';
        }

        // Today's sales (with branch filter)
        const todayParams = [today];
        if (branchId) todayParams.push(branchId);

        const [[todaySales]] = await req.tenantDb.execute(`
      SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(total_amount), 0) as total_sales
      FROM transactions 
      WHERE status = 'completed' AND DATE(created_at) = ?${branchFilter}
    `, todayParams);

        // This week's sales (with branch filter)
        const weekParams = branchId ? [branchId] : [];
        const [[weekSales]] = await req.tenantDb.execute(`
      SELECT COALESCE(SUM(total_amount), 0) as total
      FROM transactions 
      WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)${branchFilter}
    `, weekParams);

        // This month's sales (with branch filter)
        const [[monthSales]] = await req.tenantDb.execute(`
      SELECT COALESCE(SUM(total_amount), 0) as total
      FROM transactions 
      WHERE status = 'completed' AND YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())${branchFilter}
    `, weekParams);

        // Low stock count (uses branch_stock if branch filter active)
        let lowStockQuery = `SELECT COUNT(*) as count FROM products WHERE stock <= min_stock AND is_active = true`;
        let lowStockParams = [];

        if (branchId) {
            lowStockQuery = `
              SELECT COUNT(*) as count FROM branch_stock bs
              JOIN products p ON bs.product_id = p.id
              WHERE bs.stock <= p.min_stock AND p.is_active = true AND bs.branch_id = ?
            `;
            lowStockParams = [branchId];
        }

        const [[lowStock]] = await req.tenantDb.execute(lowStockQuery, lowStockParams);

        // Weekly chart data (with branch filter)
        const [weeklyData] = await req.tenantDb.execute(`
      SELECT 
        DATE(created_at) as date,
        COALESCE(SUM(total_amount), 0) as total
      FROM transactions 
      WHERE status = 'completed' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)${branchFilter}
      GROUP BY DATE(created_at)
      ORDER BY date
    `, weekParams);

        // Debt and Receivable Summary
        const nextWeek = new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

        // Supplier Debts (not branch-specific for now)
        const [[debtStats]] = await req.tenantDb.execute(`
          SELECT 
            COALESCE(SUM(total_amount - COALESCE(paid_amount, 0)), 0) as totalOutstanding,
            SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END) as overdueCount,
            SUM(CASE WHEN due_date >= ? AND due_date <= ? THEN 1 ELSE 0 END) as upcomingCount
          FROM purchases
          WHERE payment_status IN ('unpaid', 'partial')
        `, [today, today, nextWeek]);

        // Customer Receivables (with branch filter)
        const recParams = [today, today, nextWeek];
        if (branchId) recParams.push(branchId);

        const [[recStats]] = await req.tenantDb.execute(`
          SELECT 
            COALESCE(SUM(total_amount - COALESCE(payment_amount, 0)), 0) as totalOutstanding,
            SUM(CASE WHEN due_date < ? THEN 1 ELSE 0 END) as overdueCount,
            SUM(CASE WHEN due_date >= ? AND due_date <= ? THEN 1 ELSE 0 END) as upcomingCount
          FROM transactions
          WHERE payment_status IN ('unpaid', 'partial', 'debt')${branchFilter}
        `, recParams);

        res.json({
            success: true,
            data: {
                today: {
                    orders: parseInt(todaySales.total_orders) || 0,
                    sales: parseFloat(todaySales.total_sales) || 0
                },
                week: parseFloat(weekSales.total) || 0,
                month: parseFloat(monthSales.total) || 0,
                lowStockCount: parseInt(lowStock.count) || 0,
                weeklyChart: weeklyData.map(d => ({
                    date: d.date,
                    total: parseFloat(d.total)
                })),
                debts: {
                    totalOutstanding: parseFloat(debtStats?.totalOutstanding) || 0,
                    overdueCount: parseInt(debtStats?.overdueCount) || 0,
                    upcomingCount: parseInt(debtStats?.upcomingCount) || 0
                },
                receivables: {
                    totalOutstanding: parseFloat(recStats?.totalOutstanding) || 0,
                    overdueCount: parseInt(recStats?.overdueCount) || 0,
                    upcomingCount: parseInt(recStats?.upcomingCount) || 0
                },
                branchId: branchId // Include for frontend reference
            }
        });
    } catch (error) {
        console.error('Get dashboard stats error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get dashboard stats'
        });
    }
});

/**
 * GET /api/reports/assets
 * Get total asset valuation based on Largest Unit Logic
 * Logic: Sum( (Stock / MaxConversion) * MaxUnitBuyPrice )
 */
router.get('/assets', async (req, res) => {
    try {
        const branchId = req.query.branch_id || req.user.branch_id;

        let sql;
        let params = [];

        // 1. Get Stock + Product ID
        if (branchId) {
            sql = `
                SELECT bs.product_id, bs.stock 
                FROM branch_stock bs 
                JOIN products p ON bs.product_id = p.id 
                WHERE bs.branch_id = ? AND bs.stock > 0 AND p.is_active = true
            `;
            params.push(branchId);
        } else {
            // Global Stock (Sum of all branches OR products.stock if straightforward)
            // Safer to use products.stock for consistency if we treat it as master
            sql = `
                SELECT id as product_id, stock 
                FROM products 
                WHERE stock > 0 AND is_active = true
            `;
        }

        const [products] = await req.tenantDb.execute(sql, params);

        let totalAsset = 0;

        // 2. Iterate and Calculate
        // We can't easily do this in one SQL query because MaxUnit logic is complex 
        // (needs joining units, finding max conversion, etc). 
        // Better to fetch units for these products or fetch ALL units once and map them.

        // Fetch ALL units for active products (optimize with WHERE IN if needed, but for <10k products, fetching all units is okay)
        // Optimization: Fetch only units for products involved.
        if (products.length > 0) {
            const productIds = products.map(p => p.product_id);
            const placeholders = productIds.map(() => '?').join(',');

            const [units] = await req.tenantDb.execute(`
                SELECT product_id, conversion_qty, buy_price 
                FROM product_units 
                WHERE product_id IN (${placeholders})
                ORDER BY conversion_qty DESC
            `, productIds);

            // Map units by product_id
            const unitsMap = {};
            units.forEach(u => {
                if (!unitsMap[u.product_id]) unitsMap[u.product_id] = [];
                unitsMap[u.product_id].push(u);
            });

            // Calculate
            products.forEach(p => {
                const productUnits = unitsMap[p.product_id] || [];
                if (productUnits.length > 0) {
                    // Units are already ordered by conversion_qty DESC in SQL
                    const largestUnit = productUnits[0];

                    const buyPrice = parseFloat(largestUnit.buy_price) || 0;
                    const conversion = parseFloat(largestUnit.conversion_qty) || 1;
                    const stock = parseFloat(p.stock) || 0;

                    // Logic Final Update: Revert to division because Stock in DB is BASE UNIT (e.g. 4416)
                    // (4416 / 12) * 20.000 = 7.360.000 (Correct)
                    const assetValue = (stock / conversion) * buyPrice;
                    totalAsset += assetValue;

                    try {
                        require('fs').appendFileSync('d:/debug_asset.txt', `[DEBUG FINAL] ID:${p.product_id} Stock:${stock} Conv:${conversion} Asset:${assetValue} Total:${totalAsset}\n`);
                    } catch (e) { }
                }
            });
        }

        res.json({
            success: true,
            data: {
                totalAsset: Math.round(totalAsset),
                productCount: products.length,
                note: "Valuation based on Largest Unit Buy Price"
            }
        });

    } catch (error) {
        console.error('Get asset valuation error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to calculate assets'
        });
    }
});

const PDFDocument = require('pdfkit');

/**
 * GET /api/reports/sales/pdf
 * Export sales report to PDF
 */
router.get('/sales/pdf', async (req, res) => {
    try {
        const { date_from, date_to, branch_id } = req.query;

        let sql = `
            SELECT 
                DATE(created_at) as period,
                COUNT(*) as total_transactions,
                SUM(total_amount) as total_sales,
                SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as completed_sales,
                SUM(CASE WHEN status = 'cancelled' THEN total_amount ELSE 0 END) as cancelled_amount
            FROM transactions
            WHERE 1=1
        `;
        const params = [];

        if (branch_id) { sql += ' AND branch_id = ?'; params.push(branch_id); }
        if (date_from) { sql += ' AND DATE(created_at) >= ?'; params.push(date_from); }
        if (date_to) { sql += ' AND DATE(created_at) <= ?'; params.push(date_to); }

        sql += ' GROUP BY DATE(created_at) ORDER BY DATE(created_at) DESC';

        const [rows] = await req.tenantDb.execute(sql, params);

        // Create PDF
        const doc = new PDFDocument({ margin: 50, size: 'A4' });
        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', `attachment; filename=sales_report_${Date.now()}.pdf`);
        doc.pipe(res);

        // Title
        doc.font('Helvetica-Bold').fontSize(18).text('Laporan Penjualan', { align: 'center' });
        doc.moveDown(0.5);
        doc.font('Helvetica').fontSize(10).text(`Periode: ${date_from || 'All'} s/d ${date_to || 'All'}`, { align: 'center' });
        doc.moveDown();

        // Summary
        const totalSales = rows.reduce((sum, r) => sum + parseFloat(r.total_sales || 0), 0);
        const totalTrx = rows.reduce((sum, r) => sum + parseInt(r.total_transactions || 0), 0);
        doc.fontSize(10).text(`Total: ${totalTrx} transaksi | Rp ${totalSales.toLocaleString('id-ID')}`, { align: 'center' });
        doc.moveDown();

        // Table
        const tableTop = 150;
        const headers = ['Tanggal', 'Transaksi', 'Total', 'Completed', 'Cancelled'];
        const colWidths = [100, 80, 100, 100, 100];

        doc.font('Helvetica-Bold').fontSize(9);
        let xPos = 50;
        headers.forEach((h, i) => { doc.text(h, xPos, tableTop); xPos += colWidths[i]; });
        doc.moveTo(50, tableTop + 15).lineTo(545, tableTop + 15).stroke();

        doc.font('Helvetica').fontSize(8);
        let yPos = tableTop + 25;
        const formatCurrency = (num) => `Rp ${(num || 0).toLocaleString('id-ID')}`;

        for (const row of rows) {
            if (yPos > 750) { doc.addPage(); yPos = 50; }
            xPos = 50;
            const data = [
                new Date(row.period).toLocaleDateString('id-ID'),
                row.total_transactions?.toString() || '0',
                formatCurrency(row.total_sales),
                formatCurrency(row.completed_sales),
                formatCurrency(row.cancelled_amount)
            ];
            data.forEach((text, i) => { doc.text(text, xPos, yPos); xPos += colWidths[i]; });
            yPos += 15;
        }

        doc.end();

    } catch (error) {
        console.error('Export sales PDF error:', error);
        res.status(500).json({ success: false, message: 'Failed to export PDF' });
    }
});

/**
 * GET /api/reports/profit-loss/pdf
 * Export profit & loss report to PDF
 */
router.get('/profit-loss/pdf', async (req, res) => {
    try {
        const { date_from, date_to, branch_id } = req.query;

        let sql = `
            SELECT 
                p.id as product_id,
                p.name as product_name,
                SUM(ti.quantity) as total_qty,
                SUM(ti.quantity * COALESCE(ti.buy_price, 0)) as total_cost,
                SUM(ti.subtotal) as total_revenue,
                SUM(ti.subtotal) - SUM(ti.quantity * COALESCE(ti.buy_price, 0)) as profit
            FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            JOIN products p ON ti.product_id = p.id
            WHERE t.status = 'completed'
        `;
        const params = [];

        if (branch_id) { sql += ' AND t.branch_id = ?'; params.push(branch_id); }
        if (date_from) { sql += ' AND DATE(t.created_at) >= ?'; params.push(date_from); }
        if (date_to) { sql += ' AND DATE(t.created_at) <= ?'; params.push(date_to); }

        sql += ' GROUP BY p.id, p.name ORDER BY profit DESC';

        const [products] = await req.tenantDb.execute(sql, params);

        // Create PDF
        const doc = new PDFDocument({ margin: 50, size: 'A4' });
        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', `attachment; filename=profit_loss_report_${Date.now()}.pdf`);
        doc.pipe(res);

        // Title
        doc.font('Helvetica-Bold').fontSize(18).text('Laporan Laba Rugi', { align: 'center' });
        doc.moveDown(0.5);
        doc.font('Helvetica').fontSize(10).text(`Periode: ${date_from || 'All'} s/d ${date_to || 'All'}`, { align: 'center' });
        doc.moveDown();

        // Summary
        const totalRevenue = products.reduce((sum, p) => sum + parseFloat(p.total_revenue || 0), 0);
        const totalCost = products.reduce((sum, p) => sum + parseFloat(p.total_cost || 0), 0);
        const totalProfit = products.reduce((sum, p) => sum + parseFloat(p.profit || 0), 0);

        doc.fontSize(10).text(`Revenue: Rp ${totalRevenue.toLocaleString('id-ID')} | Cost: Rp ${totalCost.toLocaleString('id-ID')} | Profit: Rp ${totalProfit.toLocaleString('id-ID')}`, { align: 'center' });
        doc.moveDown();

        // Table
        const tableTop = 160;
        const headers = ['Produk', 'Qty', 'Cost', 'Revenue', 'Profit'];
        const colWidths = [150, 50, 100, 100, 100];

        doc.font('Helvetica-Bold').fontSize(9);
        let xPos = 50;
        headers.forEach((h, i) => { doc.text(h, xPos, tableTop); xPos += colWidths[i]; });
        doc.moveTo(50, tableTop + 15).lineTo(545, tableTop + 15).stroke();

        doc.font('Helvetica').fontSize(8);
        let yPos = tableTop + 25;
        const formatCurrency = (num) => `Rp ${(num || 0).toLocaleString('id-ID')}`;

        for (const p of products) {
            if (yPos > 750) { doc.addPage(); yPos = 50; }
            xPos = 50;
            const data = [
                (p.product_name || '-').substring(0, 25),
                p.total_qty?.toString() || '0',
                formatCurrency(p.total_cost),
                formatCurrency(p.total_revenue),
                formatCurrency(p.profit)
            ];
            data.forEach((text, i) => { doc.text(text, xPos, yPos); xPos += colWidths[i]; });
            yPos += 15;
        }

        doc.end();

    } catch (error) {
        console.error('Export profit-loss PDF error:', error);
        res.status(500).json({ success: false, message: 'Failed to export PDF' });
    }
});

/**
 * GET /api/reports/top-products/pdf
 * Export top products report to PDF
 */
router.get('/top-products/pdf', async (req, res) => {
    try {
        const { date_from, date_to, branch_id, limit = 20 } = req.query;

        let sql = `
            SELECT 
                p.id as product_id,
                p.name as product_name,
                SUM(ti.quantity) as total_qty,
                SUM(ti.subtotal) as total_sales
            FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            JOIN products p ON ti.product_id = p.id
            WHERE t.status = 'completed'
        `;
        const params = [];

        if (branch_id) { sql += ' AND t.branch_id = ?'; params.push(branch_id); }
        if (date_from) { sql += ' AND DATE(t.created_at) >= ?'; params.push(date_from); }
        if (date_to) { sql += ' AND DATE(t.created_at) <= ?'; params.push(date_to); }

        sql += ` GROUP BY p.id, p.name ORDER BY total_qty DESC LIMIT ${parseInt(limit)}`;

        const [products] = await req.tenantDb.execute(sql, params);

        // Create PDF
        const doc = new PDFDocument({ margin: 50, size: 'A4' });
        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', `attachment; filename=top_products_report_${Date.now()}.pdf`);
        doc.pipe(res);

        // Title
        doc.font('Helvetica-Bold').fontSize(18).text('Top Produk Terlaris', { align: 'center' });
        doc.moveDown(0.5);
        doc.font('Helvetica').fontSize(10).text(`Periode: ${date_from || 'All'} s/d ${date_to || 'All'}`, { align: 'center' });
        doc.moveDown();

        // Table
        const tableTop = 120;
        const headers = ['#', 'Produk', 'Qty Terjual', 'Total Penjualan'];
        const colWidths = [40, 220, 100, 130];

        doc.font('Helvetica-Bold').fontSize(9);
        let xPos = 50;
        headers.forEach((h, i) => { doc.text(h, xPos, tableTop); xPos += colWidths[i]; });
        doc.moveTo(50, tableTop + 15).lineTo(545, tableTop + 15).stroke();

        doc.font('Helvetica').fontSize(8);
        let yPos = tableTop + 25;
        const formatCurrency = (num) => `Rp ${(num || 0).toLocaleString('id-ID')}`;

        products.forEach((p, index) => {
            if (yPos > 750) { doc.addPage(); yPos = 50; }
            xPos = 50;
            const data = [
                (index + 1).toString(),
                (p.product_name || '-').substring(0, 35),
                p.total_qty?.toString() || '0',
                formatCurrency(p.total_sales)
            ];
            data.forEach((text, i) => { doc.text(text, xPos, yPos); xPos += colWidths[i]; });
            yPos += 15;
        });

        doc.end();

    } catch (error) {
        console.error('Export top products PDF error:', error);
        res.status(500).json({ success: false, message: 'Failed to export PDF' });
    }
});

module.exports = router;

