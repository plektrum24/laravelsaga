const express = require('express');
const router = express.Router();
const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const multer = require('multer');
const { exec } = require('child_process');
const { findMysqldump } = require('../../services/BackupScheduler');

// Configure upload
const UPLOAD_DIR = path.join(__dirname, '../../uploads/temp');
if (!fs.existsSync(UPLOAD_DIR)) {
    fs.mkdirSync(UPLOAD_DIR, { recursive: true });
}
const upload = multer({ dest: UPLOAD_DIR });

// Backup directory
const BACKUP_DIR = path.join(__dirname, '../../../backups');
const MAX_BACKUPS = 7;

// Ensure backup directory exists
if (!fs.existsSync(BACKUP_DIR)) {
    fs.mkdirSync(BACKUP_DIR, { recursive: true });
}

/**
 * GET /api/backup/info
 * Get simple stats for the backup page
 */
router.get('/info', async (req, res) => {
    try {
        const [products] = await req.tenantDb.query('SELECT COUNT(*) as count FROM products');
        const [categories] = await req.tenantDb.query('SELECT COUNT(*) as count FROM categories');
        const [transactions] = await req.tenantDb.query('SELECT COUNT(*) as count FROM transactions');
        const [shifts] = await req.tenantDb.query('SELECT COUNT(*) as count FROM shifts');

        res.json({
            success: true,
            data: {
                Products: products[0].count,
                Categories: categories[0].count,
                Transactions: transactions[0].count,
                Shifts: shifts[0].count
            }
        });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

/**
 * GET /api/backup/download
 * Download Full Backup as SQL (mysqldump)
 */
router.get('/download', async (req, res) => {
    try {
        const tenantId = req.user.tenant_id;
        if (!tenantId) throw new Error('Tenant ID not found in token');

        // We need database name. Connect to tenant DB is already there (req.tenantDb)
        // But we need the name for mysqldump. 
        // We can query SELECT DATABASE() 
        const [rows] = await req.tenantDb.query('SELECT DATABASE() as dbName');
        const dbName = rows[0].dbName;

        const mysqldumpBin = findMysqldump();
        const host = process.env.DB_HOST || 'localhost';
        const user = process.env.DB_USER || 'root';
        const password = process.env.DB_PASSWORD || '';
        const port = process.env.DB_PORT || 3307;

        const filename = `backup_saga_full_${new Date().toISOString().replace(/[:.]/g, '-')}.sql`;
        const tempPath = path.join(UPLOAD_DIR, filename);

        // Command
        let cmd = `${mysqldumpBin} -h ${host} -P ${port} -u ${user}`;
        if (password) cmd += ` -p"${password}"`;
        cmd += ` --routines --triggers --databases ${dbName} > "${tempPath}"`;

        exec(cmd, (error, stdout, stderr) => {
            if (error) {
                console.error('Backup download failed:', error);
                return res.status(500).json({ success: false, message: 'Backup generation failed: ' + error.message + (stderr ? ' - ' + stderr : '') });
            }

            res.download(tempPath, filename, (err) => {
                if (err) console.error('Download error:', err);
                // Cleanup
                try { fs.unlinkSync(tempPath); } catch (e) { }
            });
        });

    } catch (error) {
        console.error('Backup download error:', error);
        res.status(500).json({ success: false, message: error.message });
    }
});

/**
 * POST /api/backup/restore
 * Restore from SQL file (mysql)
 */
router.post('/restore', upload.single('file'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ success: false, message: 'No file uploaded' });
        }

        const [rows] = await req.tenantDb.query('SELECT DATABASE() as dbName');
        const dbName = rows[0].dbName; // Active Tenant DB

        // We need 'mysql' binary. Generally in same folder as 'mysqldump'
        const mysqldumpBin = findMysqldump();
        // Replace 'mysqldump' with 'mysql'
        const mysqlBin = mysqldumpBin.replace('mysqldump', 'mysql');

        const host = process.env.DB_HOST || 'localhost';
        const user = process.env.DB_USER || 'root';
        const password = process.env.DB_PASSWORD || '';
        const port = process.env.DB_PORT || 3307;

        // Command: mysql -u [user] -p[pass] [db] < [file]
        let cmd = `${mysqlBin} -h ${host} -P ${port} -u ${user}`;
        if (password) cmd += ` -p"${password}"`;
        cmd += ` ${dbName} < "${req.file.path}"`;

        console.log(`Restoring backup to ${dbName}...`);

        exec(cmd, (error, stdout, stderr) => {
            // Cleanup uploaded file
            try { fs.unlinkSync(req.file.path); } catch (e) { }

            if (error) {
                console.error('Restore failed:', error);
                return res.status(500).json({ success: false, message: 'Restore failed: ' + error.message });
            }

            res.json({
                success: true,
                message: 'Restore completed successfully (SQL)',
                data: {} // No stats for SQL restore usually
            });
        });

    } catch (error) {
        console.error('Restore error:', error);
        if (req.file && fs.existsSync(req.file.path)) fs.unlinkSync(req.file.path);
        res.status(500).json({ success: false, message: error.message });
    }
});

