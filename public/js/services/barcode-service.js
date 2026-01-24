
class BarcodeService {
    constructor() {
        this.buffer = '';
        this.lastKeyTime = 0;
        this.threshold = 50; // ms between keys (scanners are fast)
        this.listener = null;
        this.isEnabled = false;

        // Configuration
        this.ignoredTags = ['INPUT', 'TEXTAREA', 'SELECT'];
    }

    init() {
        if (this.listener) return;

        this.listener = (e) => this.handleKey(e);
        document.addEventListener('keydown', this.listener);
        this.isEnabled = true;
        console.log('BarcodeService: Initialized');
    }

    destroy() {
        if (this.listener) {
            document.removeEventListener('keydown', this.listener);
            this.listener = null;
        }
        this.isEnabled = false;
    }

    handleKey(e) {
        if (!this.isEnabled) return;

        // Ignore if user is typing in an input field (unless we force override later)
        // But for global scanner, usually we want it to work even if focus is elsewhere,
        // OR properly handle the target. 
        // Strategy: If focus is on a text input, we might want to let the input handle it naturally.
        // BUT, for POS, we want "Scan anywhere" to add to cart.
        // Let's allow excluding specific elements if needed.

        /* 
           NOTE: Most USB Scanners act as keyboards. 
           If an input is focused, the scanner types into it.
           If we intercept blindly, we might break manual typing.
           
           Heuristic:
           - Measure time between keystrokes.
           - If very fast (<50ms), it's likely a scanner.
        */

        const currentTime = new Date().getTime();
        const gap = currentTime - this.lastKeyTime;
        this.lastKeyTime = currentTime;

        // If gap is too large, reset buffer (it was manual typing or a pause)
        if (gap > this.threshold) {
            // Exception: The very first character of a scan has a large gap from previous activity
            if (this.buffer.length > 0) {
                // Previous buffer was probably manual typing or incomplete scan
                this.buffer = '';
            }
        }

        if (e.key === 'Enter') {
            // End of scan?
            if (this.buffer.length >= 3) { // Minimum length for a barcode
                // It's a scan!
                e.preventDefault(); // Prevent submitting forms or newlines
                this.emitScan(this.buffer);
            }
            this.buffer = '';
            return;
        }

        // Ignore control keys, only printable characters
        if (e.key.length === 1) {
            this.buffer += e.key;
        }
    }

    emitScan(code) {
        console.log('Barcode Scanned:', code);
        const event = new CustomEvent('scan', { detail: { code: code } });
        document.dispatchEvent(event);
    }
}

// Singleton
window.BarcodeService = new BarcodeService();
