const express = require('express');
const router = express.Router();
const LicenseService = require('../services/LicenseService');

// Get Machine ID (Used for Activation UI)
router.get('/machine-id', async (req, res) => {
    try {
        const machineId = await LicenseService.getMachineId();
        res.json({ success: true, machineId });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

// Check License Status
router.get('/check', async (req, res) => {
    const check = await LicenseService.verifyLicense();
    res.json(check);
});

// Activate License
router.post('/activate', async (req, res) => {
    const { key } = req.body;
    if (!key) return res.status(400).json({ success: false, message: 'Key is required' });

    const result = await LicenseService.activateLicense(key);
    if (result.success) {
        res.json({ success: true, message: 'Activation successful! Please restart the application.' });
    } else {
        res.status(400).json({ success: false, message: result.message });
    }
});

module.exports = router;
