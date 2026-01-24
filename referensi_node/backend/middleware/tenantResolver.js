const { getMainPool, getTenantPool } = require('../config/database');

/**
 * Middleware to resolve tenant database connection
 * Must be used after authenticateToken middleware
 */
const resolveTenant = async (req, res, next) => {
    try {
        // Super admin doesn't need tenant resolution for admin routes
        if (req.user.role === 'super_admin' && !req.user.tenant_id) {
            return next();
        }

        const tenantId = req.user.tenant_id;

        if (!tenantId) {
            return res.status(400).json({
                success: false,
                message: 'Tenant information not found'
            });
        }

        // Get tenant info from main database
        const mainPool = await getMainPool();
        const [tenants] = await mainPool.execute(
            'SELECT * FROM tenants WHERE id = ? AND status = "active"',
            [tenantId]
        );

        if (tenants.length === 0) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found or inactive'
            });
        }

        const tenant = tenants[0];

        // Get tenant database pool
        const tenantPool = await getTenantPool(tenant.database_name);

        // Run robust schema verification
        await ensureSchema(tenantPool, tenant.database_name);

        // Attach tenant info and database connection to request
        req.tenant = tenant;
        req.tenantDb = tenantPool;

        next();
    } catch (error) {
        console.error('Tenant resolution error:', error);
        return res.status(500).json({
            success: false,
            message: 'Failed to resolve tenant database'
        });
    }
};

/**
 * Helper to ensure database schema has all required columns
 * Used for auto-healing legacy databases
 */
const ensureSchema = async (pool, dbName) => {
    const addColumn = async (table, columnDef, colName) => {
        try {
            // Try to add column. Will fail if exists (fastest check)
            await pool.execute(`ALTER TABLE ${table} ADD COLUMN ${columnDef}`);
            console.log(`[MIGRATION] Added ${colName} to ${dbName}.${table}`);
        } catch (e) {
            // 1060: Duplicate column name
            if (e.errno !== 1060) {
                // Only log unexpected errors
                // console.error(`[MIGRATION] Check failed for ${table}.${colName}:`, e.message);
            }
        }
    };

    // Transaction Items
    await addColumn('transaction_items', "unit_name VARCHAR(50) DEFAULT 'Pcs'", 'unit_name');
    await addColumn('transaction_items', "buy_price DECIMAL(15,2) DEFAULT 0", 'buy_price');
    await addColumn('transaction_items', "conversion_qty DECIMAL(15,4) DEFAULT 1", 'conversion_qty'); // Fixed type to DECIMAL

    // Transactions
    await addColumn('transactions', "cashier_name VARCHAR(100) DEFAULT 'Admin'", 'cashier_name');

    // Fix: Make shift_id nullable to support optional shifts
    try {
        await pool.execute("ALTER TABLE transactions MODIFY COLUMN shift_id INT NULL");
    } catch (e) {
        // Ignore if already nullable or other minor errors
    }

    // Products
    await addColumn('products', "weight DECIMAL(10,2) DEFAULT 0", 'weight');
    await addColumn('products', "barcode VARCHAR(255) NULL", 'barcode');
    await addColumn('products', "is_base_unit BOOLEAN DEFAULT FALSE", 'is_base_unit'); // Check just in case

    // Product Units (CRITICAL FOR NEW TENANT/BRANCH ISSUES)
    await addColumn('product_units', "weight DECIMAL(10,2) DEFAULT 0", 'weight');
    await addColumn('product_units', "is_base_unit BOOLEAN DEFAULT FALSE", 'is_base_unit');
    await addColumn('product_units', "sort_order INT DEFAULT 99", 'sort_order');
};

module.exports = {
    resolveTenant
};
