const express = require('express');
const router = express.Router();
const ExcelJS = require('exceljs');
const PDFDocument = require('pdfkit');

/**
 * GET /api/export/products/excel
 * Export all products to Excel (25-column format matching import template)
 */
router.get('/products/excel', async (req, res) => {
    try {
        // Fetch products with units
        const [products] = await req.tenantDb.execute(`
            SELECT p.*, c.name as category_name, u.name as base_unit_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN units u ON p.base_unit_id = u.id
            WHERE p.is_active = true
            ORDER BY p.name
        `);

        // Fetch units for each product
        for (let product of products) {
            const [units] = await req.tenantDb.execute(`
                SELECT pu.*, u.name as unit_name
                FROM product_units pu
                JOIN units u ON pu.unit_id = u.id
                WHERE pu.product_id = ?
                ORDER BY pu.sort_order
            `, [product.id]);
            product.units = units;
        }

        // Create workbook
        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Products');

        // Define columns - 24 columns horizontal format matching import template
        sheet.columns = [
            { header: 'NAMAITEM', key: 'name', width: 30 },
            { header: 'KATEGORI', key: 'category', width: 15 },
            { header: 'SATUAN1', key: 'unit1', width: 10 },
            { header: 'SATUAN2', key: 'unit2', width: 10 },
            { header: 'SATUAN3', key: 'unit3', width: 10 },
            { header: 'SATUAN4', key: 'unit4', width: 10 },
            { header: 'SATUAN5', key: 'unit5', width: 10 },
            { header: 'KONVERSI1', key: 'conv1', width: 10 },
            { header: 'KONVERSI2', key: 'conv2', width: 10 },
            { header: 'KONVERSI3', key: 'conv3', width: 10 },
            { header: 'KONVERSI4', key: 'conv4', width: 10 },
            { header: 'KONVERSI5', key: 'conv5', width: 10 },
            { header: 'HARGABELI1', key: 'buy1', width: 12 },
            { header: 'HARGABELI2', key: 'buy2', width: 12 },
            { header: 'HARGABELI3', key: 'buy3', width: 12 },
            { header: 'HARGABELI4', key: 'buy4', width: 12 },
            { header: 'HARGABELI5', key: 'buy5', width: 12 },
            { header: 'HARGAJUAL1', key: 'sell1', width: 12 },
            { header: 'HARGAJUAL2', key: 'sell2', width: 12 },
            { header: 'HARGAJUAL3', key: 'sell3', width: 12 },
            { header: 'HARGAJUAL4', key: 'sell4', width: 12 },
            { header: 'HARGAJUAL5', key: 'sell5', width: 12 },
            { header: 'STOK', key: 'stock', width: 10 },
            { header: 'STOKMIN', key: 'min_stock', width: 10 },
            { header: 'EXPIRY', key: 'expiry', width: 12 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        // Add data rows - one row per product with all units horizontal
        for (const product of products) {
            // Get nearest expiry date for this product from purchase_items
            let nearestExpiry = '';
            try {
                const [expiryRows] = await req.tenantDb.execute(`
                    SELECT MIN(pi.expiry_date) as nearest_expiry
                    FROM purchase_items pi
                    WHERE pi.product_id = ? 
                    AND pi.expiry_date IS NOT NULL 
                    AND pi.current_stock > 0
                `, [product.id]);
                if (expiryRows[0]?.nearest_expiry) {
                    nearestExpiry = expiryRows[0].nearest_expiry.toISOString().split('T')[0];
                }
            } catch (e) { /* ignore if table doesn't exist */ }

            const row = {
                name: product.name,
                category: product.category_name || '',
                stock: product.stock,
                min_stock: product.min_stock,
                expiry: nearestExpiry
            };

            // Fill units data (up to 5 units)
            const units = product.units || [];
            for (let i = 0; i < 5; i++) {
                const unit = units[i];
                if (unit) {
                    row[`unit${i + 1}`] = unit.unit_name;
                    row[`conv${i + 1}`] = unit.conversion_qty;
                    row[`buy${i + 1}`] = unit.buy_price;
                    row[`sell${i + 1}`] = unit.sell_price;
                } else {
                    row[`unit${i + 1}`] = '';
                    row[`conv${i + 1}`] = '';
                    row[`buy${i + 1}`] = '';
                    row[`sell${i + 1}`] = '';
                }
            }

            sheet.addRow(row);
        }

        // Set response headers
        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=products_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export Excel error:', error);
        res.status(500).json({ success: false, message: 'Failed to export' });
    }
});

/**
 * GET /api/export/products/pdf
 * Export all products to PDF
 */
router.get('/products/pdf', async (req, res) => {
    try {
        const [products] = await req.tenantDb.execute(`
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = true
            ORDER BY p.name
        `);

        // Fetch base unit prices
        for (let product of products) {
            const [units] = await req.tenantDb.execute(`
                SELECT buy_price, sell_price FROM product_units
                WHERE product_id = ? AND is_base_unit = true
            `, [product.id]);
            if (units.length > 0) {
                product.buy_price = units[0].buy_price;
                product.sell_price = units[0].sell_price;
            }
        }

        const doc = new PDFDocument({ margin: 50, size: 'A4' });

        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', `attachment; filename=products_${Date.now()}.pdf`);

        doc.pipe(res);

        // Title
        doc.fontSize(20).font('Helvetica-Bold').text('Daftar Produk', { align: 'center' });
        doc.fontSize(10).font('Helvetica').text(`Tanggal: ${new Date().toLocaleDateString('id-ID')}`, { align: 'center' });
        doc.moveDown();

        // Table header
        const tableTop = 120;
        const colWidths = [80, 150, 80, 80, 80];
        const headers = ['SKU', 'Nama', 'Kategori', 'Harga Beli', 'Harga Jual'];

        doc.font('Helvetica-Bold').fontSize(10);
        let xPos = 50;
        headers.forEach((header, i) => {
            doc.text(header, xPos, tableTop, { width: colWidths[i], align: 'left' });
            xPos += colWidths[i];
        });

        // Draw line
        doc.moveTo(50, tableTop + 15).lineTo(545, tableTop + 15).stroke();

        // Table rows
        doc.font('Helvetica').fontSize(9);
        let yPos = tableTop + 25;

        const formatCurrency = (num) => {
            return new Intl.NumberFormat('id-ID').format(num || 0);
        };

        for (const product of products) {
            if (yPos > 750) {
                doc.addPage();
                yPos = 50;
            }

            xPos = 50;
            const rowData = [
                product.sku,
                product.name.substring(0, 25),
                (product.category_name || '-').substring(0, 12),
                formatCurrency(product.buy_price),
                formatCurrency(product.sell_price)
            ];

            rowData.forEach((text, i) => {
                doc.text(text, xPos, yPos, { width: colWidths[i], align: 'left' });
                xPos += colWidths[i];
            });

            yPos += 18;
        }

        // Footer
        doc.fontSize(8).text(`Total: ${products.length} produk`, 50, yPos + 10);

        doc.end();

    } catch (error) {
        console.error('Export PDF error:', error);
        res.status(500).json({ success: false, message: 'Failed to export PDF' });
    }
});

/**
 * GET /api/export/stock/excel
 * Export stock data to Excel
 */
router.get('/stock/excel', async (req, res) => {
    try {
        const [products] = await req.tenantDb.execute(`
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = true
            ORDER BY p.name
        `);

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Stock Report');

        sheet.columns = [
            { header: 'SKU', key: 'sku', width: 15 },
            { header: 'Nama Produk', key: 'name', width: 30 },
            { header: 'Kategori', key: 'category', width: 15 },
            { header: 'Stock', key: 'stock', width: 12 },
            { header: 'Min Stock', key: 'min_stock', width: 12 },
            { header: 'Status', key: 'status', width: 15 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        // Add data
        for (const product of products) {
            let status = 'In Stock';
            if (product.stock <= 0) status = 'Out of Stock';
            else if (product.stock <= product.min_stock) status = 'Low Stock';

            const row = sheet.addRow({
                sku: product.sku,
                name: product.name,
                category: product.category_name || '-',
                stock: product.stock,
                min_stock: product.min_stock,
                status: status
            });

            // Color code status
            const statusCell = row.getCell('status');
            if (status === 'Out of Stock') {
                statusCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFECACA' } };
                statusCell.font = { color: { argb: 'FFDC2626' } };
            } else if (status === 'Low Stock') {
                statusCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFEF3C7' } };
                statusCell.font = { color: { argb: 'FFD97706' } };
            } else {
                statusCell.fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFD1FAE5' } };
                statusCell.font = { color: { argb: 'FF059669' } };
            }
        }

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=stock_report_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export Stock Excel error:', error);
        res.status(500).json({ success: false, message: 'Failed to export' });
    }
});

/**
 * GET /api/export/stock/pdf
 * Export stock data to PDF
 */
router.get('/stock/pdf', async (req, res) => {
    try {
        const [products] = await req.tenantDb.execute(`
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE p.is_active = true
            ORDER BY p.name
        `);

        const doc = new PDFDocument({ margin: 50, size: 'A4' });
        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', `attachment; filename=stock_report_${Date.now()}.pdf`);
        doc.pipe(res);

        // Title
        doc.font('Helvetica-Bold').fontSize(18).text('Stock Report', { align: 'center' });
        doc.moveDown(0.5);
        doc.font('Helvetica').fontSize(10).text(`Generated: ${new Date().toLocaleString('id-ID')}`, { align: 'center' });
        doc.moveDown();

        // Summary
        const outOfStock = products.filter(p => p.stock <= 0).length;
        const lowStock = products.filter(p => p.stock > 0 && p.stock <= p.min_stock).length;
        const inStock = products.filter(p => p.stock > p.min_stock).length;

        doc.fontSize(10).text(`Out of Stock: ${outOfStock} | Low Stock: ${lowStock} | In Stock: ${inStock}`, { align: 'center' });
        doc.moveDown();

        // Table header
        const tableTop = 150;
        const colWidths = [70, 150, 80, 60, 60, 70];
        const headers = ['SKU', 'Nama', 'Kategori', 'Stock', 'Min', 'Status'];

        doc.font('Helvetica-Bold').fontSize(9);
        let xPos = 50;
        headers.forEach((header, i) => {
            doc.text(header, xPos, tableTop, { width: colWidths[i], align: 'left' });
            xPos += colWidths[i];
        });

        doc.moveTo(50, tableTop + 15).lineTo(545, tableTop + 15).stroke();

        // Table rows
        doc.font('Helvetica').fontSize(8);
        let yPos = tableTop + 25;

        for (const product of products) {
            if (yPos > 750) {
                doc.addPage();
                yPos = 50;
            }

            let status = 'In Stock';
            if (product.stock <= 0) status = 'Out of Stock';
            else if (product.stock <= product.min_stock) status = 'Low Stock';

            xPos = 50;
            const rowData = [
                product.sku?.substring(0, 12) || '-',
                product.name?.substring(0, 25) || '-',
                (product.category_name || '-').substring(0, 12),
                product.stock?.toString() || '0',
                product.min_stock?.toString() || '0',
                status
            ];

            rowData.forEach((text, i) => {
                doc.text(text, xPos, yPos, { width: colWidths[i], align: 'left' });
                xPos += colWidths[i];
            });

            yPos += 15;
        }

        doc.fontSize(8).text(`Total: ${products.length} produk`, 50, yPos + 10);
        doc.end();

    } catch (error) {
        console.error('Export Stock PDF error:', error);
        res.status(500).json({ success: false, message: 'Failed to export PDF' });
    }
});

/**
 * GET /api/export/template/products
 * Download Excel template for import
 * 
 * Columns (A-Y):
 * A: NAMAITEM, B: KATEGORI
 * C-G: SATUAN1-5 (1=base/terkecil)
 * H-L: KONVERSI1-5
 * M-Q: HARGABELI1-5
 * R-V: HARGAJUAL1-5
 * W: STOK, X: STOKMIN, Y: EXPIRY
 */
router.get('/template/products', async (req, res) => {
    try {
        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet('Template Produk');

        // Instructions sheet
        const infoSheet = workbook.addWorksheet('Petunjuk');
        infoSheet.getColumn(1).width = 80;
        infoSheet.getCell('A1').value = 'PETUNJUK IMPORT PRODUK';
        infoSheet.getCell('A1').font = { bold: true, size: 14 };
        infoSheet.getCell('A3').value = '1. Isi data di sheet "Template Produk"';
        infoSheet.getCell('A4').value = '2. SKU akan di-generate otomatis berdasarkan kategori';
        infoSheet.getCell('A5').value = '3. NAMAITEM, KATEGORI, dan SATUAN1 WAJIB diisi';
        infoSheet.getCell('A6').value = '4. SATUAN1 = satuan DASAR/terkecil (misal: PCS, GRAM, ML)';
        infoSheet.getCell('A7').value = '5. SATUAN2-5 = satuan lebih besar (opsional, misal: PAK, DUS, KARUNG)';
        infoSheet.getCell('A8').value = '6. KONVERSI1 = selalu 1 (satuan dasar)';
        infoSheet.getCell('A9').value = '7. KONVERSI2-5 = berapa SATUAN1 dalam 1 unit satuan tersebut';
        infoSheet.getCell('A10').value = '   Contoh: 1 DUS = 40 PCS, maka KONVERSI untuk DUS = 40';
        infoSheet.getCell('A11').value = '8. KATEGORI dan SATUAN harus sesuai dengan yang ada di sistem (lihat daftar di bawah)';
        infoSheet.getCell('A12').value = '9. STOK dalam satuan dasar (SATUAN1)';
        infoSheet.getCell('A13').value = '10. EXPIRY = tanggal kadaluarsa (format: YYYY-MM-DD atau DD/MM/YYYY, kosong = tidak ada expiry)';
        infoSheet.getCell('A14').value = '11. Nama kolom di baris 1 TIDAK BOLEH diubah';

        // Get categories and units for reference
        const [categories] = await req.tenantDb.execute('SELECT name FROM categories WHERE is_active = true');
        const [units] = await req.tenantDb.execute('SELECT name FROM units ORDER BY sort_order');

        // Add category and unit reference
        infoSheet.getCell('A15').value = 'DAFTAR KATEGORI (gunakan nama persis seperti ini):';
        infoSheet.getCell('A15').font = { bold: true };
        categories.forEach((cat, i) => {
            infoSheet.getCell(`A${16 + i}`).value = `  • ${cat.name}`;
        });

        const unitStartRow = 16 + categories.length + 1;
        infoSheet.getCell(`A${unitStartRow}`).value = 'DAFTAR SATUAN (gunakan nama persis seperti ini):';
        infoSheet.getCell(`A${unitStartRow}`).font = { bold: true };
        units.forEach((unit, i) => {
            infoSheet.getCell(`A${unitStartRow + 1 + i}`).value = `  • ${unit.name}`;
        });

        // Template columns - 24 columns (SATUAN1-5 from left to right)
        sheet.columns = [
            { header: 'NAMAITEM', key: 'name', width: 30 },
            { header: 'KATEGORI', key: 'category', width: 15 },
            { header: 'SATUAN1', key: 'unit1', width: 10 },
            { header: 'SATUAN2', key: 'unit2', width: 10 },
            { header: 'SATUAN3', key: 'unit3', width: 10 },
            { header: 'SATUAN4', key: 'unit4', width: 10 },
            { header: 'SATUAN5', key: 'unit5', width: 10 },
            { header: 'KONVERSI1', key: 'conv1', width: 10 },
            { header: 'KONVERSI2', key: 'conv2', width: 10 },
            { header: 'KONVERSI3', key: 'conv3', width: 10 },
            { header: 'KONVERSI4', key: 'conv4', width: 10 },
            { header: 'KONVERSI5', key: 'conv5', width: 10 },
            { header: 'HARGABELI1', key: 'buy1', width: 12 },
            { header: 'HARGABELI2', key: 'buy2', width: 12 },
            { header: 'HARGABELI3', key: 'buy3', width: 12 },
            { header: 'HARGABELI4', key: 'buy4', width: 12 },
            { header: 'HARGABELI5', key: 'buy5', width: 12 },
            { header: 'HARGAJUAL1', key: 'sell1', width: 12 },
            { header: 'HARGAJUAL2', key: 'sell2', width: 12 },
            { header: 'HARGAJUAL3', key: 'sell3', width: 12 },
            { header: 'HARGAJUAL4', key: 'sell4', width: 12 },
            { header: 'HARGAJUAL5', key: 'sell5', width: 12 },
            { header: 'STOK', key: 'stock', width: 10 },
            { header: 'STOKMIN', key: 'min_stock', width: 10 },
            { header: 'EXPIRY', key: 'expiry', width: 12 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        // Sample 1: Single unit product (non-expirable)
        sheet.addRow({
            name: 'Sabun Mandi',
            category: 'Toiletries',
            unit1: 'PCS',
            unit2: '',
            unit3: '',
            unit4: '',
            unit5: '',
            conv1: 1,
            conv2: '',
            conv3: '',
            conv4: '',
            conv5: '',
            buy1: 5000,
            buy2: '',
            buy3: '',
            buy4: '',
            buy5: '',
            sell1: 7000,
            sell2: '',
            sell3: '',
            sell4: '',
            sell5: '',
            stock: 50,
            min_stock: 10,
            expiry: '' // Non-expirable product
        });

        // Sample 2: Multi unit with expiry (PCS + DUS)
        sheet.addRow({
            name: 'Indomie Goreng',
            category: 'Makanan',
            unit1: 'PCS',
            unit2: 'DUS',
            unit3: '',
            unit4: '',
            unit5: '',
            conv1: 1,
            conv2: 40,
            conv3: '',
            conv4: '',
            conv5: '',
            buy1: 2500,
            buy2: 95000,
            buy3: '',
            buy4: '',
            buy5: '',
            sell1: 3000,
            sell2: 110000,
            sell3: '',
            sell4: '',
            sell5: '',
            stock: 100,
            min_stock: 20,
            expiry: '2027-06-30' // With expiry date
        });

        // Sample 3: Multi unit (KG + SAK + KARUNG) - non-expirable
        sheet.addRow({
            name: 'Gula Pasir',
            category: 'Sembako',
            unit1: 'KG',
            unit2: 'SAK',
            unit3: 'KARUNG',
            unit4: '',
            unit5: '',
            conv1: 1,
            conv2: 25,
            conv3: 50,
            conv4: '',
            conv5: '',
            buy1: 13000,
            buy2: 325000,
            buy3: 650000,
            buy4: '',
            buy5: '',
            sell1: 14000,
            sell2: 350000,
            sell3: 700000,
            sell4: '',
            sell5: '',
            stock: 500,
            min_stock: 50,
            expiry: '' // Non-expirable product
        });

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', 'attachment; filename=template_produk.xlsx');

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Template error:', error);
        res.status(500).json({ success: false, message: 'Failed to generate template' });
    }
});

/**
 * GET /api/export/transactions/excel
 * Export transactions to Excel
 */
router.get('/transactions/excel', async (req, res) => {
    try {
        const { from, to } = req.query;
        let sql = `
            SELECT t.*, s.user_id
            FROM transactions t
            JOIN shifts s ON t.shift_id = s.id
            WHERE t.status = 'completed'
        `;
        const params = [];

        if (from) {
            sql += ' AND DATE(t.created_at) >= ?';
            params.push(from);
        }
        if (to) {
            sql += ' AND DATE(t.created_at) <= ?';
            params.push(to);
        }

        sql += ' ORDER BY t.created_at DESC';

        const [transactions] = await req.tenantDb.execute(sql, params);

        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet('Transaksi');

        sheet.columns = [
            { header: 'No Invoice', key: 'invoice', width: 20 },
            { header: 'Tanggal', key: 'date', width: 20 },
            { header: 'Subtotal', key: 'subtotal', width: 15 },
            { header: 'Diskon', key: 'discount', width: 12 },
            { header: 'Pajak', key: 'tax', width: 12 },
            { header: 'Total', key: 'total', width: 15 },
            { header: 'Pembayaran', key: 'payment_method', width: 12 },
            { header: 'Status', key: 'status', width: 12 }
        ];

        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        for (const trans of transactions) {
            sheet.addRow({
                invoice: trans.invoice_number,
                date: new Date(trans.created_at).toLocaleString('id-ID'),
                subtotal: trans.subtotal,
                discount: trans.discount,
                tax: trans.tax,
                total: trans.total_amount,
                payment_method: trans.payment_method,
                status: trans.status
            });
        }

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=transactions_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export transactions error:', error);
        res.status(500).json({ success: false, message: 'Failed to export transactions' });
    }
});

/**
 * GET /api/export/suppliers/excel
 * Export suppliers to Excel
 */
router.get('/suppliers/excel', async (req, res) => {
    try {
        const [suppliers] = await req.tenantDb.execute('SELECT * FROM suppliers ORDER BY name');

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Suppliers');

        sheet.columns = [
            { header: 'Nama Supplier', key: 'name', width: 30 },
            { header: 'Kontak Person', key: 'contact_person', width: 20 },
            { header: 'Telepon', key: 'phone', width: 15 },
            { header: 'Alamat', key: 'address', width: 40 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        suppliers.forEach(s => {
            sheet.addRow({
                name: s.name,
                contact_person: s.contact_person,
                phone: s.phone,
                address: s.address
            });
        });

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=suppliers_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export suppliers error:', error);
        res.status(500).json({ success: false, message: 'Failed to export suppliers' });
    }
});

/**
 * GET /api/export/template/suppliers
 * Download Excel template for supplier import
 */
router.get('/template/suppliers', async (req, res) => {
    try {
        const workbook = new ExcelJS.Workbook();
        const sheet = workbook.addWorksheet('Template Supplier');

        sheet.columns = [
            { header: 'Nama Supplier (Wajib)', key: 'name', width: 30 },
            { header: 'Kontak Person', key: 'contact_person', width: 20 },
            { header: 'Telepon', key: 'phone', width: 15 },
            { header: 'Alamat', key: 'address', width: 40 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        // Add sample data
        sheet.addRow({
            name: 'PT Contoh Supplier',
            contact_person: 'Budi Santoso',
            phone: '081234567890',
            address: 'Jl. Sudirman No. 1, Jakarta'
        });

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', 'attachment; filename=template_suppliers.xlsx');

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Template error:', error);
        res.status(500).json({ success: false, message: 'Failed to generate template' });
    }
});

/**
 * GET /api/export/customers/excel
 * Export customers to Excel
 */
router.get('/customers/excel', async (req, res) => {
    try {
        const [customers] = await req.tenantDb.execute('SELECT * FROM customers ORDER BY name');

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Customers');

        sheet.columns = [
            { header: 'Nama Customer', key: 'name', width: 30 },
            { header: 'Email', key: 'email', width: 25 },
            { header: 'Telepon', key: 'phone', width: 15 },
            { header: 'Alamat', key: 'address', width: 40 },
            { header: 'Limit Hutang', key: 'credit_limit', width: 15 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        customers.forEach(c => {
            sheet.addRow({
                name: c.name,
                email: c.email,
                phone: c.phone,
                address: c.address,
                credit_limit: c.credit_limit
            });
        });

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=customers_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export customers error:', error);
        res.status(500).json({ success: false, message: 'Failed to export customers' });
    }
});

/**
 * GET /api/export/template/customers
 * Download Excel template for customer import
 */
router.get('/template/customers', async (req, res) => {
    try {
        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        const sheet = workbook.addWorksheet('Template Customer');

        sheet.columns = [
            { header: 'Nama Customer (Wajib)', key: 'name', width: 30 },
            { header: 'Email', key: 'email', width: 25 },
            { header: 'Telepon', key: 'phone', width: 15 },
            { header: 'Alamat', key: 'address', width: 40 },
            { header: 'Limit Hutang', key: 'credit_limit', width: 15 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        // Add sample data
        sheet.addRow({
            name: 'Andi pelanggan',
            email: 'andi@example.com',
            phone: '081299887766',
            address: 'Jl. Merdeka No. 45',
            credit_limit: 1000000
        });

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', 'attachment; filename=template_customers.xlsx');

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Template error:', error);
        res.status(500).json({ success: false, message: 'Failed to generate template' });
    }
});

/**
 * GET /api/export/debts/excel
 * Export supplier debts to Excel
 */
router.get('/debts/excel', async (req, res) => {
    try {
        const [debts] = await req.tenantDb.execute(`
            SELECT 
                p.id,
                p.invoice_number,
                p.date,
                p.total_amount as amount,
                COALESCE(p.paid_amount, 0) as paid,
                p.due_date,
                p.payment_status as status,
                s.name as supplier,
                s.phone
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE p.payment_status IN ('unpaid', 'partial')
            ORDER BY p.due_date ASC
        `);

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Supplier Debts');

        sheet.columns = [
            { header: 'Invoice', key: 'invoice', width: 20 },
            { header: 'Supplier', key: 'supplier', width: 25 },
            { header: 'Tanggal', key: 'date', width: 15 },
            { header: 'Jatuh Tempo', key: 'due_date', width: 15 },
            { header: 'Total', key: 'amount', width: 18 },
            { header: 'Dibayar', key: 'paid', width: 18 },
            { header: 'Sisa', key: 'remaining', width: 18 },
            { header: 'Status', key: 'status', width: 12 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFDC2626' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        let totalDebt = 0;
        for (const debt of debts) {
            const remaining = debt.amount - debt.paid;
            totalDebt += remaining;
            sheet.addRow({
                invoice: debt.invoice_number,
                supplier: debt.supplier,
                date: debt.date ? new Date(debt.date).toLocaleDateString('id-ID') : '-',
                due_date: debt.due_date ? new Date(debt.due_date).toLocaleDateString('id-ID') : '-',
                amount: debt.amount,
                paid: debt.paid,
                remaining: remaining,
                status: debt.status
            });
        }

        // Add total row
        const totalRow = sheet.addRow({
            invoice: '',
            supplier: 'TOTAL',
            date: '',
            due_date: '',
            amount: debts.reduce((s, d) => s + d.amount, 0),
            paid: debts.reduce((s, d) => s + d.paid, 0),
            remaining: totalDebt,
            status: ''
        });
        totalRow.font = { bold: true };

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=supplier_debts_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export debts error:', error);
        res.status(500).json({ success: false, message: 'Failed to export debts' });
    }
});

/**
 * GET /api/export/receivables/excel
 * Export customer receivables to Excel
 */
router.get('/receivables/excel', async (req, res) => {
    try {
        const [receivables] = await req.tenantDb.execute(`
            SELECT 
                t.id,
                t.invoice_number,
                t.created_at as date,
                t.total_amount as amount,
                COALESCE(t.payment_amount, 0) as paid,
                t.due_date,
                t.payment_status as status,
                c.name as customer,
                c.phone
            FROM transactions t
            LEFT JOIN customers c ON t.customer_id = c.id
            WHERE t.payment_status IN ('unpaid', 'partial', 'debt')
            ORDER BY t.due_date ASC
        `);

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        workbook.created = new Date();

        const sheet = workbook.addWorksheet('Customer Receivables');

        sheet.columns = [
            { header: 'Invoice', key: 'invoice', width: 20 },
            { header: 'Customer', key: 'customer', width: 25 },
            { header: 'Phone', key: 'phone', width: 15 },
            { header: 'Tanggal', key: 'date', width: 15 },
            { header: 'Jatuh Tempo', key: 'due_date', width: 15 },
            { header: 'Total', key: 'amount', width: 18 },
            { header: 'Dibayar', key: 'paid', width: 18 },
            { header: 'Sisa Piutang', key: 'remaining', width: 18 },
            { header: 'Status', key: 'status', width: 12 }
        ];

        // Style header
        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF059669' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        let totalReceivable = 0;
        for (const rec of receivables) {
            const remaining = rec.amount - rec.paid;
            totalReceivable += remaining;
            sheet.addRow({
                invoice: rec.invoice_number,
                customer: rec.customer,
                phone: rec.phone || '-',
                date: rec.date ? new Date(rec.date).toLocaleDateString('id-ID') : '-',
                due_date: rec.due_date ? new Date(rec.due_date).toLocaleDateString('id-ID') : '-',
                amount: rec.amount,
                paid: rec.paid,
                remaining: remaining,
                status: rec.status
            });
        }

        // Add total row
        const totalRow = sheet.addRow({
            invoice: '',
            customer: 'TOTAL',
            phone: '',
            date: '',
            due_date: '',
            amount: receivables.reduce((s, r) => s + r.amount, 0),
            paid: receivables.reduce((s, r) => s + r.paid, 0),
            remaining: totalReceivable,
            status: ''
        });
        totalRow.font = { bold: true };

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=customer_receivables_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export receivables error:', error);
        res.status(500).json({ success: false, message: 'Failed to export receivables' });
    }
});

/**
 * GET /api/export/purchases/excel
 * Export purchases (Goods In) to Excel
 */
router.get('/purchases/excel', async (req, res) => {
    try {
        const { from, to } = req.query;
        let sql = `
            SELECT p.*, s.name as supplier_name
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE 1=1
        `;
        const params = [];

        if (from) {
            sql += ' AND DATE(p.date) >= ?';
            params.push(from);
        }
        if (to) {
            sql += ' AND DATE(p.date) <= ?';
            params.push(to);
        }

        sql += ' ORDER BY p.date DESC';

        const [purchases] = await req.tenantDb.execute(sql, params);

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        const sheet = workbook.addWorksheet('Purchase Report');

        sheet.columns = [
            { header: 'Invoice/GRN', key: 'invoice', width: 22 },
            { header: 'Supplier', key: 'supplier', width: 25 },
            { header: 'Tanggal', key: 'date', width: 15 },
            { header: 'Jatuh Tempo', key: 'due_date', width: 15 },
            { header: 'Total', key: 'total', width: 18 },
            { header: 'Dibayar', key: 'paid', width: 18 },
            { header: 'Status', key: 'status', width: 12 }
        ];

        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FF4F46E5' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        for (const p of purchases) {
            sheet.addRow({
                invoice: p.invoice_number,
                supplier: p.supplier_name || '-',
                date: p.date ? new Date(p.date).toLocaleDateString('id-ID') : '-',
                due_date: p.due_date ? new Date(p.due_date).toLocaleDateString('id-ID') : '-',
                total: p.total_amount,
                paid: p.paid_amount || 0,
                status: p.payment_status
            });
        }

        // Total row
        const totalRow = sheet.addRow({
            invoice: '',
            supplier: 'TOTAL',
            date: '',
            due_date: '',
            total: purchases.reduce((s, p) => s + (p.total_amount || 0), 0),
            paid: purchases.reduce((s, p) => s + (p.paid_amount || 0), 0),
            status: ''
        });
        totalRow.font = { bold: true };

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=purchases_report_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export purchases error:', error);
        res.status(500).json({ success: false, message: 'Failed to export purchases' });
    }
});

/**
 * GET /api/export/returns/excel
 * Export returns to Excel
 */
router.get('/returns/excel', async (req, res) => {
    try {
        const [returns] = await req.tenantDb.execute(`
            SELECT r.*, s.name as supplier_name
            FROM returns r
            LEFT JOIN suppliers s ON r.supplier_id = s.id
            ORDER BY r.created_at DESC
        `);

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        const sheet = workbook.addWorksheet('Returns Report');

        sheet.columns = [
            { header: 'Kode Return', key: 'code', width: 20 },
            { header: 'Supplier', key: 'supplier', width: 25 },
            { header: 'Alasan', key: 'reason', width: 25 },
            { header: 'Tanggal', key: 'date', width: 15 },
            { header: 'Total Item', key: 'items', width: 12 },
            { header: 'Total Nilai', key: 'total', width: 18 },
            { header: 'Status', key: 'status', width: 12 }
        ];

        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = {
            type: 'pattern',
            pattern: 'solid',
            fgColor: { argb: 'FFD97706' }
        };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        for (const r of returns) {
            sheet.addRow({
                code: r.code,
                supplier: r.supplier_name || '-',
                reason: r.reason || '-',
                date: r.created_at ? new Date(r.created_at).toLocaleDateString('id-ID') : '-',
                items: r.total_items || 0,
                total: r.total_amount || 0,
                status: r.status
            });
        }

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=returns_report_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export returns error:', error);
        res.status(500).json({ success: false, message: 'Failed to export returns' });
    }
});

/**
 * GET /api/export/purchases/pdf
 * Export purchases to PDF
 */
router.get('/purchases/pdf', async (req, res) => {
    try {
        const { from, to, branch_id } = req.query;
        let sql = `
            SELECT p.*, s.name as supplier_name
            FROM purchases p
            LEFT JOIN suppliers s ON p.supplier_id = s.id
            WHERE 1=1
        `;
        const params = [];

        if (from) {
            sql += ' AND DATE(p.date) >= ?';
            params.push(from);
        }
        if (to) {
            sql += ' AND DATE(p.date) <= ?';
            params.push(to);
        }
        if (branch_id) {
            sql += ' AND p.branch_id = ?';
            params.push(branch_id);
        }

        sql += ' ORDER BY p.date DESC';

        const [purchases] = await req.tenantDb.execute(sql, params);

        const doc = new PDFDocument({ margin: 50, size: 'A4' });
        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', `attachment; filename=purchases_report_${Date.now()}.pdf`);
        doc.pipe(res);

        // Title
        doc.font('Helvetica-Bold').fontSize(18).text('Laporan Pembelian', { align: 'center' });
        doc.moveDown(0.5);
        doc.font('Helvetica').fontSize(10).text(`Periode: ${from || 'All'} s/d ${to || 'All'}`, { align: 'center' });
        doc.moveDown();

        // Summary
        const totalAmount = purchases.reduce((sum, p) => sum + parseFloat(p.total_amount || 0), 0);
        doc.fontSize(10).text(`Total Transaksi: ${purchases.length} | Total Nilai: Rp ${totalAmount.toLocaleString('id-ID')}`, { align: 'center' });
        doc.moveDown();

        // Table header
        const tableTop = 150;
        const colWidths = [100, 120, 80, 100, 80];
        const headers = ['Invoice', 'Supplier', 'Tanggal', 'Total', 'Status'];

        doc.font('Helvetica-Bold').fontSize(9);
        let xPos = 50;
        headers.forEach((header, i) => {
            doc.text(header, xPos, tableTop, { width: colWidths[i], align: 'left' });
            xPos += colWidths[i];
        });

        doc.moveTo(50, tableTop + 15).lineTo(545, tableTop + 15).stroke();

        // Table rows
        doc.font('Helvetica').fontSize(8);
        let yPos = tableTop + 25;

        const formatCurrency = (num) => `Rp ${(num || 0).toLocaleString('id-ID')}`;

        for (const purchase of purchases) {
            if (yPos > 750) {
                doc.addPage();
                yPos = 50;
            }

            xPos = 50;
            const rowData = [
                (purchase.invoice_number || '-').substring(0, 15),
                (purchase.supplier_name || '-').substring(0, 18),
                purchase.date ? new Date(purchase.date).toLocaleDateString('id-ID') : '-',
                formatCurrency(purchase.total_amount),
                purchase.payment_status || '-'
            ];

            rowData.forEach((text, i) => {
                doc.text(text, xPos, yPos, { width: colWidths[i], align: 'left' });
                xPos += colWidths[i];
            });

            yPos += 15;
        }

        doc.fontSize(8).text(`Total: ${purchases.length} transaksi`, 50, yPos + 10);
        doc.end();

    } catch (error) {
        console.error('Export purchases PDF error:', error);
        res.status(500).json({ success: false, message: 'Failed to export PDF' });
    }
});

/**
 * GET /api/export/returns/pdf
 * Export returns to PDF
 */
router.get('/returns/pdf', async (req, res) => {
    try {
        const [returns] = await req.tenantDb.execute(`
            SELECT r.*, s.name as supplier_name
            FROM returns r
            LEFT JOIN suppliers s ON r.supplier_id = s.id
            ORDER BY r.created_at DESC
        `);

        const doc = new PDFDocument({ margin: 50, size: 'A4' });
        res.setHeader('Content-Type', 'application/pdf');
        res.setHeader('Content-Disposition', `attachment; filename=returns_report_${Date.now()}.pdf`);
        doc.pipe(res);

        // Title
        doc.font('Helvetica-Bold').fontSize(18).text('Laporan Retur Barang', { align: 'center' });
        doc.moveDown(0.5);
        doc.font('Helvetica').fontSize(10).text(`Generated: ${new Date().toLocaleString('id-ID')}`, { align: 'center' });
        doc.moveDown();

        // Summary
        const totalValue = returns.reduce((sum, r) => sum + parseFloat(r.total_amount || 0), 0);
        doc.fontSize(10).text(`Total Retur: ${returns.length} | Total Nilai: Rp ${totalValue.toLocaleString('id-ID')}`, { align: 'center' });
        doc.moveDown();

        // Table header
        const tableTop = 150;
        const colWidths = [80, 100, 100, 90, 80];
        const headers = ['Kode', 'Supplier', 'Alasan', 'Total', 'Status'];

        doc.font('Helvetica-Bold').fontSize(9);
        let xPos = 50;
        headers.forEach((header, i) => {
            doc.text(header, xPos, tableTop, { width: colWidths[i], align: 'left' });
            xPos += colWidths[i];
        });

        doc.moveTo(50, tableTop + 15).lineTo(545, tableTop + 15).stroke();

        // Table rows
        doc.font('Helvetica').fontSize(8);
        let yPos = tableTop + 25;

        const formatCurrency = (num) => `Rp ${(num || 0).toLocaleString('id-ID')}`;

        for (const r of returns) {
            if (yPos > 750) {
                doc.addPage();
                yPos = 50;
            }

            xPos = 50;
            const rowData = [
                (r.code || '-').substring(0, 12),
                (r.supplier_name || '-').substring(0, 15),
                (r.reason || '-').substring(0, 15),
                formatCurrency(r.total_amount),
                r.status || '-'
            ];

            rowData.forEach((text, i) => {
                doc.text(text, xPos, yPos, { width: colWidths[i], align: 'left' });
                xPos += colWidths[i];
            });

            yPos += 15;
        }

        doc.fontSize(8).text(`Total: ${returns.length} retur`, 50, yPos + 10);
        doc.end();

    } catch (error) {
        console.error('Export returns PDF error:', error);
        res.status(500).json({ success: false, message: 'Failed to export PDF' });
    }
});

/**
 * GET /api/export/profit-loss/excel
 * Export profit & loss report to Excel
 */
router.get('/profit-loss/excel', async (req, res) => {
    try {
        const { date_from, date_to, branch_id } = req.query;

        let sql = `
            SELECT 
                p.id as product_id,
                p.name as product_name,
                SUM(ti.quantity) as total_qty,
                SUM(ti.quantity * COALESCE(ti.buy_price, 0)) as total_cost,
                SUM(ti.subtotal) as total_revenue,
                SUM(ti.subtotal) - SUM(ti.quantity * COALESCE(ti.buy_price, 0)) as profit
            FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            JOIN products p ON ti.product_id = p.id
            WHERE t.status = 'completed'
        `;
        const params = [];

        if (branch_id) { sql += ' AND t.branch_id = ?'; params.push(branch_id); }
        if (date_from) { sql += ' AND DATE(t.created_at) >= ?'; params.push(date_from); }
        if (date_to) { sql += ' AND DATE(t.created_at) <= ?'; params.push(date_to); }

        sql += ' GROUP BY p.id, p.name ORDER BY profit DESC';

        const [products] = await req.tenantDb.execute(sql, params);

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        const sheet = workbook.addWorksheet('Profit Loss Report');

        sheet.columns = [
            { header: 'Produk', key: 'product', width: 35 },
            { header: 'Qty Terjual', key: 'qty', width: 15 },
            { header: 'Total Cost', key: 'cost', width: 18 },
            { header: 'Total Revenue', key: 'revenue', width: 18 },
            { header: 'Profit', key: 'profit', width: 18 }
        ];

        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF4F46E5' } };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        for (const p of products) {
            sheet.addRow({
                product: p.product_name,
                qty: p.total_qty,
                cost: parseFloat(p.total_cost) || 0,
                revenue: parseFloat(p.total_revenue) || 0,
                profit: parseFloat(p.profit) || 0
            });
        }

        // Summary row
        const totalRevenue = products.reduce((sum, p) => sum + parseFloat(p.total_revenue || 0), 0);
        const totalCost = products.reduce((sum, p) => sum + parseFloat(p.total_cost || 0), 0);
        const totalProfit = products.reduce((sum, p) => sum + parseFloat(p.profit || 0), 0);

        const totalRow = sheet.addRow({
            product: 'TOTAL',
            qty: products.reduce((sum, p) => sum + parseFloat(p.total_qty || 0), 0),
            cost: totalCost,
            revenue: totalRevenue,
            profit: totalProfit
        });
        totalRow.font = { bold: true };

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=profit_loss_report_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export profit-loss Excel error:', error);
        res.status(500).json({ success: false, message: 'Failed to export' });
    }
});

/**
 * GET /api/export/top-products/excel
 * Export top products to Excel
 */
router.get('/top-products/excel', async (req, res) => {
    try {
        const { date_from, date_to, branch_id, limit = 50 } = req.query;

        let sql = `
            SELECT 
                p.id as product_id,
                p.name as product_name,
                SUM(ti.quantity) as total_qty,
                SUM(ti.subtotal) as total_sales
            FROM transaction_items ti
            JOIN transactions t ON ti.transaction_id = t.id
            JOIN products p ON ti.product_id = p.id
            WHERE t.status = 'completed'
        `;
        const params = [];

        if (branch_id) { sql += ' AND t.branch_id = ?'; params.push(branch_id); }
        if (date_from) { sql += ' AND DATE(t.created_at) >= ?'; params.push(date_from); }
        if (date_to) { sql += ' AND DATE(t.created_at) <= ?'; params.push(date_to); }

        sql += ` GROUP BY p.id, p.name ORDER BY total_qty DESC LIMIT ${parseInt(limit)}`;

        const [products] = await req.tenantDb.execute(sql, params);

        const workbook = new ExcelJS.Workbook();
        workbook.creator = 'SAGA TOKO APP';
        const sheet = workbook.addWorksheet('Top Products');

        sheet.columns = [
            { header: 'Rank', key: 'rank', width: 8 },
            { header: 'Produk', key: 'product', width: 40 },
            { header: 'Qty Terjual', key: 'qty', width: 15 },
            { header: 'Total Penjualan', key: 'sales', width: 20 }
        ];

        sheet.getRow(1).font = { bold: true };
        sheet.getRow(1).fill = { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FF10B981' } };
        sheet.getRow(1).font = { bold: true, color: { argb: 'FFFFFFFF' } };

        products.forEach((p, index) => {
            sheet.addRow({
                rank: index + 1,
                product: p.product_name,
                qty: p.total_qty,
                sales: parseFloat(p.total_sales) || 0
            });
        });

        res.setHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        res.setHeader('Content-Disposition', `attachment; filename=top_products_${Date.now()}.xlsx`);

        await workbook.xlsx.write(res);
        res.end();

    } catch (error) {
        console.error('Export top products Excel error:', error);
        res.status(500).json({ success: false, message: 'Failed to export' });
    }
});

module.exports = router;
