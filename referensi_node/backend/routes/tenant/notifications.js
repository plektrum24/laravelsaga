const express = require('express');
const router = express.Router();

/**
 * Helper: Ensure notifications table exists (lazy migration)
 */
async function ensureNotificationsTable(db) {
    try {
        await db.execute(`
            CREATE TABLE IF NOT EXISTS notification_states (
                id INT AUTO_INCREMENT PRIMARY KEY,
                notification_key VARCHAR(100) UNIQUE NOT NULL,
                is_read BOOLEAN DEFAULT FALSE,
                dismissed_at DATETIME NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        `);
    } catch (e) { /* Table might already exist */ }
}

/**
 * GET /api/notifications
 * Get all notifications (Low Stock, Due Debts, Due Receivables)
 */
router.get('/', async (req, res) => {
    try {
        await ensureNotificationsTable(req.tenantDb);

        const today = new Date().toISOString().split('T')[0];
        const notifications = [];

        // Get notification states (dismissed and read)
        let dismissedKeys = new Set();
        let readKeys = new Set();
        try {
            const [stateRows] = await req.tenantDb.execute(
                `SELECT notification_key, is_read, dismissed_at FROM notification_states`
            );
            dismissedKeys = new Set(stateRows.filter(r => r.dismissed_at !== null).map(r => r.notification_key));
            readKeys = new Set(stateRows.filter(r => r.is_read).map(r => r.notification_key));
        } catch (e) {
            console.log('[NOTIF] notification_states error:', e.message);
        }

        // 1. Recent Price Changes (Top Priority / Latest Activity)
        try {
            // Get logs without joining users (users table is in main DB)
            const [priceLogs] = await req.tenantDb.execute(`
                SELECT pl.id, pl.old_price, pl.new_price, pl.created_at, pl.user_id,
                       COALESCE(p.name, 'Unknown Product') as product_name, 
                       COALESCE(u.name, '?') as unit_name
                FROM price_logs pl
                LEFT JOIN products p ON pl.product_id = p.id
                LEFT JOIN units u ON pl.unit_id = u.id
                ORDER BY pl.created_at DESC
                LIMIT 10
            `);
            console.log('[NOTIF] Price Logs found:', priceLogs.length);

            // Fetch User Names from Main DB
            const userIds = [...new Set(priceLogs.map(l => l.user_id).filter(id => id))];
            const userMap = {};
            if (userIds.length > 0) {
                try {
                    const { getMainPool } = require('../../config/database');
                    const mainDb = await getMainPool();
                    const [users] = await mainDb.execute(
                        `SELECT id, name FROM users WHERE id IN (${userIds.map(() => '?').join(',')})`,
                        userIds
                    );
                    users.forEach(u => userMap[u.id] = u.name);
                } catch (err) {
                    console.error('[NOTIF] Failed to fetch users from main DB:', err.message);
                }
            }

            priceLogs.forEach(log => {
                const key = `price_log_${log.id}`;
                if (!dismissedKeys.has(key)) {
                    const timeStr = new Date(log.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    const oldP = parseFloat(log.old_price).toLocaleString('id-ID');
                    const newP = parseFloat(log.new_price).toLocaleString('id-ID');

                    // Resolve User Name
                    let userName = 'System';
                    if (log.user_id) {
                        userName = userMap[log.user_id] || `User #${log.user_id}`;
                    }

                    notifications.push({
                        key,
                        type: 'info',
                        title: 'Price Changed',
                        message: `${log.product_name} (${log.unit_name}): Rp ${oldP} -> Rp ${newP} by ${userName}`,
                        link: `inventory.html?edit_product=${log.product_id}`,
                        time: timeStr,
                        is_read: readKeys.has(key)
                    });
                }
            });
        } catch (e) {
            console.log('[NOTIF] Price logs error:', e.message);
        }

        // 2. Low Stock Products
        try {
            const [lowStockProducts] = await req.tenantDb.execute(`
                SELECT id, name, stock, min_stock 
                FROM products 
                WHERE stock <= min_stock AND is_active = 1
                ORDER BY stock ASC
                LIMIT 15
            `);
            console.log('[NOTIF] Low stock:', lowStockProducts.length);

            lowStockProducts.forEach(p => {
                const key = `low_stock_${p.id}`;
                if (!dismissedKeys.has(key)) {
                    notifications.push({
                        key,
                        type: 'warning',
                        title: 'Low Stock Alert',
                        message: `${p.name} only has ${parseFloat(p.stock).toFixed(2)} left (Min: ${parseFloat(p.min_stock).toFixed(2)})`,
                        link: 'inventory.html?low_stock=true',
                        time: 'Now',
                        is_read: readKeys.has(key)
                    });
                }
            });
        } catch (e) {
            console.log('[NOTIF] Low stock error:', e.message);
        }

        // 2. Customer Receivables - H-2 before due date or already overdue
        try {
            // Calculate H-2: 2 days from now
            const h2Date = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

            const [customerReceivables] = await req.tenantDb.execute(`
                SELECT 
                    t.id,
                    t.invoice_number, 
                    COALESCE(t.customer_name, 'Walk-in') as customer, 
                    t.due_date,
                    (t.total_amount - COALESCE(t.payment_amount, 0)) as remaining
                FROM transactions t
                WHERE t.payment_amount < t.total_amount
                AND t.status = 'completed'
                AND t.due_date IS NOT NULL
                AND t.due_date <= ?
                ORDER BY t.due_date ASC
                LIMIT 15
            `, [h2Date]);
            console.log('[NOTIF] Receivables (H-2):', customerReceivables.length);

            customerReceivables.forEach(r => {
                const key = `receivable_${r.id}`;
                if (!dismissedKeys.has(key)) {
                    const isOverdue = r.due_date && new Date(r.due_date) < new Date(today);
                    const dateStr = r.due_date instanceof Date
                        ? r.due_date.toISOString().split('T')[0]
                        : String(r.due_date).split('T')[0];
                    notifications.push({
                        key,
                        type: isOverdue ? 'error' : 'info',
                        title: isOverdue ? 'Overdue Receivable' : 'Due Soon (H-2)',
                        message: `Collect from ${r.customer} (Inv: ${r.invoice_number}). Remaining: ${Number(r.remaining).toLocaleString()}`,
                        link: 'transactions.html',
                        time: dateStr,
                        is_read: readKeys.has(key)
                    });
                }
            });
        } catch (e) {
            console.log('[NOTIF] Receivables error:', e.message);
        }

        // 3. Supplier Debts - H-2 before due date or already overdue
        try {
            const h2Date = new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];

            const [supplierDebts] = await req.tenantDb.execute(`
                SELECT 
                    p.id,
                    p.invoice_number, 
                    COALESCE(s.name, 'Unknown') as supplier, 
                    p.due_date,
                    (p.total_amount - COALESCE(p.paid_amount, 0)) as remaining
                FROM purchases p
                LEFT JOIN suppliers s ON p.supplier_id = s.id
                WHERE COALESCE(p.paid_amount, 0) < p.total_amount
                AND p.due_date IS NOT NULL
                AND p.due_date <= ?
                ORDER BY p.due_date ASC
                LIMIT 10
            `, [h2Date]);
            console.log('[NOTIF] Debts (H-2):', supplierDebts.length);

            supplierDebts.forEach(d => {
                const key = `debt_${d.id}`;
                if (!dismissedKeys.has(key)) {
                    const isOverdue = d.due_date && new Date(d.due_date) < new Date(today);
                    const dateStr = d.due_date instanceof Date
                        ? d.due_date.toISOString().split('T')[0]
                        : String(d.due_date).split('T')[0];
                    notifications.push({
                        key,
                        type: isOverdue ? 'error' : 'info',
                        title: isOverdue ? 'Overdue Debt' : 'Debt Due Soon (H-2)',
                        message: `Pay ${d.supplier} (Inv: ${d.invoice_number}). Remaining: ${Number(d.remaining).toLocaleString()}`,
                        link: 'goods-in.html',
                        time: dateStr,
                        is_read: readKeys.has(key)
                    });
                }
            });
        } catch (e) {
            console.log('[NOTIF] Debts error:', e.message);
        }

        // (Moved to top)


        // Sort all notifications by time (rough sorting as we have mixed date formats)
        // For accurate sorting, we might need a raw timestamp property.
        // But for now, just appending is fine or rely on client side?
        // Let's rely on the order pushed: Low Stock (Urgent) > Receivables > Debts > Price Logs.
        // Actually, Price Logs are "Recent Activities". Maybe put them first?
        // Let's just append for now.

        // Count unread
        const unreadCount = notifications.filter(n => !n.is_read).length;
        console.log('[NOTIF] Total:', notifications.length, 'Unread:', unreadCount);

        res.json({
            success: true,
            data: notifications,
            count: notifications.length,
            unreadCount
        });

    } catch (error) {
        console.error('[NOTIF] FATAL ERROR:', error);
        res.status(500).json({ success: false, message: 'Failed to fetch notifications: ' + error.message });
    }
});

