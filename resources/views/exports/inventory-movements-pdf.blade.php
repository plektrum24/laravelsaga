<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Movements Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
        }
        table.data-table td {
            border: 1px solid #ddd;
            padding: 6px;
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
        .badge {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-add {
            background-color: #d4edda;
            color: #155724;
        }
        .badge-subtract {
            background-color: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>INVENTORY MOVEMENTS REPORT</h1>
        <p>Stock Tracking & Adjustments</p>
    </div>

    <div class="summary">
        <p><strong>Total Movements:</strong> {{ $movements->count() }} records</p>
        <p><strong>Additions:</strong> {{ $movements->where('qty', '>', 0)->count() }} | 
           <strong>Reductions:</strong> {{ $movements->where('qty', '<', 0)->count() }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Date/Time</th>
                <th style="width: 15%;">Reference</th>
                <th style="width: 20%;">Product</th>
                <th style="width: 15%;">Branch</th>
                <th style="width: 10%;" class="text-center">Type</th>
                <th style="width: 10%;" class="text-right">Qty</th>
                <th style="width: 13%;" class="text-right">Current Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movements as $index => $movement)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>{{ date('d/m/Y H:i', strtotime($movement->created_at)) }}</td>
                <td>{{ $movement->reference_number }}</td>
                <td>
                    {{ $movement->product ? $movement->product->name : '-' }}
                    @if($movement->product && $movement->product->sku)
                        <br><small style="color: #666;">{{ $movement->product->sku }}</small>
                    @endif
                </td>
                <td>{{ $movement->branch ? $movement->branch->name : '-' }}</td>
                <td class="text-center">
                    <span class="badge {{ $movement->qty > 0 ? 'badge-add' : 'badge-subtract' }}">
                        {{ ucfirst($movement->type) }}
                    </span>
                </td>
                <td class="text-right" style="color: {{ $movement->qty > 0 ? '#155724' : '#721c24' }}; font-weight: bold;">
                    {{ $movement->qty > 0 ? '+' : '' }}{{ number_format($movement->qty, 2) }}
                </td>
                <td class="text-right">{{ number_format($movement->current_stock, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Generated on {{ date('d F Y H:i:s') }} by SAGA POS System</p>
    </div>
</body>
</html>
