<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Receipt - {{ $transaction->invoice_number }}</title>
    <style>
        @page { margin: 0; }
        body { 
            font-family: 'Courier', 'monospace'; 
            font-size: 10px; 
            width: 80mm; 
            margin: 0; 
            padding: 5mm;
            line-height: 1.2;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .header { margin-bottom: 5mm; }
        .company-name { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .divider { border-top: 1px dashed #000; margin: 2mm 0; }
        
        .info-table, .items-table { width: 100%; border-collapse: collapse; }
        .items-table td { padding: 1mm 0; }
        
        .footer { margin-top: 5mm; font-size: 9px; }
        
        /* Layout for receipts */
        .price-col { width: 25mm; }
        .qty-col { width: 10mm; }
    </style>
</head>
<body>
    <div class="header text-center">
        <div class="company-name">{{ $tenant->name ?? 'SAGA TOKO APP' }}</div>
        <div>{{ $branch->name ?? 'Main Branch' }}</div>
        <div>{{ $branch->address ?? 'Digital POS System' }}</div>
    </div>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td>Inv: {{ $transaction->invoice_number }}</td>
            <td class="text-right">{{ $transaction->date->format('d/m/y H:i') }}</td>
        </tr>
        <tr>
            <td>Kasir: {{ $transaction->user->name ?? 'Admin' }}</td>
            <td class="text-right">Cust: {{ $transaction->customer->name ?? 'General' }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="items-table">
        @foreach($transaction->items as $item)
        <tr>
            <td colspan="3">{{ $item->product->name }}</td>
        </tr>
        <tr>
            <td class="qty-col">{{ number_format($item->qty, 0) }}x</td>
            <td class="text-right">{{ number_format($item->price, 0, ',', '.') }}</td>
            <td class="text-right price-col">{{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="divider"></div>

    <table class="info-table">
        <tr>
            <td class="bold">TOTAL</td>
            <td class="text-right bold">{{ number_format($transaction->grand_total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Bayar ({{ $transaction->payment_method }})</td>
            <td class="text-right">{{ number_format($transaction->paid_amount, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="text-right">{{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer text-center">
        <div class="bold">TERIMA KASIH</div>
        <div>Barang yang sudah dibeli</div>
        <div>tidak dapat ditukar/dikembalikan</div>
        <div style="margin-top: 2mm;">Powered by SAGA TOKO</div>
    </div>
</body>
</html>