/**
 * PATCH /api/notifications/:key/read
 */
router.patch('/:key/read', async (req, res) => {
    try {
        await ensureNotificationsTable(req.tenantDb);
        const { key } = req.params;
        const { is_read } = req.body;

        await req.tenantDb.execute(`
            INSERT INTO notification_states (notification_key, is_read) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE is_read = ?
        `, [key, is_read, is_read]);

        res.json({ success: true, message: is_read ? 'Marked as read' : 'Marked as unread' });
    } catch (error) {
        console.error('Toggle read error:', error);
        res.status(500).json({ success: false, message: 'Failed to update notification' });
    }
});

/**
 * DELETE /api/notifications/:key
 */
router.delete('/:key', async (req, res) => {
    try {
        await ensureNotificationsTable(req.tenantDb);
        const { key } = req.params;

        await req.tenantDb.execute(`
            INSERT INTO notification_states (notification_key, dismissed_at) 
            VALUES (?, NOW())
            ON DUPLICATE KEY UPDATE dismissed_at = NOW()
        `, [key]);

        res.json({ success: true, message: 'Notification dismissed' });
    } catch (error) {
        console.error('Dismiss error:', error);
        res.status(500).json({ success: false, message: 'Failed to dismiss notification' });
    }
});

/**
 * POST /api/notifications/clear-all
 */
router.post('/clear-all', async (req, res) => {
    try {
        await ensureNotificationsTable(req.tenantDb);
        const { keys } = req.body;

        if (keys && keys.length > 0) {
            for (const k of keys) {
                await req.tenantDb.execute(`
                    INSERT INTO notification_states (notification_key, dismissed_at) 
                    VALUES (?, NOW())
                    ON DUPLICATE KEY UPDATE dismissed_at = NOW()
                `, [k]);
            }
        }

        res.json({ success: true, message: 'All notifications dismissed' });
    } catch (error) {
        console.error('Clear all error:', error);
        res.status(500).json({ success: false, message: 'Failed to clear notifications' });
    }
});

module.exports = router;
