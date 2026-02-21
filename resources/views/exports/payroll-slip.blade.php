<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji - {{ $payroll->employee->name }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #444; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 20px; font-weight: bold; text-transform: uppercase; color: #2563eb; }
        .document-title { font-size: 16px; margin-top: 5px; color: #666; }
        
        .info-section { width: 100%; margin-bottom: 30px; }
        .info-table { width: 100%; }
        .info-table td { padding: 4px 0; vertical-align: top; }
        .label { color: #666; width: 120px; }
        .value { font-weight: bold; }
        
        .salary-section { width: 100%; margin-bottom: 30px; border-collapse: collapse; }
        .salary-section th { background-color: #f3f4f6; text-align: left; padding: 10px; border-bottom: 1px solid #e5e7eb; }
        .salary-section td { padding: 10px; border-bottom: 1px solid #f3f4f6; }
        
        .total-section { float: right; width: 300px; margin-top: 20px; }
        .total-row { display: block; clear: both; padding: 10px 0; border-top: 1px solid #eee; }
        .total-label { float: left; width: 150px; }
        .total-value { float: right; text-align: right; font-weight: bold; }
        .grand-total { font-size: 18px; color: #2563eb; border-top: 2px solid #2563eb; padding-top: 15px; margin-top: 10px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .signature-area { margin-top: 50px; width: 100%; }
        .signature-box { float: right; width: 200px; text-align: center; }
        .signature-line { margin-top: 60px; border-top: 1px solid #333; padding-top: 5px; }

        .currency { font-family: 'DejaVu Sans', 'Helvetica', sans-serif; }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">{{ $tenant->name ?? 'SAGA TOKO APP' }}</div>
        <div class="document-title">SLIP GAJI KARYAWAN</div>
        <div>Periode: {{ \Carbon\Carbon::parse($payroll->period)->translatedFormat('F Y') }}</div>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td class="label">Nama Karyawan</td>
                <td class="value">: {{ $payroll->employee->name }}</td>
                <td class="label">ID Karyawan</td>
                <td class="value">: {{ $payroll->employee->nik }}</td>
            </tr>
            <tr>
                <td class="label">Jabatan</td>
                <td class="value">: {{ $payroll->employee->role }}</td>
                <td class="label">Cabang</td>
                <td class="value">: {{ $payroll->employee->branch->name ?? '-' }}</td>
            </tr>
            <tr>
                <td class="label">Status Pajak</td>
                <td class="value">: TK/0 (Default)</td>
                <td class="label">Metode Bayar</td>
                <td class="value">: Transfer Bank ({{ $payroll->employee->bank_name }})</td>
            </tr>
        </table>
    </div>

    <table class="salary-section">
        <thead>
            <tr>
                <th>DESKRIPSI PENDAPATAN</th>
                <th style="text-align: right;">JUMLAH (IDR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Gaji Pokok</td>
                <td style="text-align: right;">{{ number_format($payroll->basic_salary, 0, ',', '.') }}</td>
            </tr>
            @if($payroll->allowances > 0)
            <tr>
                <td>Total Tunjangan</td>
                <td style="text-align: right;">{{ number_format($payroll->allowances, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($payroll->bonuses > 0)
            <tr>
                <td>Bonus & Insentif</td>
                <td style="text-align: right;">{{ number_format($payroll->bonuses, 0, ',', '.') }}</td>
            </tr>
            @endif
        </tbody>
        <thead>
            <tr>
                <th>DESKRIPSI POTONGAN</th>
                <th style="text-align: right;">JUMLAH (IDR)</th>
            </tr>
        </thead>
        <tbody>
            @if($payroll->deductions > 0)
            <tr>
                <td>Potongan Absensi / Lainnya</td>
                <td style="text-align: right; color: #dc2626;">- {{ number_format($payroll->deductions, 0, ',', '.') }}</td>
            </tr>
            @else
            <tr>
                <td>- Tidak ada potongan -</td>
                <td style="text-align: right;">0</td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="total-section">
        <div class="total-row">
            <span class="total-label">Subtotal Pendapatan</span>
            <span class="total-value">{{ number_format($payroll->basic_salary + $payroll->allowances + $payroll->bonuses, 0, ',', '.') }}</span>
        </div>
        <div class="total-row">
            <span class="total-label">Total Potongan</span>
            <span class="total-value" style="color: #dc2626;">- {{ number_format($payroll->deductions, 0, ',', '.') }}</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">GAJI BERSIH (TAKE HOME PAY)</span>
            <span class="total-value">IDR {{ number_format($payroll->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div style="clear: both;"></div>

    <div class="signature-area">
        <div class="signature-box" style="float: left;">
            <p>Penerima,</p>
            <div class="signature-line">{{ $payroll->employee->name }}</div>
        </div>
        <div class="signature-box">
            <p>Dibuat Pada: {{ $date ?? date('d/m/Y') }}<br>Manager Operasional,</p>
            <div class="signature-line">Administrasi Payroll</div>
        </div>
    </div>

    <div class="footer">
        Slip gaji ini dihasilkan secara otomatis oleh <strong>SAGA TOKO APP</strong> dan merupakan bukti pembayaran yang sah.
    </div>
</body>
</html>
