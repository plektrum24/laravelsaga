const express = require('express');
const router = express.Router();
const { body, validationResult } = require('express-validator');
const Tenant = require('../../models/main/Tenant');
const multer = require('multer');
const path = require('path');
const fs = require('fs');

// Configure multer for logo upload
const storage = multer.diskStorage({
    destination: function (req, file, cb) {
        const uploadPath = path.join(__dirname, '../../uploads/logos');
        // Ensure directory exists
        if (!fs.existsSync(uploadPath)) {
            fs.mkdirSync(uploadPath, { recursive: true });
        }
        cb(null, uploadPath);
    },
    filename: function (req, file, cb) {
        // Create unique filename: tenant_id + timestamp + extension
        const ext = path.extname(file.originalname);
        const filename = `tenant_${req.user.tenant_id}_${Date.now()}${ext}`;
        cb(null, filename);
    }
});

const upload = multer({
    storage: storage,
    limits: { fileSize: 2 * 1024 * 1024 }, // 2MB max
    fileFilter: function (req, file, cb) {
        const allowedTypes = /jpeg|jpg|png|gif|webp/;
        const ext = allowedTypes.test(path.extname(file.originalname).toLowerCase());
        const mime = allowedTypes.test(file.mimetype);
        if (ext && mime) {
            cb(null, true);
        } else {
            cb(new Error('Only image files are allowed (jpg, png, gif, webp)'));
        }
    }
});

/**
 * POST /api/tenant-profile/logo
 * Upload tenant logo
 */
router.post('/logo', upload.single('logo'), async (req, res) => {
    try {
        if (req.user.role !== 'tenant_owner') {
            return res.status(403).json({
                success: false,
                message: 'Only tenant owner can upload logo'
            });
        }

        if (!req.file) {
            return res.status(400).json({
                success: false,
                message: 'No file uploaded'
            });
        }

        // Generate URL for the logo
        const logoUrl = `/uploads/logos/${req.file.filename}`;

        // Update tenant logo_url in database
        await Tenant.update(req.user.tenant_id, { logo_url: logoUrl });

        res.json({
            success: true,
            message: 'Logo uploaded successfully',
            data: { logo_url: logoUrl }
        });
    } catch (error) {
        console.error('Upload logo error:', error);
        res.status(500).json({
            success: false,
            message: error.message || 'Failed to upload logo'
        });
    }
});

/**
 * GET /api/tenant-profile
 * Get current tenant profile
 */
router.get('/', async (req, res) => {
    try {
        if (!req.user.tenant_id) {
            return res.status(400).json({
                success: false,
                message: 'No tenant associated with this user'
            });
        }

        const tenant = await Tenant.findById(req.user.tenant_id);
        if (!tenant) {
            return res.status(404).json({
                success: false,
                message: 'Tenant not found'
            });
        }

        res.json({
            success: true,
            data: {
                id: tenant.id,
                name: tenant.name,
                code: tenant.code,
                address: tenant.address,
                phone: tenant.phone,
                logo_url: tenant.logo_url,
                status: tenant.status
            }
        });
    } catch (error) {
        console.error('Get tenant profile error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to get tenant profile'
        });
    }
});

/**
 * PUT /api/tenant-profile
 * Update current tenant profile (Owner only)
 */
router.put('/', [
    body('name').optional().notEmpty().withMessage('Name cannot be empty'),
    body('address').optional(),
    body('phone').optional()
], async (req, res) => {
    try {
        // Only tenant_owner can update
        if (req.user.role !== 'tenant_owner') {
            return res.status(403).json({
                success: false,
                message: 'Only tenant owner can update profile'
            });
        }

        const errors = validationResult(req);
        if (!errors.isEmpty()) {
            return res.status(400).json({
                success: false,
                errors: errors.array()
            });
        }

        const { name, address, phone, logo_url } = req.body;
        const updateData = {};

        if (name) updateData.name = name;
        if (address !== undefined) updateData.address = address;
        if (phone !== undefined) updateData.phone = phone;
        if (logo_url !== undefined) updateData.logo_url = logo_url;

        await Tenant.update(req.user.tenant_id, updateData);

        // Fetch updated tenant
        const tenant = await Tenant.findById(req.user.tenant_id);

        res.json({
            success: true,
            message: 'Tenant profile updated successfully',
            data: {
                id: tenant.id,
                name: tenant.name,
                code: tenant.code,
                address: tenant.address,
                phone: tenant.phone,
                logo_url: tenant.logo_url
            }
        });
    } catch (error) {
        console.error('Update tenant profile error:', error);
        res.status(500).json({
            success: false,
            message: 'Failed to update tenant profile'
        });
    }
});

module.exports = router;
