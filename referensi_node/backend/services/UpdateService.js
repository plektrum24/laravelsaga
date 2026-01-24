/**
 * SAGA TOKO - Update Service
 * Checks for updates and handles download/install
 */

const https = require('https');
const http = require('http');
const fs = require('fs');
const path = require('path');

// Current app version (read from package.json)
let packageJson;
try {
    // Try root package.json (Development)
    packageJson = require('../../package.json');
} catch (e) {
    try {
        // Try backend package.json (Production/Bundled)
        packageJson = require('../package.json');
    } catch (e2) {
        packageJson = { version: '1.0.0' };
    }
}
const CURRENT_VERSION = packageJson.version || '1.0.0';

// Update server configuration
// Can be changed to your own server or GitHub releases
const UPDATE_CONFIG = {
    // Option 1: Custom server
    checkUrl: 'https://raw.githubusercontent.com/your-repo/sagatoko/main/version.json',
    // Option 2: Local file for testing
    localVersionFile: path.join(__dirname, '../../version.json')
};

// Update download directory
const UPDATE_DIR = path.join(__dirname, '../../updates');

/**
 * Get current version
 */
function getCurrentVersion() {
    return CURRENT_VERSION;
}

/**
 * Check for updates
 * Returns: { hasUpdate: boolean, currentVersion, latestVersion, downloadUrl, changelog }
 */
async function checkForUpdates() {
    return new Promise((resolve) => {
        // First try local version file (for development/testing)
        if (fs.existsSync(UPDATE_CONFIG.localVersionFile)) {
            try {
                const versionInfo = JSON.parse(fs.readFileSync(UPDATE_CONFIG.localVersionFile, 'utf8'));
                const hasUpdate = compareVersions(versionInfo.version, CURRENT_VERSION) > 0;
                return resolve({
                    hasUpdate,
                    currentVersion: CURRENT_VERSION,
                    latestVersion: versionInfo.version,
                    downloadUrl: versionInfo.downloadUrl || null,
                    changelog: versionInfo.changelog || [],
                    releaseDate: versionInfo.releaseDate || null
                });
            } catch (error) {
                console.error('Error reading local version file:', error);
            }
        }

        // Try remote update server
        const url = UPDATE_CONFIG.checkUrl;
        const client = url.startsWith('https') ? https : http;

        const request = client.get(url, { timeout: 10000 }, (response) => {
            let data = '';

            response.on('data', (chunk) => {
                data += chunk;
            });

            response.on('end', () => {
                try {
                    const versionInfo = JSON.parse(data);
                    const hasUpdate = compareVersions(versionInfo.version, CURRENT_VERSION) > 0;
                    resolve({
                        hasUpdate,
                        currentVersion: CURRENT_VERSION,
                        latestVersion: versionInfo.version,
                        downloadUrl: versionInfo.downloadUrl || null,
                        changelog: versionInfo.changelog || [],
                        releaseDate: versionInfo.releaseDate || null
                    });
                } catch (error) {
                    resolve({
                        hasUpdate: false,
                        currentVersion: CURRENT_VERSION,
                        latestVersion: CURRENT_VERSION,
                        error: 'Failed to parse update info'
                    });
                }
            });
        });

        request.on('error', (error) => {
            console.error('Update check error:', error.message);
            resolve({
                hasUpdate: false,
                currentVersion: CURRENT_VERSION,
                latestVersion: CURRENT_VERSION,
                error: 'Could not connect to update server'
            });
        });

        request.on('timeout', () => {
            request.destroy();
            resolve({
                hasUpdate: false,
                currentVersion: CURRENT_VERSION,
                latestVersion: CURRENT_VERSION,
                error: 'Update check timed out'
            });
        });
    });
}

/**
 * Download update file
 * @param {string} downloadUrl - URL to download from
 * @param {function} onProgress - Progress callback (percent)
 */
async function downloadUpdate(downloadUrl, onProgress = () => { }) {
    return new Promise((resolve, reject) => {
        // Ensure update directory exists
        if (!fs.existsSync(UPDATE_DIR)) {
            fs.mkdirSync(UPDATE_DIR, { recursive: true });
        }

        const filename = path.basename(downloadUrl) || 'SagaToko-Update.exe';
        const filepath = path.join(UPDATE_DIR, filename);
        const file = fs.createWriteStream(filepath);

        const client = downloadUrl.startsWith('https') ? https : http;

        const request = client.get(downloadUrl, (response) => {
            const totalSize = parseInt(response.headers['content-length'], 10);
            let downloadedSize = 0;

            response.on('data', (chunk) => {
                downloadedSize += chunk.length;
                if (totalSize) {
                    const percent = Math.round((downloadedSize / totalSize) * 100);
                    onProgress(percent);
                }
            });

            response.pipe(file);

            file.on('finish', () => {
                file.close();
                resolve({
                    success: true,
                    filepath,
                    filename,
                    size: downloadedSize
                });
            });
        });

        request.on('error', (error) => {
            fs.unlink(filepath, () => { }); // Cleanup partial file
            reject(error);
        });

        file.on('error', (error) => {
            fs.unlink(filepath, () => { });
            reject(error);
        });
    });
}

/**
 * Compare version strings
 * Returns: 1 if v1 > v2, -1 if v1 < v2, 0 if equal
 */
function compareVersions(v1, v2) {
    const parts1 = v1.split('.').map(Number);
    const parts2 = v2.split('.').map(Number);

    for (let i = 0; i < Math.max(parts1.length, parts2.length); i++) {
        const p1 = parts1[i] || 0;
        const p2 = parts2[i] || 0;
        if (p1 > p2) return 1;
        if (p1 < p2) return -1;
    }
    return 0;
}

/**
 * Get update settings
 */
function getUpdateSettings() {
    const settingsPath = path.join(UPDATE_DIR, 'settings.json');
    const defaults = {
        autoCheck: true,
        checkInterval: 24, // hours
        lastCheck: null,
        skipVersion: null
    };

    try {
        if (fs.existsSync(settingsPath)) {
            return { ...defaults, ...JSON.parse(fs.readFileSync(settingsPath, 'utf8')) };
        }
    } catch (error) {
        console.error('Error reading update settings:', error);
    }

    return defaults;
}

/**
 * Save update settings
 */
function saveUpdateSettings(settings) {
    const settingsPath = path.join(UPDATE_DIR, 'settings.json');

    if (!fs.existsSync(UPDATE_DIR)) {
        fs.mkdirSync(UPDATE_DIR, { recursive: true });
    }

    fs.writeFileSync(settingsPath, JSON.stringify(settings, null, 2));
}

module.exports = {
    getCurrentVersion,
    checkForUpdates,
    downloadUpdate,
    compareVersions,
    getUpdateSettings,
    saveUpdateSettings,
    UPDATE_DIR
};
