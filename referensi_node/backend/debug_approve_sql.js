const mysql = require('mysql2/promise');
require('dotenv').config();

async function debugApprove() {
    const connection = await mysql.createConnection({
        host: process.env.DB_HOST || 'localhost',
        user: process.env.DB_USER || 'root',
        password: process.env.DB_PASSWORD || '',
        database: 'saga_tenant_jkt001'
    });

    console.log('Connected to database...');
    const transferId = 8; // From subagent log

    try {
        console.log(`Approving Transfer ${transferId}...`);

        const [transfers] = await connection.execute('SELECT * FROM stock_transfers WHERE id = ?', [transferId]);
        if (transfers.length === 0) throw new Error('Transfer not found');
        const transfer = transfers[0];
        console.log('Transfer found:', transfer);

        const [items] = await connection.execute('SELECT * FROM stock_transfer_items WHERE transfer_id = ?', [transferId]);
        console.log(`Found ${items.length} items.`);

        await connection.beginTransaction();
        console.log('Transaction started.');

        for (const item of items) {
            console.log(`Processing Item ${item.id}, Product ${item.product_id}, Qty ${item.qty_requested}`);

            const qty = item.qty_requested;

            // Update Stock
            const [updateRes] = await connection.execute(`
                UPDATE branch_stock 
                SET stock = stock - ? 
                WHERE branch_id = ? AND product_id = ?
            `, [qty, transfer.from_branch_id, item.product_id]);
            console.log('Stock Updated:', updateRes.info);

            // Update Item
            await connection.execute(`
                UPDATE stock_transfer_items
                SET qty_approved = ?
                WHERE id = ?
            `, [qty, item.id]);
            console.log('Item Updated.');
        }

        // Update Transfer
        await connection.execute(`
            UPDATE stock_transfers 
            SET status = 'shipped', approved_by = 1, approved_at = NOW()
            WHERE id = ?
        `, [transferId]);
        console.log('Transfer Status Updated.');

        await connection.commit();
        console.log('SUCCESS: Transaction Committed.');

    } catch (error) {
        console.error('FAILURE:', error);
        await connection.rollback();
    } finally {
        await connection.end();
    }
}

debugApprove();
