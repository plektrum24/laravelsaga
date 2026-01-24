const express = require('express');
const router = express.Router();
const LicenseService = require('../../services/LicenseService');

// Generate License Key (Super Admin Only)
router.post('/generate', async (req, res) => {
    // Note: Caller must ensure user is super_admin via middleware in server.js
    const { machineId, duration } = req.body;

    if (!machineId) {
        return res.status(400).json({ success: false, message: 'Machine ID is required' });
    }
    if (!duration) {
        return res.status(400).json({ success: false, message: 'Duration is required' });
    }

    try {
        const licenseKey = LicenseService.generateLicense(machineId, duration);
        res.json({
            success: true,
            machineId,
            duration,
            licenseKey
        });
    } catch (error) {
        res.status(500).json({ success: false, message: error.message });
    }
});

module.exports = router;
