const express = require('express');
const router = express.Router();
const multer = require('multer');
const sharp = require('sharp');
const path = require('path');
const fs = require('fs');

// Ensure upload directory exists
const uploadDir = path.join(__dirname, '../../public/uploads/products');
if (!fs.existsSync(uploadDir)) {
    fs.mkdirSync(uploadDir, { recursive: true });
}

// Configure multer for memory storage (we'll process with sharp before saving)
const storage = multer.memoryStorage();

const fileFilter = (req, file, cb) => {
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    if (allowedTypes.includes(file.mimetype)) {
        cb(null, true);
    } else {
        cb(new Error('Invalid file type. Only JPG, PNG, and WEBP are allowed.'), false);
    }
};

const upload = multer({
    storage: storage,
    limits: {
        fileSize: 5 * 1024 * 1024 // 5MB max (before resize)
    },
    fileFilter: fileFilter
});

// Fixed image dimensions
const IMAGE_WIDTH = 400;
const IMAGE_HEIGHT = 400;

/**
 * POST /api/upload/product-image
 * Upload and resize product image to 400x400 pixels
 */
router.post('/product-image', upload.single('image'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ success: false, message: 'No file uploaded' });
        }

        // Generate unique filename
        const timestamp = Date.now();
        const filename = `product_${timestamp}.jpg`;
        const filepath = path.join(uploadDir, filename);

        // Resize and convert to JPEG using sharp
        await sharp(req.file.buffer)
            .resize(IMAGE_WIDTH, IMAGE_HEIGHT, {
                fit: 'cover',      // Crop to fill 400x400
                position: 'center' // Center the crop
            })
            .jpeg({ quality: 85 }) // Convert to JPEG with 85% quality
            .toFile(filepath);

        // Return the URL path (relative to public folder)
        const imageUrl = `/uploads/products/${filename}`;

        console.log('[UPLOAD] Product image saved:', imageUrl);

        res.json({
            success: true,
            message: 'Image uploaded successfully',
            data: {
                url: imageUrl,
                width: IMAGE_WIDTH,
                height: IMAGE_HEIGHT
            }
        });

    } catch (error) {
        console.error('[UPLOAD] Error:', error);
        res.status(500).json({ success: false, message: 'Failed to upload image: ' + error.message });
    }
});

/**
 * DELETE /api/upload/product-image
 * Delete a product image
 */
router.delete('/product-image', async (req, res) => {
    try {
        const { url } = req.body;
        if (!url) {
            return res.status(400).json({ success: false, message: 'No URL provided' });
        }

        // Extract filename from URL
        const filename = path.basename(url);
        const filepath = path.join(uploadDir, filename);

        // Check if file exists and delete
        if (fs.existsSync(filepath)) {
            fs.unlinkSync(filepath);
            console.log('[UPLOAD] Deleted:', filename);
        }

        res.json({ success: true, message: 'Image deleted' });

    } catch (error) {
        console.error('[UPLOAD] Delete error:', error);
        res.status(500).json({ success: false, message: 'Failed to delete image' });
    }
});

/**
 * POST /api/upload/qris-image
 * Upload QRIS payment image (no resize, keep original aspect ratio)
 */
const qrisDir = path.join(__dirname, '../../public/uploads/qris');
if (!fs.existsSync(qrisDir)) {
    fs.mkdirSync(qrisDir, { recursive: true });
}

router.post('/qris-image', upload.single('image'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({ success: false, message: 'No file uploaded' });
        }

        // Generate filename (always qris.png to overwrite previous)
        const filename = `qris_${req.tenantId || 'default'}.png`;
        const filepath = path.join(qrisDir, filename);

        // Resize to max 400px width while keeping aspect ratio
        await sharp(req.file.buffer)
            .resize(400, null, { fit: 'inside' })
            .png({ quality: 90 })
            .toFile(filepath);

        const imageUrl = `/uploads/qris/${filename}`;
        console.log('[UPLOAD] QRIS image saved:', imageUrl);

        res.json({
            success: true,
            message: 'QRIS image uploaded',
            data: { url: imageUrl }
        });

    } catch (error) {
        console.error('[UPLOAD] QRIS Error:', error);
        res.status(500).json({ success: false, message: 'Failed to upload QRIS: ' + error.message });
    }
});

module.exports = router;
