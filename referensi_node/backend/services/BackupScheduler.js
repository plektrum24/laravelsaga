/**
 * SAGA TOKO - Backup Scheduler
 * Runs automatic backups at configured time using mysqldump
 */

const fs = require('fs');
const path = require('path');
const mysql = require('mysql2/promise');
const { exec } = require('child_process');

// Default Backup Directory
const BACKUP_DIR = path.join(__dirname, '../../backups');
const SETTINGS_FILE = path.join(BACKUP_DIR, 'settings.json');
const MAX_BACKUPS = 7;

let schedulerInterval = null;
let lastCheckDate = null;

// Initialize the backup scheduler
function initBackupScheduler() {
    console.log('📅 Backup scheduler initialized');

    // Ensure backup directory exists
    if (!fs.existsSync(BACKUP_DIR)) {
        fs.mkdirSync(BACKUP_DIR, { recursive: true });
    }

    // Check every minute
    schedulerInterval = setInterval(checkAndRunBackup, 60000);

    // Also check immediately on startup
    setTimeout(checkAndRunBackup, 5000);
}

// Find mysqldump executable
function findMysqldump() {
    // 1. Try environment variable or settings
    // TODO: Add setting support

    // 2. Common paths
    const commonPaths = [
        'C:\\xampp\\mysql\\bin\\mysqldump.exe', // User's XAMPP Path
        'C:\\laragon\\bin\\mysql\\current\\bin\\mysqldump.exe',
        'D:\\Project Aplikasi\\sagatokov3\\Build_Exe\\mariadb\\mariadb-12.1.2-winx64\\bin\\mysqldump.exe',
        // Add more local paths here
        'mysqldump', // System PATH (Fallback)
    ];

    for (const p of commonPaths) {
        try {
            if (p === 'mysqldump') return p; // Assume in PATH if generic
            if (fs.existsSync(p)) return `"${p}"`;
        } catch (e) { }
    }
    return 'mysqldump'; // Fallback to PATH
}

// Check if it's time to run backup
function checkAndRunBackup() {
    try {
        const settings = getSettings();

        if (!settings.autoBackup) {
            return; // Auto backup disabled
        }

        const now = new Date();
        const currentTime = now.toTimeString().slice(0, 5); // HH:MM
        const today = now.toISOString().slice(0, 10); // YYYY-MM-DD

        // Check if we already ran backup today
        if (lastCheckDate === today) {
            return;
        }

        // Check if it's backup time
        if (currentTime === settings.backupTime) {
            console.log('⏰ Backup time! Running automatic backup...');
            runBackup(settings);
            lastCheckDate = today;

            // Update last backup time in settings
            settings.lastBackup = now.toISOString();
            saveSettings(settings);
        }
    } catch (error) {
        console.error('Backup scheduler error:', error);
    }
}

// Run the actual backup (SQL)
async function runBackup(settings) {
    let mainDb = null;

    try {
        // 1. Determine Target Path
        let targetDir = BACKUP_DIR;
        if (settings && settings.backupPath && fs.existsSync(settings.backupPath)) {
            targetDir = settings.backupPath;
            console.log('📂 Using custom backup path:', targetDir);
        }

        const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19);
        const filename = `auto_backup_saga_full_${timestamp}.sql`;
        const filepath = path.join(targetDir, filename);

        // 2. Connect to Main DB to get Tenant info
        mainDb = await mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USER || 'root',
            password: process.env.DB_PASSWORD || '',
            database: process.env.DB_NAME || 'saga_main',
            port: process.env.DB_PORT || 3307
        });

        // Get first active tenant (simplified for single-tenant POS)
        const [tenants] = await mainDb.query('SELECT * FROM tenants LIMIT 1');
        if (tenants.length === 0) {
            console.error('❌ Auto backup failed: No tenant found');
            return;
        }
        const tenant = tenants[0];
        const dbName = tenant.database_name;

        // 3. Construct mysqldump command
        const mysqldumpBin = findMysqldump();
        const host = process.env.DB_HOST || 'localhost';
        const user = process.env.DB_USER || 'root';
        const password = process.env.DB_PASSWORD || '';
        const port = process.env.DB_PORT || 3307;

        // Command: mysqldump -h [host] -P [port] -u [user] -p[pass] --routines --triggers [db] > [file]
        // Note: Password handling is tricky safely. Using -p[pass] (no space)
        let cmd = `${mysqldumpBin} -h ${host} -P ${port} -u ${user}`;
        if (password) {
            cmd += ` -p"${password}"`;
        }
        cmd += ` --routines --triggers --databases ${dbName} > "${filepath}"`;

        console.log(`📦 Running mysqldump for tenant ${tenant.name}...`);

        exec(cmd, (error, stdout, stderr) => {
            if (error) {
                console.error(`❌ Auto backup execution failed: ${error.message}`);
                return;
            }
            if (stderr) {
                // mysqldump writes to stderr for info too, so just log it
                // console.log(`Backup info: ${stderr}`);
            }

            try {
                if (fs.existsSync(filepath)) {
                    const stats = fs.statSync(filepath);
                    console.log(`✅ Auto backup created: ${filename} (${formatBytes(stats.size)})`);
                    cleanupOldBackups(targetDir);
                } else {
                    console.error('❌ Auto backup failed: File not created');
                }
            } catch (err) {
                console.error('❌ Auto backup verification failed:', err);
            }
        });

    } catch (error) {
        console.error('❌ Auto backup setup failed:', error.message);
    } finally {
        if (mainDb) await mainDb.end();
    }
}

// Cleanup old backups
function cleanupOldBackups(directory) {
    try {
        const files = fs.readdirSync(directory)
            .filter(f => f.includes('backup_saga') && (f.endsWith('.json') || f.endsWith('.sql')))
            .map(f => ({
                name: f,
                time: fs.statSync(path.join(directory, f)).mtime.getTime()
            }))
            .sort((a, b) => b.time - a.time);

        if (files.length > MAX_BACKUPS) {
            files.slice(MAX_BACKUPS).forEach(f => {
                fs.unlinkSync(path.join(directory, f.name));
                console.log(`🗑️ Deleted old backup: ${f.name}`);
            });
        }
    } catch (error) {
        console.error('Cleanup error:', error);
    }
}

// Get backup settings
function getSettings() {
    const defaults = {
        autoBackup: false,
        backupTime: '22:00',
        backupPath: '',
        maxBackups: MAX_BACKUPS,
        lastBackup: null
    };

    try {
        if (fs.existsSync(SETTINGS_FILE)) {
            return { ...defaults, ...JSON.parse(fs.readFileSync(SETTINGS_FILE, 'utf8')) };
        }
    } catch (error) {
        console.error('Error reading backup settings:', error);
    }

    return defaults;
}

// Save backup settings
function saveSettings(settings) {
    try {
        fs.writeFileSync(SETTINGS_FILE, JSON.stringify(settings, null, 2));
    } catch (error) {
        console.error('Error saving backup settings:', error);
    }
}

// Format bytes helper
function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Stop the scheduler
function stopBackupScheduler() {
    if (schedulerInterval) {
        clearInterval(schedulerInterval);
        schedulerInterval = null;
        console.log('📅 Backup scheduler stopped');
    }
}

module.exports = {
    initBackupScheduler,
    stopBackupScheduler,
    runBackup,
    findMysqldump // Export for other uses
};
