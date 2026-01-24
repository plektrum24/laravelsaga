const express = require('express');
const router = express.Router();
const UpdateService = require('../services/UpdateService');

/**
 * GET /api/updates/version
 * Get current app version
 */
router.get('/version', (req, res) => {
    res.json({
        success: true,
        data: {
            version: UpdateService.getCurrentVersion(),
            appName: 'SAGA TOKO APP'
        }
    });
});

/**
 * GET /api/updates/check
 * Check for available updates
 */
router.get('/check', async (req, res) => {
    try {
        const updateInfo = await UpdateService.checkForUpdates();

        // Update last check time
        const settings = UpdateService.getUpdateSettings();
        settings.lastCheck = new Date().toISOString();
        UpdateService.saveUpdateSettings(settings);

        res.json({
            success: true,
            data: updateInfo
        });
    } catch (error) {
        console.error('Update check error:', error);
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

/**
 * POST /api/updates/download
 * Download the latest update
 */
router.post('/download', async (req, res) => {
    try {
        const updateInfo = await UpdateService.checkForUpdates();

        if (!updateInfo.hasUpdate) {
            return res.json({
                success: false,
                message: 'No update available'
            });
        }

        if (!updateInfo.downloadUrl) {
            return res.status(400).json({
                success: false,
                message: 'No download URL available'
            });
        }

        const result = await UpdateService.downloadUpdate(updateInfo.downloadUrl);

        res.json({
            success: true,
            message: 'Update downloaded successfully',
            data: result
        });
    } catch (error) {
        console.error('Download update error:', error);
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

/**
 * GET /api/updates/settings
 * Get update settings
 */
router.get('/settings', (req, res) => {
    res.json({
        success: true,
        data: UpdateService.getUpdateSettings()
    });
});

/**
 * POST /api/updates/settings
 * Update settings
 */
router.post('/settings', (req, res) => {
    try {
        const currentSettings = UpdateService.getUpdateSettings();
        const newSettings = { ...currentSettings, ...req.body };
        UpdateService.saveUpdateSettings(newSettings);

        res.json({
            success: true,
            message: 'Settings saved',
            data: newSettings
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

/**
 * POST /api/updates/skip
 * Skip a specific version
 */
router.post('/skip', (req, res) => {
    try {
        const { version } = req.body;
        const settings = UpdateService.getUpdateSettings();
        settings.skipVersion = version;
        UpdateService.saveUpdateSettings(settings);

        res.json({
            success: true,
            message: `Version ${version} will be skipped`
        });
    } catch (error) {
        res.status(500).json({
            success: false,
            message: error.message
        });
    }
});

module.exports = router;
