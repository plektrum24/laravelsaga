const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');

// SECRET KEY for signing. Change this to something random and secret!
// This keys MUST match the one used in the Key Generator.
const SECRET_SALT = 'SAGA_TOKO_OFFICIAL_SECURE_KEY_2024_XZ9';

const LICENSE_FILE = path.join(__dirname, '../../license.key');
const MACHINE_ID_FILE = path.join(__dirname, '../../machine.id');

let cachedId = null; // In-memory cache

class LicenseService {

    /**
     * Get unique Machine ID (UUID)
     */
    static getMachineId() {
        return new Promise((resolve, reject) => {
            if (cachedId) return resolve(cachedId);

            // Check File Cache First (Priority!)
            try {
                if (fs.existsSync(MACHINE_ID_FILE)) {
                    cachedId = fs.readFileSync(MACHINE_ID_FILE, 'utf8').trim();
                    if (cachedId && cachedId.length > 5) return resolve(cachedId);
                }
            } catch (e) { console.error('Error reading machine.id', e); }

            const platform = process.platform;

            if (platform === 'win32') {
                exec('wmic csproduct get uuid', { windowsHide: true }, (err, stdout) => {
                    if (err || !stdout) {
                        return resolve(this.getPersistentFallbackId());
                    }
                    // Parse output like:
                    // UUID
                    // 4C4C4544-0042-4410-8056-B9C04F343232
                    const lines = stdout.trim().split('\n');
                    let id = '';
                    if (lines.length > 1) {
                        id = lines[1].trim();
                    } else {
                        id = lines[0].trim();
                    }

                    if (!id || id === 'UUID') {
                        cachedId = this.getPersistentFallbackId();
                        return resolve(cachedId);
                    }
                    if (!id || id === 'UUID') {
                        cachedId = this.getPersistentFallbackId();
                        return resolve(cachedId);
                    }

                    // Save to file for next time
                    try {
                        fs.writeFileSync(MACHINE_ID_FILE, id);
                    } catch (e) { }

                    cachedId = id;
                    resolve(id);
                });
            } else {
                cachedId = this.getPersistentFallbackId();
                resolve(cachedId);
            }
        });
    }

    /**
     * Generate or retrieve a persistent random ID if hardware fetch fails
     */
    static getPersistentFallbackId() {
        if (fs.existsSync(MACHINE_ID_FILE)) {
            return fs.readFileSync(MACHINE_ID_FILE, 'utf8').trim();
        }

        const newId = 'FALLBACK-ID-' + crypto.randomBytes(4).toString('hex').toUpperCase();
        try {
            fs.writeFileSync(MACHINE_ID_FILE, newId);
        } catch (e) {
            console.error('Failed to write machine id file', e);
        }
        return newId;
    }

    /**
     * Verify if the current machine has a valid license
     */
    static async verifyLicense() {
        try {
            if (!fs.existsSync(LICENSE_FILE)) {
                return { valid: false, reason: 'No license file found' };
            }

            const licenseContent = fs.readFileSync(LICENSE_FILE, 'utf8').trim();
            const machineId = await this.getMachineId();

            // Format: YYYY-MM-DD.SIGNATURE
            const parts = licenseContent.split('.');

            // Handle Legacy/Lifetime (No dot)
            if (parts.length === 1) {
                const expectedKey = crypto.createHmac('sha256', SECRET_SALT)
                    .update(machineId)
                    .digest('hex');
                if (licenseContent === expectedKey) return { valid: true, type: 'lifetime' };
                return { valid: false, reason: 'Invalid license signature' };
            }

            // Handle Term-Based
            if (parts.length === 2) {
                const [expiryDate, signature] = parts;

                // 1. Check Signature Integrity
                const expectedSignature = crypto.createHmac('sha256', SECRET_SALT)
                    .update(machineId + expiryDate)
                    .digest('hex');

                if (signature !== expectedSignature) {
                    return { valid: false, reason: 'Invalid license signature' };
                }

                // 2. Check Expiration
                if (expiryDate !== 'LIFETIME') {
                    const today = new Date().toISOString().split('T')[0];
                    if (today > expiryDate) {
                        return { valid: false, reason: 'License EXPIRED on ' + expiryDate };
                    }
                }

                return { valid: true, type: expiryDate === 'LIFETIME' ? 'lifetime' : 'term', expiry: expiryDate };
            }

            return { valid: false, reason: 'Invalid license format' };

        } catch (error) {
            console.error('License check error:', error);
            return { valid: false, reason: 'Error checking license' };
        }
    }

    /**
     * Activate license (save key to file)
     */
    static async activateLicense(inputKey) {
        try {
            const machineId = await this.getMachineId();
            const parts = inputKey.trim().split('.');
            let isValid = false;

            // Validate before saving
            if (parts.length === 1) {
                // Legacy verify
                const expected = crypto.createHmac('sha256', SECRET_SALT).update(machineId).digest('hex');
                if (inputKey.trim() === expected) isValid = true;
            } else if (parts.length === 2) {
                // Term verify
                const [date, sig] = parts;
                const expected = crypto.createHmac('sha256', SECRET_SALT).update(machineId + date).digest('hex');
                if (sig === expected) isValid = true;
            }

            if (isValid) {
                fs.writeFileSync(LICENSE_FILE, inputKey.trim());
                return { success: true };
            } else {
                return { success: false, message: 'Invalid License Key for this Machine' };
            }
        } catch (error) {
            return { success: false, message: error.message };
        }
    }

    /**
     * Generate License Key for a specific Machine ID
     * @param {string} targetMachineId 
     * @param {string} duration '1_month', '1_year', 'lifetime'
     */
    static generateLicense(targetMachineId, duration) {
        try {
            if (!targetMachineId) throw new Error('Machine ID is required');

            let expiryDate = '';
            const today = new Date();

            if (duration === 'lifetime') {
                expiryDate = 'LIFETIME';
            } else if (duration === '1_year') {
                today.setFullYear(today.getFullYear() + 1);
                expiryDate = today.toISOString().split('T')[0];
            } else if (duration === '6_months') {
                today.setMonth(today.getMonth() + 6);
                expiryDate = today.toISOString().split('T')[0];
            } else if (duration === '3_months') {
                today.setMonth(today.getMonth() + 3);
                expiryDate = today.toISOString().split('T')[0];
            } else if (duration === '1_month') {
                today.setMonth(today.getMonth() + 1);
                expiryDate = today.toISOString().split('T')[0];
            } else {
                // Custom date format: YYYY-MM-DD
                if (duration.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    expiryDate = duration;
                } else {
                    throw new Error('Invalid duration');
                }
            }

            // Sign: HMAC(MachineID + Expiry)
            // Note: If lifetime, we can use the same format: LIFETIME.SIGNATURE
            // Or use the machineId+expiry logic.

            // Consistent with verifyLicense logic:
            // update(machineId + expiryDate)

            const signature = crypto.createHmac('sha256', SECRET_SALT)
                .update(targetMachineId + expiryDate)
                .digest('hex');

            return `${expiryDate}.${signature}`;

        } catch (error) {
            console.error('Key Generation failed:', error);
            throw error;
        }
    }
}

module.exports = LicenseService;
