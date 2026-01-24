const { getMainPool } = require('../../config/database');

class Tenant {
    /**
     * Ensure subscription columns exist (lazy migration)
     */
    static async ensureSchema() {
        const pool = await getMainPool();
        const addColumn = async (colDef) => {
            try {
                await pool.execute(`ALTER TABLE tenants ADD COLUMN ${colDef}`);
            } catch (e) {
                // 1060 = Duplicate column, ignore
                if (e.errno !== 1060) console.error('Schema error:', e.message);
            }
        };
        await addColumn('subscription_start DATE NULL');
        await addColumn('subscription_end DATE NULL');
        await addColumn("plan_type ENUM('trial', '1_month', '3_months', '6_months', '1_year', 'lifetime', 'custom') DEFAULT 'trial'");

        // Update ENUM to include 'custom' if it doesn't have it
        try {
            await pool.execute(`ALTER TABLE tenants MODIFY COLUMN plan_type ENUM('trial', '1_month', '3_months', '6_months', '1_year', 'lifetime', 'custom') DEFAULT 'trial'`);
        } catch (e) {
            // Ignore if fails
        }
    }

    /**
     * Find all tenants
     */
    static async findAll() {
        await Tenant.ensureSchema();
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            'SELECT * FROM tenants ORDER BY created_at DESC'
        );
        return rows;
    }

    /**
     * Find tenant by ID
     * @param {number} id 
     */
    static async findById(id) {
        await Tenant.ensureSchema();
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            'SELECT * FROM tenants WHERE id = ?',
            [id]
        );
        return rows[0] || null;
    }

    /**
     * Find tenant by code
     * @param {string} code 
     */
    static async findByCode(code) {
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            'SELECT * FROM tenants WHERE code = ?',
            [code]
        );
        return rows[0] || null;
    }

    /**
     * Create new tenant
     * @param {Object} tenantData 
     */
    static async create(tenantData) {
        await Tenant.ensureSchema();
        const pool = await getMainPool();
        const databaseName = `saga_tenant_${tenantData.code.toLowerCase()}`;

        // Set default subscription: 30 days trial
        const today = new Date();
        const trialEnd = new Date(today);
        trialEnd.setDate(trialEnd.getDate() + 30);

        const [result] = await pool.execute(
            `INSERT INTO tenants (name, code, database_name, logo_url, address, phone, status, subscription_start, subscription_end, plan_type) 
       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
            [
                tenantData.name,
                tenantData.code,
                databaseName,
                tenantData.logo_url || null,
                tenantData.address || null,
                tenantData.phone || null,
                tenantData.status || 'active',
                today.toISOString().split('T')[0],
                trialEnd.toISOString().split('T')[0],
                'trial'
            ]
        );

        return {
            id: result.insertId,
            ...tenantData,
            database_name: databaseName
        };
    }

    /**
     * Update tenant
     * @param {number} id 
     * @param {Object} tenantData 
     */
    static async update(id, tenantData) {
        await Tenant.ensureSchema();
        const pool = await getMainPool();
        const fields = [];
        const values = [];

        if (tenantData.name) {
            fields.push('name = ?');
            values.push(tenantData.name);
        }
        if (tenantData.logo_url !== undefined) {
            fields.push('logo_url = ?');
            values.push(tenantData.logo_url);
        }
        if (tenantData.address !== undefined) {
            fields.push('address = ?');
            values.push(tenantData.address);
        }
        if (tenantData.phone !== undefined) {
            fields.push('phone = ?');
            values.push(tenantData.phone);
        }
        if (tenantData.status) {
            fields.push('status = ?');
            values.push(tenantData.status);
        }
        // Subscription fields
        if (tenantData.subscription_start !== undefined) {
            fields.push('subscription_start = ?');
            values.push(tenantData.subscription_start);
        }
        if (tenantData.subscription_end !== undefined) {
            fields.push('subscription_end = ?');
            values.push(tenantData.subscription_end);
        }
        if (tenantData.plan_type !== undefined) {
            fields.push('plan_type = ?');
            values.push(tenantData.plan_type);
        }

        if (fields.length === 0) return null;

        values.push(id);
        await pool.execute(
            `UPDATE tenants SET ${fields.join(', ')} WHERE id = ?`,
            values
        );

        return await Tenant.findById(id);
    }

    /**
     * Check if tenant subscription is active
     * @param {Object} tenant 
     * @returns {Object} { active: boolean, message: string, daysLeft: number }
     */
    static isSubscriptionActive(tenant) {
        // No subscription_end means legacy/unlimited
        if (!tenant.subscription_end) {
            return { active: true, message: 'Unlimited', daysLeft: -1 };
        }

        // Lifetime plan never expires
        if (tenant.plan_type === 'lifetime') {
            return { active: true, message: 'Lifetime', daysLeft: -1 };
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const endDate = new Date(tenant.subscription_end);
        endDate.setHours(0, 0, 0, 0);

        const diffTime = endDate - today;
        const daysLeft = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (daysLeft < 0) {
            return { active: false, message: 'Subscription expired', daysLeft: 0 };
        }

        return { active: true, message: `${daysLeft} days left`, daysLeft };
    }

    /**
     * Extend subscription by plan type
     * @param {number} id 
     * @param {string} planType 
     */
    static async extendSubscription(id, planType) {
        const tenant = await Tenant.findById(id);
        if (!tenant) return null;

        const planDays = {
            'trial': 30,
            '1_month': 30,
            '3_months': 90,
            '6_months': 180,
            '1_year': 365,
            'lifetime': 0 // Special case
        };

        const days = planDays[planType] || 30;

        // Start from today or current end date (whichever is later)
        const today = new Date();
        let startDate = today;

        if (tenant.subscription_end) {
            const currentEnd = new Date(tenant.subscription_end);
            if (currentEnd > today) {
                startDate = currentEnd;
            }
        }

        let endDate = null;
        if (planType !== 'lifetime') {
            endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + days);
        }

        return await Tenant.update(id, {
            subscription_start: today.toISOString().split('T')[0],
            subscription_end: endDate ? endDate.toISOString().split('T')[0] : null,
            plan_type: planType
        });
    }

    /**
     * Get active tenants count
     */
    static async countActive() {
        const pool = await getMainPool();
        const [rows] = await pool.execute(
            'SELECT COUNT(*) as count FROM tenants WHERE status = "active"'
        );
        return rows[0].count;
    }

    /**
     * Get tenants with user counts
     */
    static async findAllWithStats() {
        await Tenant.ensureSchema();
        const pool = await getMainPool();
        const [rows] = await pool.execute(`
      SELECT t.*, 
             COUNT(u.id) as user_count
      FROM tenants t
      LEFT JOIN users u ON t.id = u.tenant_id
      GROUP BY t.id
      ORDER BY t.created_at DESC
    `);
        return rows;
    }
}

module.exports = Tenant;

