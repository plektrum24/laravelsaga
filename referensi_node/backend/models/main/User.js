const { getMainPool } = require('../../config/database');
const bcrypt = require('bcryptjs');

// Always use explicit database name to avoid pool switching issues
const DB_NAME = process.env.DB_NAME || 'saga_main';

class User {
    /**
     * Find all users (Super Admin only)
     */
    static async findAll() {
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            `SELECT id, email, name, role, tenant_id, branch_id, is_active, created_at FROM ${DB_NAME}.users ORDER BY created_at DESC`
        );
        return rows;
    }

    /**
     * Find user by email
     * @param {string} email 
     */
    static async findByEmail(email) {
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            `SELECT * FROM ${DB_NAME}.users WHERE email = ?`,
            [email]
        );
        return rows[0] || null;
    }

    /**
     * Find user by ID
     * @param {number} id 
     */
    static async findById(id) {
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            `SELECT id, email, name, role, tenant_id, branch_id, is_active, created_at FROM ${DB_NAME}.users WHERE id = ?`,
            [id]
        );
        return rows[0] || null;
    }

    /**
     * Create new user
     * @param {Object} userData 
     */
    static async create(userData) {
        const pool = await getMainPool();
        const hashedPassword = await bcrypt.hash(userData.password, 10);

        const [result] = await pool.execute(
            `INSERT INTO ${DB_NAME}.users (email, password, name, role, tenant_id, branch_id, is_active) 
       VALUES (?, ?, ?, ?, ?, ?, ?)`,
            [
                userData.email,
                hashedPassword,
                userData.name,
                userData.role,
                userData.tenant_id || null,
                userData.branch_id || null,
                userData.is_active !== undefined ? userData.is_active : true
            ]
        );

        return { id: result.insertId, ...userData, password: undefined };
    }

    /**
     * Update user
     * @param {number} id 
     * @param {Object} userData 
     */
    static async update(id, userData) {
        const pool = await getMainPool();
        const fields = [];
        const values = [];

        if (userData.name) {
            fields.push('name = ?');
            values.push(userData.name);
        }
        if (userData.email) {
            fields.push('email = ?');
            values.push(userData.email);
        }
        if (userData.password) {
            fields.push('password = ?');
            values.push(await bcrypt.hash(userData.password, 10));
        }
        if (userData.role) {
            fields.push('role = ?');
            values.push(userData.role);
        }
        if (userData.tenant_id !== undefined) {
            fields.push('tenant_id = ?');
            values.push(userData.tenant_id || null);
        }
        if (userData.branch_id !== undefined) {
            fields.push('branch_id = ?');
            values.push(userData.branch_id);
        }
        if (userData.is_active !== undefined) {
            fields.push('is_active = ?');
            values.push(userData.is_active);
        }

        if (fields.length === 0) return null;

        values.push(id);
        // Use explicit database name to avoid pool switching issues
        const dbName = process.env.DB_NAME || 'saga_main';
        await pool.execute(
            `UPDATE ${dbName}.users SET ${fields.join(', ')} WHERE id = ?`,
            values
        );

        return await User.findById(id);
    }

    /**
     * Verify user password
     * @param {string} password 
     * @param {string} hashedPassword 
     */
    static async verifyPassword(password, hashedPassword) {
        return await bcrypt.compare(password, hashedPassword);
    }

    /**
     * Get all users by tenant
     * @param {number} tenantId 
     */
    static async findByTenant(tenantId) {
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            `SELECT id, email, name, role, branch_id, is_active, created_at FROM ${DB_NAME}.users WHERE tenant_id = ?`,
            [tenantId]
        );
        return rows;
    }

    /**
     * Delete user
     * @param {number} id 
     */
    static async delete(id) {
        const pool = await getMainPool();
        const [result] = await pool.execute(
            `DELETE FROM ${DB_NAME}.users WHERE id = ?`,
            [id]
        );
        return result.affectedRows > 0;
    }
}

module.exports = User;
