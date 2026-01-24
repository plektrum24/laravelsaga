/**
 * SAGA POS - Barcode Scanner Service
 * Detects and handles barcode scanner input (USB keyboard emulation)
 * Also provides QR code scanning via camera for mobile
 */

class BarcodeService {
    constructor() {
        // Barcode buffer and timing
        this.buffer = '';
        this.lastKeyTime = 0;
        this.threshold = 50; // ms between keys (scanners are very fast)
        this.minLength = 3; // Minimum barcode length
        this.maxLength = 50; // Maximum barcode length
        
        // Event listener reference
        this.listener = null;
        this.isEnabled = false;

        // Tags to ignore for keyboard input
        this.ignoredTags = ['TEXTAREA', 'SELECT'];

        // Barcode prefixes to listen for (optional filtering)
        this.acceptedPrefixes = null; // null = all prefixes accepted

        // QR code scanner
        this.qrcodeScanner = null;
        this.isQrScanning = false;
    }

    /**
     * Initialize barcode scanner (keyboard)
     */
    init() {
        if (this.listener) return;

        this.listener = (e) => this.handleKey(e);
        document.addEventListener('keydown', this.listener);
        this.isEnabled = true;

        console.log('BarcodeService: Keyboard scanner initialized');
        this.dispatchEvent('scanner:init', { type: 'keyboard' });
    }

    /**
     * Destroy barcode scanner
     */
    destroy() {
        if (this.listener) {
            document.removeEventListener('keydown', this.listener);
            this.listener = null;
        }
        this.isEnabled = false;
        this.stopQrScanner();
        console.log('BarcodeService: Scanner destroyed');
    }

    /**
     * Handle keyboard event
     */
    handleKey(e) {
        if (!this.isEnabled) return;

        // Skip if inside input field (unless it's for barcode entry)
        const target = e.target;
        if (target.tagName === 'INPUT' && target.type === 'text' && !target.classList.contains('barcode-input')) {
            // Allow normal input behavior
            return;
        }

        // Skip if inside ignored tags
        if (this.ignoredTags.includes(target.tagName)) {
            return;
        }

        const currentTime = Date.now();
        const gap = currentTime - this.lastKeyTime;
        this.lastKeyTime = currentTime;

        // Reset buffer if gap is too large (manual typing, not scanner)
        if (gap > this.threshold && this.buffer.length > 0) {
            this.buffer = '';
        }

        // Handle Enter key (end of barcode)
        if (e.key === 'Enter') {
            if (this.buffer.length >= this.minLength) {
                e.preventDefault(); // Prevent form submission
                this.emitScan(this.buffer);
            }
            this.buffer = '';
            return;
        }

        // Collect printable characters
        if (e.key.length === 1) {
            if (this.buffer.length < this.maxLength) {
                this.buffer += e.key;
            }
        }
    }

    /**
     * Emit scan event with barcode
     */
    emitScan(code) {
        console.log('Barcode scanned:', code);

        // Check accepted prefixes if configured
        if (this.acceptedPrefixes && !this.acceptedPrefixes.some(p => code.startsWith(p))) {
            console.warn('Barcode prefix not accepted:', code);
            this.dispatchEvent('scan:invalid', { code });
            return;
        }

        // Dispatch custom event
        this.dispatchEvent('scan', { code });

        // Also trigger global event for backward compatibility
        const event = new CustomEvent('barcode:scan', {
            detail: { code, timestamp: Date.now() }
        });
        document.dispatchEvent(event);
    }

    /**
     * Dispatch custom event
     */
    dispatchEvent(eventName, detail = {}) {
        const event = new CustomEvent(eventName, { detail });
        document.dispatchEvent(event);
    }

    /**
     * Set barcode prefixes to accept
     */
    setAcceptedPrefixes(prefixes) {
        this.acceptedPrefixes = prefixes;
    }

    /**
     * Start QR code scanner (via camera)
     */
    async startQrScanner(elementId = 'qr-scanner') {
        if (this.isQrScanning) return;

        try {
            // Dynamically load html5-qrcode library if not already loaded
            if (typeof Html5Qrcode === 'undefined') {
                await this.loadScript('https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js');
            }

            const element = document.getElementById(elementId);
            if (!element) {
                throw new Error(`Element with ID '${elementId}' not found`);
            }

            // Create scanner instance
            this.qrcodeScanner = new Html5Qrcode(elementId);

            // Start scanning
            await this.qrcodeScanner.start(
                { facingMode: 'environment' }, // Use rear camera
                {
                    fps: 10,
                    qrbox: { width: 250, height: 250 }
                },
                (decodedText) => this.emitQrScan(decodedText),
                (error) => console.warn('QR scan error:', error)
            );

            this.isQrScanning = true;
            console.log('QR code scanner started');
            this.dispatchEvent('qr:start', {});
        } catch (error) {
            console.error('Failed to start QR scanner:', error);
            this.dispatchEvent('qr:error', { error: error.message });
        }
    }

    /**
     * Stop QR code scanner
     */
    async stopQrScanner() {
        if (!this.isQrScanning || !this.qrcodeScanner) return;

        try {
            await this.qrcodeScanner.stop();
            this.qrcodeScanner = null;
            this.isQrScanning = false;
            console.log('QR code scanner stopped');
            this.dispatchEvent('qr:stop', {});
        } catch (error) {
            console.error('Error stopping QR scanner:', error);
        }
    }

    /**
     * Emit QR scan event
     */
    emitQrScan(code) {
        console.log('QR code scanned:', code);
        this.dispatchEvent('qr:scan', { code });

        // Also trigger global event
        const event = new CustomEvent('qrcode:scan', {
            detail: { code, timestamp: Date.now() }
        });
        document.dispatchEvent(event);
    }

    /**
     * Load external script
     */
    loadScript(src) {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    /**
     * Check if QR scanner is available
     */
    isQrScannerAvailable() {
        return typeof Html5Qrcode !== 'undefined' || navigator.mediaDevices?.getUserMedia;
    }
}

// Create singleton instance
const barcodeService = new BarcodeService();

// Export as both default and named export
export default barcodeService;
export { BarcodeService };

// Also make available globally for use in inline scripts
if (typeof window !== 'undefined') {
    window.BarcodeService = barcodeService;
}
