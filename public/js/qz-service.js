const QzService = {
    isConnected: false,
    printers: [],

    // Connect to QZ Tray
    async connect() {
        if (this.isConnected) return true;

        try {
            // Check if qz object exists (library loaded)
            if (typeof qz === 'undefined' || !qz) {
                // Try to see if it's on window
                if (window.qz) {
                    // valid
                } else {
                    throw new Error('QZ Tray library not loaded! Check internet connection or CDN.');
                }
            }

            // QZ Security Config
            qz.security.setCertificatePromise(function (resolve, reject) {
                // Return null to tell QZ we don't have a certificate (Dialog mode)
                resolve(null);
            });

            qz.security.setSignaturePromise(function (toSign) {
                return function (resolve, reject) {
                    // Return empty signature (Dialog mode)
                    resolve();
                };
            });

            // Connection options
            const options = {
                retries: 3,
                delay: 1
            };

            if (!qz.websocket.isActive()) {
                await qz.websocket.connect(options);
            }

            this.isConnected = true;
            return true;
        } catch (error) {
            console.error('QZ Connection Error:', error);
            this.isConnected = false;

            // Format error for display
            let errorMessage = error;
            if (typeof error === 'object') {
                errorMessage = error.message || (error.target ? "Connection Refused (Check QZ Tray App)" : "Unknown Error");
            }
            throw new Error(errorMessage.toString());
        }
    },

    // Disconnect
    async disconnect() {
        if (qz.websocket.isActive()) {
            await qz.websocket.disconnect();
        }
        this.isConnected = false;
    },

    // Check connection status
    isActive() {
        return this.isConnected && qz && qz.websocket && qz.websocket.isActive();
    },

    // Get list of printers
    async getPrinters() {
        if (!this.isConnected) await this.connect();
        this.printers = await qz.printers.find();
        return this.printers;
    },

    // Print HTML content (Pixel printing) -> Best for simple formatting like tables
    // options: { printerName: 'Epson...', html: '<h1>...</h1>' }
    async printHTML(printerName, htmlCanvas) {
        if (!this.isConnected) await this.connect();

        const config = qz.configs.create(printerName, {
            encoding: 'ISO-8859-1' // standard encoding
        });

        const data = [
            {
                type: 'pixel',
                format: 'html',
                flavor: 'plain', // or 'file' if passing url
                data: htmlCanvas
            }
        ];

        return qz.print(config, data);
    },

    // Print Raw Commands (ESC/POS) -> Best for speed on Dot Matrix / Thermal
    async printRaw(printerName, commands) {
        if (!this.isConnected) await this.connect();

        const config = qz.configs.create(printerName);
        return qz.print(config, commands);
    }
};

// Make it globally available
window.QzService = QzService;
