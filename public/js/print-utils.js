/**
 * SAGA TOKO - Print Utility
 * 2 separate templates: Thermal vs Dot Matrix
 * Auto-selects based on printerType setting
 */

const SagaPrint = {

  // Get saved print settings from localStorage
  getSettings() {
    const defaults = {
      // Printer & Method
      printerType: 'dotmatrix', // 'thermal' or 'dotmatrix'
      paperSize: 'portrait',     // thermal: '58' or '80', dotmatrix: 'portrait'
      method: 'browser',         // 'browser' or 'qz'
      autoPrint: false,
      showPreview: true,
      copies: 1,

      // Store Info
      storeName: 'SAGA TOKO',
      storeAddress: 'Jl. Contoh No. 123, Kota',
      storePhone: '021-12345678',
      showLogo: false,
      logoUrl: '',
      footerMessage: '',

      // Layout Settings
      fontSize: 12,           // Base font size (px)
      fontFamily: 'Arial, sans-serif',

      // Paper Size (for dot matrix, in mm)
      paperWidth: 210,        // Default to A4/Letter width (was 120)
      paperHeight: 140,       // Half page = 140mm

      // Margins (mm)
      marginTop: 10,
      marginBottom: 10,
      marginLeft: 10,
      marginRight: 10,

      // Column Widths for Dot Matrix (percentages, must total 100)
      colWidthName: 40,
      colWidthQty: 10,
      colWidthUnit: 15,
      colWidthPrice: 15,
      colWidthTotal: 20,

      // Content Options
      showInvoiceNumber: true,
      showDateTime: true,
      showCashier: true,
      showCustomer: true,
      showPaymentMethod: true,
      showChange: true,
      showDiscount: true,
      showBarcode: false
    };

    try {
      const saved = localStorage.getItem('saga_print_settings');
      if (saved) {
        return { ...defaults, ...JSON.parse(saved) };
      }
    } catch (e) {
      console.error('Error loading print settings:', e);
    }
    return defaults;
  },

  // Get logo URL (prioritize tenant logo, then settings)
  getLogoUrl() {
    const settings = this.getSettings();
    if (!settings.showLogo) return null;

    // First try tenant logo (actual uploaded logo)
    try {
      const tenant = JSON.parse(localStorage.getItem('saga_tenant') || '{}');
      if (tenant.logo_url) {
        return tenant.logo_url.startsWith('http') ? tenant.logo_url : 'http://localhost:3000' + tenant.logo_url;
      }
    } catch (e) { }

    // Fallback to settings logoUrl (if not placeholder)
    if (settings.logoUrl && !settings.logoUrl.includes('placeholder')) {
      return settings.logoUrl;
    }

    return null;
  },

  // Format currency
  formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    }).format(amount || 0);
  },

  // Format weight (Gr/Kg)
  formatWeight(grams) {
    if (!grams) return '0 Gr';
    if (grams >= 1000) return (grams / 1000).toLocaleString('id-ID', { minimumFractionDigits: 1, maximumFractionDigits: 2 }) + ' Kg';
    return grams.toLocaleString('id-ID') + ' Gr';
  },

  // ========================================
  // DOT MATRIX PAPER SIZE CONFIGURATION
  // ========================================
  // 
  // Paper: 12cm x 14cm Continuous Form
  // - Physical Width:  120mm (between tractor holes)
  // - Physical Height: 140mm (perforation interval)
  // - Printable Area:  100mm x 120mm (with margins)
  //
  getDotMatrixPageSize(paperSize) {
    switch (paperSize) {
      case '9.5x5.5': return '241mm 140mm';
      case '9.5x11': return '241mm 279mm';
      case '12x14cm': return '120mm 140mm';  // Portrait: 12cm wide x 14cm tall
      default: return '120mm 140mm';
    }
  },

  getDotMatrixContentWidth(paperSize) {
    switch (paperSize) {
      case '9.5x5.5': return '200mm';
      case '9.5x11': return '200mm';
      case '12x14cm': return '100mm';  // 120mm - 20mm (10mm margin each side)
      default: return '100mm';
    }
  },

  getDotMatrixContentHeight(paperSize) {
    switch (paperSize) {
      case '9.5x5.5': return '120mm';
      case '9.5x11': return '260mm';
      case '12x14cm': return '120mm';  // 140mm - 20mm (10mm margin top/bottom)
      default: return '120mm';
    }
  },

  // Build items HTML for THERMAL (V14: 2-Line Layout based on Image)
  // Line 1: **Item Name**
  // Line 2: Qty Unit x Price ..... Total
  buildItemsHTMLThermal(items) {
    let html = '';
    if (items && items.length > 0) {
      items.forEach(item => {
        const price = this.formatCurrency(item.price || item.unit_price || item.sell_price || 0);
        const subtotal = this.formatCurrency(item.subtotal || (item.quantity * (item.price || item.unit_price || item.sell_price || 0)));
        const unitName = item.unit_name || item.unit || 'Pcs';

        html += `
        <div class="item-block" style="margin-bottom: 5px;">
            <div class="item-name" style="font-weight:bold;">${item.name || item.product_name}</div>
            <div class="item-details" style="display:flex; justify-content:space-between;">
                <span>${item.quantity} ${unitName} x ${price}</span>
                <span style="font-weight:bold;">Rp.${subtotal}</span>
            </div>
        </div>`;
      });
    }
    return html;
  },

  // Build items table HTML for DOT MATRIX (5 columns: Item, Qty, Unit, Harga, Subtotal)
  buildItemsHTMLDotMatrix(items) {
    let html = '';
    if (items && items.length > 0) {
      items.forEach(item => {
        const price = this.formatCurrency(item.price || item.unit_price || item.sell_price || 0);
        const subtotal = this.formatCurrency(item.subtotal || (item.quantity * (item.price || item.unit_price || item.sell_price || 0)));
        const unitName = item.unit_name || item.unit || 'Pcs';
        html += `<tr>
          <td class='col-name'>${item.name || item.product_name}</td>
          <td class='col-qty'>${item.quantity}</td>
          <td class='col-unit'>${unitName}</td>
          <td class='col-price'>${price}</td>
          <td class='col-total'>${subtotal}</td>
        </tr>`;
      });
    }
    return html;
  },

  // Legacy function - kept for backward compatibility
  buildItemsHTML(items) {
    return this.buildItemsHTMLDotMatrix(items);
  },

  // ============================================
  // TEMPLATE 1: THERMAL (58mm / 80mm)
  // V14 LAYOUT UPDATE (Reference Image Match)
  // - Centered Header (Logo optional)
  // - Address/Phone/Cashier centered/listed
  // - Item: Name on top, Qty x Price on bottom left, Total on bottom right
  // ============================================
  generateThermalReceipt(transaction) {
    const settings = this.getSettings();
    const logoUrl = this.getLogoUrl();
    const dateStr = transaction.date ? new Date(transaction.date).toLocaleDateString('id-ID') : new Date().toLocaleDateString('id-ID');
    const timeStr = transaction.time || new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const itemsHTML = this.buildItemsHTMLThermal(transaction.items);

    // Width Mapping
    const width = settings.paperSize === '58' ? '48mm' : '72mm';
    // Font Sizing (V13 clarity retained, but V14 layout applied)
    const baseFontSize = settings.paperSize === '58' ? '10px' : '11px';
    const headerFontSize = settings.paperSize === '58' ? '12px' : '14px';

    // Logo HTML
    let logoHtml = '';
    if (logoUrl) {
      logoHtml = `<div style="text-align:center;margin-bottom:5px;"><img src="${logoUrl}" alt="Logo" style="max-height:50px;max-width:60px;filter:grayscale(100%) contrast(150%);"></div>`;
    }

    const autoPrintScript = settings.autoPrint ? `<script>window.onload = function() { doPrint(true); };</script>` : '';

    return `<!DOCTYPE html>
<html>
<head>
  <title>Receipt</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { 
      width: ${width}; 
      font-family: Arial, Helvetica, sans-serif; 
      font-size: ${baseFontSize};
      padding: 5px;
      color: #000;
      line-height: 1.3;
    }
    
    .dashed-line { border-bottom: 2px dashed #000; margin: 5px 0; width: 100%; }
    
    .header { text-align: center; margin-bottom: 10px; }
    .header h1 { font-size: ${headerFontSize}; font-weight: 800; margin: 0; text-transform: uppercase; margin-bottom: 2px; }
    .header-info { text-align: center; font-size: ${baseFontSize}; }
    .header-info div { margin-bottom: 1px; }

    /* Column Headers: NAMA BARANG ..... JUMLAH */
    .col-header { display: flex; justify-content: space-between; font-weight: bold; text-transform: uppercase; font-size: 9px; margin-bottom: 2px; }
    
    /* Items Container */
    .items-container { width: 100%; }

    /* Totals Section */
    .totals-row { display: flex; justify-content: space-between; margin-bottom: 2px; }
    .grand-total { font-weight: 800; font-size: ${headerFontSize}; display: flex; justify-content: space-between; margin-top: 2px;}

    .footer { text-align: center; margin-top: 15px; margin-bottom: 20px; font-weight: bold; font-size: 10px; }
    
    @media print { 
      html, body { margin: 0; padding: 0; height: auto !important; }
      @page { size: ${width} auto portrait; margin: 0; }
    }
  </style>
  <script>
    function doPrint(isAuto) {
        var sentToQz = false;
        try {
            if (window.opener && window.opener.QzService) {
                var s = window.opener.localStorage.getItem('saga_print_settings');
                if (s) {
                    var p = JSON.parse(s);
                    if (p.method === 'qz' && p.thermalPrinterName) {
                         if (window.opener.QzService.isActive && window.opener.QzService.isActive()) {
                            window.opener.QzService.printHTML(p.thermalPrinterName, document.documentElement.outerHTML);
                            return; 
                         }
                    }
                }
            }
        } catch(e) {}
        
        if (isAuto && sentToQz) return;
        
        if (!isAuto || (isAuto && !sentToQz)) {
            setTimeout(function() { 
                window.focus(); 
                window.print(); 
            }, 100);
        }
    }
  </script>
</head>
<body>
  <div class="header">
    ${logoHtml}
    <h1>${settings.storeName}</h1>
    <div class="header-info">
        <div>Alamat: ${settings.storeAddress}</div>
        <div>No. Telp: ${settings.storePhone}</div>
        <div>Admin / Kasir: ${transaction.cashier || 'Admin'}</div>
        <div>Pelanggan: ${transaction.customer_name || 'Umum'}</div>
        <div>Tanggal: ${dateStr} - ${timeStr}</div>
    </div>
  </div>
  
  <div class="dashed-line"></div>
  
  <div class="col-header">
    <span>NAMA BARANG</span>
    <span>JUMLAH</span>
  </div>
  
  <div class="dashed-line"></div>
  
  <div class="items-container">
    ${itemsHTML}
  </div>
  
  <div class="dashed-line"></div>
  
  <div class="totals">
    <div class="totals-row">
        <span>Sub Total</span>
        <span>Rp.${this.formatCurrency(transaction.subtotal || transaction.total_amount)}</span>
    </div>
    ${settings.showDiscount && transaction.discount > 0 ? `
    <div class="totals-row">
        <span>Potongan / Discount</span>
        <span>- Rp.${this.formatCurrency(transaction.discount)}</span>
    </div>` : ''}

    <div class="totals-row">
        <span>Total Berat</span>
        <span>${this.formatWeight(transaction.totalWeight || 0)}</span>
    </div>
    
    <div class="dashed-line"></div>
    
    <div class="grand-total">
        <span>TOTAL</span>
        <span>Rp.${this.formatCurrency(transaction.total || transaction.total_amount)}</span>
    </div>
  </div>
  
  <div class="footer">
     <div>${settings.footerMessage}</div>
     <div style="display:flex;justify-content:space-between;margin-top:20px;">
        <div style="text-align:center;">
          <div style="height:25px;"></div>
          <div>Penerima</div>
        </div>
        <div style="text-align:center;">
          <div style="height:25px;"></div>
          <div>Hormat Kami,</div>
        </div>
     </div>
  </div>
  ${autoPrintScript}
</body>
</html>`;
  },

  // ============================================
  // TEMPLATE 2: DOT MATRIX (Portrait / Landscape)
  // FIXED ALIGNMENT V11 (Retained)
  // ============================================
  generateDotMatrixReceipt(transaction) {
    console.log('[PRINT] Customer name received:', transaction.customer_name);
    const settings = this.getSettings();
    const logoUrl = this.getLogoUrl(); // Will be null if showLogo is false
    const dateStr = transaction.date ? new Date(transaction.date).toLocaleDateString('id-ID') : new Date().toLocaleDateString('id-ID');
    const timeStr = transaction.time || new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const itemsHTML = this.buildItemsHTMLDotMatrix(transaction.items);

    // Auto-exec script (if enabled)
    const autoPrintScript = settings.autoPrint ? `<script>window.onload = function() { doPrint(true); };</script>` : '';

    return `<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Faktur Dot Matrix</title>
    <style>
        /* --- RESET & GLOBAL (DYNAMIC FROM SETTINGS) --- */
        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: ${settings.fontFamily || "'Courier New', Courier, monospace"};
            font-size: ${settings.fontSize || 12}px;
            background-color: white;
        }

        /* ============================================
           KERTAS CONTINUOUS FORM 12cm x 14cm
           - Size: 120mm (W) x 140mm (H)
           - Margin: 10mm semua sisi
           - Printable Area: 100mm x 120mm
           ============================================ */
        @page {
            size: ${settings.paperWidth || 120}mm ${settings.paperHeight || 140}mm;
            margin: ${settings.marginTop || 10}mm ${settings.marginRight || 10}mm ${settings.marginBottom || 10}mm ${settings.marginLeft || 10}mm; 
        }

        /* Invoice mengisi area printable (setelah @page margin) */
        .invoice-box {
            width: 100%;
            background: white;
            padding: 0;
        }

        /* --- HEADER --- */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 5px;
        }

        .header-left { display: flex; gap: 10px; align-items: flex-start; }
        
        .logo-box {
            width: 60px;
            height: 50px;
            border: 2px solid black;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .company-info { text-align: left; }
        .company-info h2 {
            margin: 0 0 5px 0;
            font-size: 16px;
            font-style: italic;
            text-transform: uppercase;
        }

        .header-right table { text-align: left; }
        .header-right td { padding-right: 5px; } 

        /* --- GARIS PEMISAH --- */
        .double-dashed {
            border-top: 1px dashed black;
            border-bottom: 1px dashed black;
            height: 3px;
            margin: 5px 0;
            width: 100%;
        }
        
        .single-dashed {
            border-bottom: 1px dashed black;
            margin: 5px 0;
            width: 100%;
        }

        /* --- TABEL ITEM --- */
        .item-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed; 
        }

        .item-table th {
            text-align: left;
            padding: 5px 0;
            border-bottom: 1px dashed black;
        }

        .item-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        /* --- PENGATURAN LEBAR KOLOM & ALIGNMENT (5 COLUMNS - DYNAMIC) --- */
        .item-table .col-name, .summary-row .col-name   { width: ${settings.colWidthName || 40}%; text-align: left; overflow: hidden; }
        .item-table .col-qty, .summary-row .col-qty     { width: ${settings.colWidthQty || 10}%; text-align: center; } 
        .item-table .col-unit, .summary-row .col-unit   { width: ${settings.colWidthUnit || 15}%; text-align: center; }
        .item-table .col-price, .summary-row .col-price { width: ${settings.colWidthPrice || 15}%; text-align: right; }
        .item-table .col-total, .summary-row .col-total { width: ${settings.colWidthTotal || 20}%; text-align: right; }

        /* --- FOOTER / TOTALS --- */
        .summary-row {
            display: flex;
            width: 100%;
            padding: 2px 0;
            align-items: center;
        }

        .footer-bottom {
            margin-top: auto; 
        }

        .message-box {
            text-align: center;
            margin: 10px 0;
            font-style: italic;
        }

        /* --- TANDA TANGAN --- */
        .signatures {
            display: flex;
            justify-content: space-between;
            padding-bottom: 10px;
        }
        .sig-block { width: 200px; text-align: center; }
        .sig-space { height: 25px; }

        /* --- NO PRINT (BUTTONS) --- */
        .print-control {
            text-align: center; 
            padding: 10px;
            background: #f0f0f0;
            border-bottom: 1px solid #ccc;
            margin-bottom: 10px;
        }

        @media print {
            html, body {
                width: 100%;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
            }
            .invoice-box { 
                width: 100%; 
                margin: 0; 
                padding: 0; 
                border: none;
                box-shadow: none; 
            }
            .no-print { display: none !important; }
            .print-control { display: none !important; }
        }
    </style>
    <script>
        // SMART PRINT: Tries QZ Tray (Silent) -> Fallback to Browser (Popup)
        function doPrint(isAuto) {
            
            var btn = document.querySelector('.print-control');
            // Hide button TEMPORARILY before doing anything
            if(btn) btn.style.display = 'none';

            // 1. Try finding QZ Service
            try {
                if (window.opener && window.opener.QzService) {
                    var settings = window.opener.localStorage.getItem('saga_print_settings');
                    if (settings) {
                        var parsed = JSON.parse(settings);
                        if (parsed.method === 'qz' && parsed.dotMatrixPrinterName) {
                            if (window.opener.QzService.isActive && window.opener.QzService.isActive()) {
                                
                                // Capture HTML *WHILE BUTTON IS HIDDEN*
                                var content = document.documentElement.outerHTML;
                                
                                window.opener.QzService.printHTML(parsed.dotMatrixPrinterName, content)
                                    .then(function() { 
                                        if (!isAuto) alert('✅ Print Berhasil Dikirim ke QZ Tray!'); 
                                        restoreBtn();
                                    })
                                    .catch(function(err) { 
                                        if (!isAuto) {
                                            alert('⚠️ QZ Tray Error: ' + err + '. Menggunakan Browser Print.');
                                            fallbackPrint(); 
                                        } else {
                                           restoreBtn();
                                        }
                                    });
                                return; 
                            }
                        }
                    }
                }
            } catch(e) { console.log('QZ Bridge skipped', e); }

            // 2. Logic:
            if (isAuto) {
                // Restore button immediately if auto failed (so user can see it)
                restoreBtn();
                return;
            }

            // If MANUAL CLICK: 
            fallbackPrint();
        }

        function fallbackPrint() {
            setTimeout(function() {
                window.focus();
                window.print();
                restoreBtn();
            }, 100);
        }

        function restoreBtn() {
            setTimeout(function() {
                var btn = document.querySelector('.print-control');
                if(btn) btn.style.display = 'block';
            }, 2000);
        }
    </script>
</head>
<body>

    <!-- CONTROL BAR -->
    <div class="print-control no-print">
        <button onclick="doPrint(false)" style="font-weight:bold; padding: 8px 20px; font-size: 14px; cursor: pointer; background: #007bff; color: white; border: none; border-radius: 4px;">🖨️ CETAK FAKTUR</button>
        <div style="font-size:11px; color:#555; margin-top:5px;">Paper Setting: Custom (9.5 x 5.5 inch)</div>
    </div>

    <div class="invoice-box">
        <div class="header">
            <div class="header-left">
                ${logoUrl ? `<div class="logo-box" style="border:none;"><img src="${logoUrl}" style="max-width:100%; max-height:100%;"></div>` : ''}
                <div class="company-info">
                    <h2>FAKTUR PENJUALAN</h2>
                    <div>Kepada Yth :</div>
                    <div>${transaction.customer_name || 'Walk-in Customer'}</div>
                </div>
            </div>
            <div class="header-right">
                <table>
                    <tr><td>No</td><td>: ${transaction.code || transaction.invoice_number || '-'}</td></tr>
                    <tr><td>Tanggal</td><td>: ${dateStr}</td></tr>
                    <tr><td>Kasir</td><td>: ${transaction.cashier || 'Admin'}</td></tr>
                </table>
            </div>
        </div>

        <div class="double-dashed" style="border-style: double; border-width: 3px 0 0 0;"></div>

        <table class="item-table">
            <thead>
                <tr>
                    <th class="col-name">Nama Barang</th>
                    <th class="col-qty">Qty</th>
                    <th class="col-unit">Unit</th>
                    <th class="col-price">Harga</th>
                    <th class="col-total">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                ${itemsHTML}
            </tbody>
        </table>

         <div class="footer-bottom">
            
            <div class="single-dashed"></div>

            <div class="summary-row">
                <div class="col-name">Sub Total</div>
                <div class="col-qty"></div>
                <div class="col-unit"></div>
                <div class="col-price"></div>
                <div class="col-total">Rp. ${this.formatCurrency(transaction.subtotal || transaction.total_amount)}</div>
            </div>

            <div class="single-dashed"></div>

            <!-- Discount Row -->
            ${settings.showDiscount && transaction.discount > 0 ? `
            <div class="summary-row">
                <div class="col-name">Potongan / Discount</div>
                <div class="col-qty"></div>
                <div class="col-unit"></div>
                <div class="col-price"></div>
                <div class="col-total">- Rp. ${this.formatCurrency(transaction.discount)}</div>
            </div>
            <div class="single-dashed"></div>
            ` : ''}

            <div class="summary-row">
                <div class="col-name">Total Berat</div>
                <div class="col-qty"></div>
                <div class="col-unit"></div>
                <div class="col-price"></div>
                <div class="col-total">${this.formatWeight(transaction.totalWeight || 0)}</div>
            </div>
            <div class="single-dashed"></div>

            <div class="summary-row" style="font-weight: bold;">
                <div class="col-name">Total</div>
                <div class="col-qty"></div>
                <div class="col-unit"></div>
                <div class="col-price"></div>
                <div class="col-total">Rp. ${this.formatCurrency(transaction.total || transaction.total_amount)}</div>
            </div>

            <div class="signatures">
                <div class="sig-block">
                    <div>Penerima</div>
                    <div class="sig-space"></div>
                </div>
                <div class="sig-block">
                    <div>Hormat kami,</div>
                    <div class="sig-space"></div>
                </div>
            </div>

            <div class="message-box">
                ${settings.footerMessage}
            </div>
        </div>
    </div>
    ${autoPrintScript}
</body>
</html>`;
  },

  // ============================================
  // AUTO-SELECT TEMPLATE BASED ON SETTINGS
  // ============================================
  generateReceiptHTML(transaction) {
    const settings = this.getSettings();
    if (settings.printerType === 'thermal') {
      return this.generateThermalReceipt(transaction);
    } else {
      return this.generateDotMatrixReceipt(transaction);
    }
  },

  // Print directly (opens print window and triggers print)
  print(contentHTML, showPreview = null) {
    const settings = this.getSettings();

    // Check if running in Electron
    const isElectron = typeof window !== 'undefined' && window.process && window.process.type;

    if (isElectron && window.require) {
      try {
        const { ipcRenderer } = window.require('electron');
        console.log('[SagaPrint] Electron detected, using native print');

        let printerName = '';
        if (settings.printerType === 'thermal') {
          printerName = settings.thermalPrinterName;
        } else {
          printerName = settings.dotMatrixPrinterName;
        }

        ipcRenderer.invoke('print-job', {
          content: contentHTML,
          printerName: printerName,
          silent: settings.autoPrint
        }).then(() => {
          console.log('[SagaPrint] Native print job sent');
          // Optional: Show success notification
        }).catch(err => {
          console.error('[SagaPrint] Native print failed:', err);
          alert('Print Failed: ' + err.message);
        });
        return;
      } catch (e) {
        console.error('[SagaPrint] Electron IPC error:', e);
      }
    }

    // Try QZ Tray first (if connected)
    if (window.qz && window.qz.websocket && window.qz.websocket.isActive()) {
      console.log('[SagaPrint] Using QZ Tray for printing');
      // QZ Tray HTML print
      const config = qz.configs.create(null); // Use default printer
      qz.print(config, [{
        type: 'html',
        format: 'plain',
        data: contentHTML
      }]).then(() => {
        console.log('[SagaPrint] QZ Tray print successful');
      }).catch(err => {
        console.error('[SagaPrint] QZ Tray print failed:', err);
        this.fallbackPrint(contentHTML);
      });
      return;
    }

    // Fallback to window popup + print
    this.fallbackPrint(contentHTML);
  },

  // Fallback print using popup window
  fallbackPrint(contentHTML) {
    const printWindow = window.open('', '_blank', 'width=400,height=600');
    if (!printWindow) {
      console.error('[SagaPrint] Could not open print window');
      return;
    }
    printWindow.document.write(contentHTML);
    printWindow.document.close();

    // Wait for content to load, then trigger print
    printWindow.onload = function () {
      setTimeout(() => {
        printWindow.focus();
        printWindow.print();
        // Close after print dialog (optional - user may want to see it)
        // printWindow.close();
      }, 300);
    };

    // For cases where onload doesn't fire (like in Electron)
    setTimeout(() => {
      printWindow.focus();
      printWindow.print();
    }, 500);
  },

  // Print a transaction/receipt
  printReceipt(transaction, showPreview = null) {
    const contentHTML = this.generateReceiptHTML(transaction);
    this.print(contentHTML, showPreview);
  },

  // ============================================
  // PREVIEW CONTENT (for display in modal)
  // Returns simple HTML for the preview DIV
  // ============================================
  generatePreviewContent(transaction) {
    const settings = this.getSettings();
    const logoUrl = this.getLogoUrl();
    const dateStr = transaction.date ? new Date(transaction.date).toLocaleDateString('id-ID') : new Date().toLocaleDateString('id-ID');
    const timeStr = transaction.time || new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const isThermal = settings.printerType === 'thermal';
    const itemsHTML = isThermal ? this.buildItemsHTMLThermal(transaction.items) : this.buildItemsHTMLDotMatrix(transaction.items);

    // Logo HTML
    let logoHtml = '';
    if (logoUrl) {
      logoHtml = `<div style="text-align:center;margin-bottom:5px;"><img src="${logoUrl}" alt="Logo" style="max-height:50px;max-width:100px;"></div>`;
    }

    return `
      <div style="font-family:'Courier New',monospace;font-size:10pt;color:#000;padding:10px;">
        ${logoHtml}
        <div style="text-align:center;font-weight:bold;margin-bottom:10px;">
          ${settings.storeName}<br>
          <span style="font-weight:normal;font-size:9pt;">${settings.storeAddress}</span>
        </div>
        
        <div style="margin-bottom:10px;border-bottom:1px dashed #000;padding-bottom:5px;">
          <div>No: ${transaction.code || transaction.invoice_number || '-'}</div>
          <div>Tgl: ${dateStr} ${timeStr}</div>
          <div>Kasir: ${transaction.cashier || 'Admin'}</div>
          <div>Plg: ${transaction.customer_name || 'Umum'}</div>
        </div>

        <table style="width:100%;font-size:9pt;margin-bottom:10px;">
          <thead>
            <tr style="border-bottom:1px dashed #000;">
              <th style="text-align:left;">Item</th>
              <th style="text-align:center;">Qty</th>
              <th style="text-align:right;">Total</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHTML}
          </tbody>
        </table>

         <div style="border-top:1px dashed #000;padding-top:5px;">
           <div style="display:flex;justify-content:space-between;">
             <span>Subtotal:</span>
             <span>Rp ${this.formatCurrency(transaction.subtotal || transaction.total_amount)}</span>
           </div>
           ${transaction.discount > 0 ? `
           <div style="display:flex;justify-content:space-between;">
             <span>Disc:</span>
             <span>- Rp ${this.formatCurrency(transaction.discount)}</span>
           </div>` : ''}
           <div style="display:flex;justify-content:space-between;font-weight:bold;margin-top:5px;">
             <span>TOTAL:</span>
             <span>Rp ${this.formatCurrency(transaction.total || transaction.total_amount)}</span>
           </div>
         </div>
      </div>
    `;
  }
};

// Make available globally
window.SagaPrint = SagaPrint;
