const express = require('express');
const router = express.Router();
const fs = require('fs');
const path = require('path');
const bcrypt = require('bcryptjs');
const { getMainPool } = require('../config/database');
const LicenseService = require('../services/LicenseService');

// Setup status file
const SETUP_FILE = path.join(__dirname, '../../.setup_complete');

/**
 * GET /api/setup/status
 * Check if initial setup has been completed
 */
router.get('/status', async (req, res) => {
    try {
        const isComplete = fs.existsSync(SETUP_FILE);
        const machineId = await LicenseService.getMachineId();
        const licenseStatus = await LicenseService.verifyLicense();

        res.json({
            success: true,
            data: {
                setupComplete: isComplete,
                machineId: machineId,
                licenseValid: licenseStatus.valid,
                licenseType: licenseStatus.type || null,
                licenseExpiry: licenseStatus.expiry || null
            }
        });
    } catch (error) {
        console.error('Setup status error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

/**
 * POST /api/setup/initialize
 * Initialize the database schema and create admin user
 */
router.post('/initialize', async (req, res) => {
    try {
        const { adminName, adminUsername, adminPassword, storeName } = req.body;

        // Validate input
        if (!adminName || !adminUsername || !adminPassword) {
            return res.status(400).json({
                success: false,
                message: 'Nama, username, dan password wajib diisi'
            });
        }

        if (adminPassword.length < 6) {
            return res.status(400).json({
                success: false,
                message: 'Password minimal 6 karakter'
            });
        }

        const pool = await getMainPool();

        // Check if admin already exists
        const [existingUsers] = await pool.execute(
            'SELECT id FROM users WHERE role = "super_admin" LIMIT 1'
        );

        if (existingUsers.length > 0) {
            return res.status(400).json({
                success: false,
                message: 'Admin sudah ada. Setup sudah pernah dilakukan.'
            });
        }

        // Hash password
        const hashedPassword = await bcrypt.hash(adminPassword, 10);

        // Create super admin user
        const [result] = await pool.execute(
            `INSERT INTO users (name, username, password, role, is_active) 
             VALUES (?, ?, ?, 'super_admin', true)`,
            [adminName, adminUsername, hashedPassword]
        );

        // Create setup complete flag
        fs.writeFileSync(SETUP_FILE, JSON.stringify({
            completedAt: new Date().toISOString(),
            storeName: storeName || 'SAGA TOKO',
            adminUsername: adminUsername
        }));

        res.json({
            success: true,
            message: 'Setup berhasil! Silakan login dengan akun admin.',
            data: {
                adminId: result.insertId,
                adminUsername: adminUsername
            }
        });
    } catch (error) {
        console.error('Setup initialize error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

/**
 * POST /api/setup/activate-license
 * Activate license during setup
 */
router.post('/activate-license', async (req, res) => {
    try {
        const { licenseKey } = req.body;

        if (!licenseKey) {
            return res.status(400).json({
                success: false,
                message: 'License key wajib diisi'
            });
        }

        const result = await LicenseService.activateLicense(licenseKey);

        if (result.success) {
            res.json({
                success: true,
                message: 'Lisensi berhasil diaktifkan!'
            });
        } else {
            res.status(400).json({
                success: false,
                message: result.message || 'License key tidak valid'
            });
        }
    } catch (error) {
        console.error('Activate license error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

/**
 * GET /api/setup/check-database
 * Check if database connection is working
 */
router.get('/check-database', async (req, res) => {
    try {
        const pool = await getMainPool();
        await pool.execute('SELECT 1');

        res.json({
            success: true,
            message: 'Database connection OK',
            data: {
                host: process.env.DB_HOST,
                port: process.env.DB_PORT,
                database: process.env.DB_NAME
            }
        });
    } catch (error) {
        console.error('Database check error:', error);
        res.status(500).json({
            success: false,
            message: 'Database connection failed: ' + error.message
        });
    }
});

module.exports = router;
