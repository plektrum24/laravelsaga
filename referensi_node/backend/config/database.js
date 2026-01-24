const mysql = require('mysql2/promise');

// Main database pool (for users and tenants)
let mainPool = null;

// Tenant database pools cache
const tenantPools = new Map();

/**
 * Initialize the main database connection pool
 */
const initMainPool = async () => {
    if (!mainPool) {
        mainPool = mysql.createPool({
            host: process.env.DB_HOST,
            port: process.env.DB_PORT,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: process.env.DB_NAME,
            waitForConnections: true,
            connectionLimit: parseInt(process.env.DB_POOL_LIMIT) || 10,
            queueLimit: 0
        });
        console.log('✅ Main database pool initialized');
    }
    return mainPool;
};

/**
 * Get the main database pool
 */
const getMainPool = async () => {
    if (!mainPool) {
        await initMainPool();
    }
    return mainPool;
};

/**
 * Get or create a tenant database pool
 * @param {string} databaseName - The tenant's database name
 */
const getTenantPool = async (databaseName) => {
    if (!tenantPools.has(databaseName)) {
        const pool = mysql.createPool({
            host: process.env.DB_HOST,
            port: process.env.DB_PORT,
            user: process.env.DB_USER,
            password: process.env.DB_PASSWORD,
            database: databaseName,
            waitForConnections: true,
            connectionLimit: parseInt(process.env.DB_POOL_LIMIT) || 10,
            queueLimit: 0
        });
        tenantPools.set(databaseName, pool);
        console.log(`✅ Tenant database pool created: ${databaseName}`);
    }
    return tenantPools.get(databaseName);
};

/**
 * Close a specific tenant pool
 * @param {string} databaseName - The tenant's database name
 */
const closeTenantPool = async (databaseName) => {
    if (tenantPools.has(databaseName)) {
        await tenantPools.get(databaseName).end();
        tenantPools.delete(databaseName);
        console.log(`🔒 Tenant database pool closed: ${databaseName}`);
    }
};

/**
 * Close all database pools
 */
const closeAllPools = async () => {
    if (mainPool) {
        await mainPool.end();
        mainPool = null;
    }
    for (const [name, pool] of tenantPools) {
        await pool.end();
    }
    tenantPools.clear();
    console.log('🔒 All database pools closed');
};

module.exports = {
    initMainPool,
    getMainPool,
    getTenantPool,
    closeTenantPool,
    closeAllPools
};
