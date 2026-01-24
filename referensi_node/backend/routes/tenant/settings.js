const express = require('express');
const router = express.Router();
const bcrypt = require('bcryptjs');
const { getMainPool } = require('../../config/database');

/**
 * POST /api/settings/reset
 * Factory Reset / Clear Data
 */
router.post('/reset', async (req, res) => {
    const { password, mode } = req.body;
    // mode: 'transactions' | 'full'

    if (!['transactions', 'full'].includes(mode)) {
        return res.status(400).json({ success: false, message: 'Invalid reset mode' });
    }

    const conn = await req.tenantDb.getConnection();

    try {
        // 1. Verify Password/Identity
        // We need to check the password of the currently logged in user (must be tenant_owner)
        if (req.user.role !== 'tenant_owner') {
            return res.status(403).json({ success: false, message: 'Only Tenant Owner can perform this action' });
        }

        // Get user's password hash from MAIN DB (users table)
        // users table is in the main database, NOT tenant database
        const mainPool = await getMainPool();
        const [users] = await mainPool.execute('SELECT password FROM users WHERE id = ?', [req.user.id]);

        if (users.length === 0) {
            return res.status(404).json({ success: false, message: 'User not found' });
        }

        const validPassword = await bcrypt.compare(password, users[0].password);
        if (!validPassword) {
            return res.status(401).json({ success: false, message: 'Invalid password' });
        }

        // 2. Begin Transaction
        await conn.beginTransaction();
        await conn.query('SET FOREIGN_KEY_CHECKS = 0'); // Disable FK for bulk delete

        if (mode === 'transactions') {
            // -- CLEAR TRANSACTIONS ONLY --
            const transactionTables = [
                'transaction_items', 'transactions',
                'purchase_items', 'purchases',
                'return_items', 'returns',
                'stock_transfer_items', 'stock_transfers',
                'shifts',
                // Add any other transaction-related tables here (e.g., payments, expenses)
            ];

            for (const table of transactionTables) {
                // Check if table exists before deleting (safety)
                const [exists] = await conn.execute(`SHOW TABLES LIKE '${table}'`);
                if (exists.length > 0) {
                    await conn.execute(`TRUNCATE TABLE ${table}`);
                }
            }

            // Reset Stock to 0
            const [bsExists] = await conn.execute("SHOW TABLES LIKE 'branch_stock'");
            if (bsExists.length > 0) {
                await conn.execute('UPDATE branch_stock SET stock = 0');
            }
            // Reset Product Stock cache (if any in products table)
            const [pExists] = await conn.execute("SHOW TABLES LIKE 'products'");
            if (pExists.length > 0) {
                await conn.execute('UPDATE products SET stock = 0');
            }

            // Reset SKU Sequences (optional, but good for fresh start)
            // await conn.execute('UPDATE sku_sequences SET last_number = 0');

        } else if (mode === 'full') {
            // -- FULL RESET --

            // Get all tables
            const [tables] = await conn.execute('SHOW TABLES');
            const tableNames = tables.map(t => Object.values(t)[0]);

            // Only keep 'branches' (we'll filter inside it later)
            // Remove 'sku_sequences' from kept tables so it gets cleared too
            const tablesToKeep = ['branches'];
            const tablesToDelete = tableNames.filter(t => !tablesToKeep.includes(t));

            for (const table of tablesToDelete) {
                await conn.execute(`DELETE FROM ${table}`);
                // Also reset Auto Increment
                try {
                    await conn.execute(`ALTER TABLE ${table} AUTO_INCREMENT = 1`);
                } catch (e) {
                    // Ignore if table doesn't support AI or other minor error
                }
            }

            // Handle Branches: Keep only main branch
            await conn.execute('DELETE FROM branches WHERE is_main = 0');
            // Check if we need to reset AI for branches? 
            // Maybe not needed if we keep main branch.

            // Re-seed default data if needed (e.g. Categories)? 
            // User asked for "Clean Data", usually means empty tables. 
            // But 'units' usually are system defaults. Let's re-seed Units and Default Categories?
            // "Full Reset (Hapus Semua) ... bersih seperti            // Re-seed Units
            // Reset AI first to ensure IDs start from 1
            await conn.execute('ALTER TABLE units AUTO_INCREMENT = 1');
            await conn.execute(`INSERT INTO units (name, short_name, type) VALUES 
                ('Pcs', 'pcs', 'quantity'),
                ('Kilogram', 'kg', 'weight'),
                ('Liter', 'l', 'volume'),
                ('Box', 'box', 'quantity')`);

            // Re-seed Categories
            // Reset AI first
            await conn.execute('ALTER TABLE categories AUTO_INCREMENT = 1');
            await conn.execute(`INSERT INTO categories (name, prefix) VALUES 
                ('General', 'GEN')`);
        }

        await conn.query('SET FOREIGN_KEY_CHECKS = 1');
        await conn.commit();

        res.json({
            success: true,
            message: mode === 'full' ? 'Factory reset successful. All data cleared.' : 'Transactions cleared successfully. Master data preserved.'
        });

    } catch (error) {
        await conn.rollback();
        // Ensure FK checks are re-enabled even on error if connection stays alive (pool)
        try { await conn.query('SET FOREIGN_KEY_CHECKS = 1'); } catch (e) { }
        console.error('Reset error:', error);
        res.status(500).json({ success: false, message: 'Failed to reset data: ' + error.message });
    } finally {
        conn.release();
    }
});

module.exports = router;
