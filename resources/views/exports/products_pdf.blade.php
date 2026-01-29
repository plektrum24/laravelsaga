<!DOCTYPE html>
<html>

<head>
    <title>Product List</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }

        th {
            bg-color: #f2f2f2;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .footer {
            text-align: right;
            font-size: 10px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2>Product Inventory List</h2>
        <p>Generated on: {{ date('Y-m-d H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th>Product Information</th>
                <th style="width: 80px;">Category</th>
                <th style="width: 150px;">Units & Pricing</th>
                <th style="width: 60px;">Stock</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $index => $product)
                @php
                    $units = $product->units->sortBy('conversion_qty');
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <div style="font-weight: bold;">{{ $product->name }}</div>
                        <div style="font-size: 10px; color: #666;">SKU: {{ $product->sku }} | Barcode:
                            {{ $product->barcode ?? '-' }}
                        </div>
                    </td>
                    <td>{{ $product->category->name ?? '-' }}</td>
                    <td>
                        @foreach($units as $u)
                            <div
                                style="margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px dashed #eee; last-child: border:0;">
                                <strong>{{ $u->unit->name ?? '-' }}</strong><br>
                                <span style="font-size: 10px;">
                                    Beli: Rp {{ number_format($u->buy_price, 0, ',', '.') }}<br>
                                    Jual: Rp {{ number_format($u->sell_price, 0, ',', '.') }}
                                    @if(!$u->is_base_unit)
                                        <br>(Isi: {{ (float) $u->conversion_qty }})
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </td>
                    <td>{{ number_format($product->stock, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Total Products: {{ count($products) }}</p>
    </div>
</body>

</html>