// KEEP OLD SQL ROUTES FOR COMPATIBILITY (but simplified/hidden)
router.get('/list', (req, res) => res.json({ success: true, data: { backups: [] } }));
router.post('/create', (req, res) => res.json({ success: false, message: 'Use JSON Backup instead' }));

/**
 * GET /api/backup/settings
 * Get backup settings
 */
router.get('/settings', async (req, res) => {
    try {
        const settingsPath = path.join(BACKUP_DIR, 'settings.json');
        let settings = {
            autoBackup: false,
            backupTime: '22:00',
            backupPath: '', // Default empty (use app dir)
            lastBackup: null
        };

        if (fs.existsSync(settingsPath)) {
            const saved = JSON.parse(fs.readFileSync(settingsPath, 'utf8'));
            settings = { ...settings, ...saved };
        }

        res.json({ success: true, data: settings });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

/**
 * POST /api/backup/settings
 * Update backup settings
 */
router.post('/settings', async (req, res) => {
    try {
        const settingsPath = path.join(BACKUP_DIR, 'settings.json');
        const settings = req.body;

        // Auto-create directory if possible
        if (settings.backupPath) {
            try {
                if (!fs.existsSync(settings.backupPath)) {
                    fs.mkdirSync(settings.backupPath, { recursive: true });
                }
                // Test write permission
                const testFile = path.join(settings.backupPath, '.write_test');
                fs.writeFileSync(testFile, 'ok');
                fs.unlinkSync(testFile);
            } catch (e) {
                return res.status(400).json({ success: false, message: 'Cannot write to target path: ' + e.message });
            }
        }

        fs.writeFileSync(settingsPath, JSON.stringify(settings, null, 2));
        res.json({ success: true, message: 'Settings saved' });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

/**
 * POST /api/backup/test-path
 * Test if path is writable
 */
router.post('/test-path', async (req, res) => {
    try {
        const { path: targetPath } = req.body;
        if (!targetPath) {
            return res.status(400).json({ success: false, message: 'Path is empty' });
        }

        // Try to create if not exists
        try {
            if (!fs.existsSync(targetPath)) {
                fs.mkdirSync(targetPath, { recursive: true });
            }

            // Try writing a test file settings
            const testFile = path.join(targetPath, 'test_write_saga.tmp');
            fs.writeFileSync(testFile, 'test');
            fs.unlinkSync(testFile);

            res.json({ success: true, message: 'Path is valid and writable' });
        } catch (e) {
            return res.status(400).json({ success: false, message: 'Path not writable: ' + e.message });
        }
    } catch (error) {
        res.status(500).json({ success: false, message: 'Server error: ' + error.message });
    }
});

module.exports = router;
