<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchases Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 5px;
        }
        .info-table strong {
            min-width: 100px;
            display: inline-block;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        table.data-table th {
            background-color: #f0f0f0;
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
        }
        table.data-table td {
            border: 1px solid #ddd;
            padding: 6px;
            font-size: 11px;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .summary {
            margin-top: 15px;
            padding: 10px;
            background-color: #f5f5f5;
            border: 1px solid #ddd;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PURCHASES REPORT</h1>
        <p>Goods In / Receiving History</p>
    </div>

    <div class="summary">
        <div class="summary-row">
            <span><strong>Total Purchases:</strong></span>
            <span>{{ $purchases->count() }} records</span>
        </div>
        <div class="summary-row">
            <span><strong>Total Amount:</strong></span>
            <span>{{ rupiah($purchases->sum('total_amount')) }}</span>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 10%;">Date</th>
                <th style="width: 15%;">Reference</th>
                <th style="width: 20%;">Supplier</th>
                <th style="width: 15%;">Branch</th>
                <th style="width: 10%;">Items</th>
                <th style="width: 15%;" class="text-right">Total Amount</th>
                <th style="width: 10%;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchases as $index => $purchase)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y', strtotime($purchase->date)) }}</td>
                <td>{{ $purchase->reference_number }}</td>
                <td>{{ $purchase->supplier ? $purchase->supplier->name : '-' }}</td>
                <td>{{ $purchase->branch ? $purchase->branch->name : '-' }}</td>
                <td class="text-center">{{ $purchase->items->count() }}</td>
                <td class="text-right">{{ rupiah($purchase->total_amount) }}</td>
                <td class="text-center">
                    <span style="padding: 2px 8px; border-radius: 3px; background-color: {{ $purchase->status === 'completed' ? '#d4edda' : ($purchase->status === 'pending' ? '#fff3cd' : '#f8d7da') }}; color: {{ $purchase->status === 'completed' ? '#155724' : ($purchase->status === 'pending' ? '#856404' : '#721c24') }};">
                        {{ ucfirst($purchase->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('d F Y H:i:s') }} by SAGA POS System</p>
    </div>
</body>
</html>
