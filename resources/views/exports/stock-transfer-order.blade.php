<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Transfer Order - {{ $transfer->transfer_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        
        .header h2 {
            font-size: 14px;
            font-weight: normal;
            color: #666;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-cell {
            display: table-cell;
            padding: 4px 8px;
            width: 50%;
            vertical-align: top;
        }
        
        .info-label {
            font-weight: bold;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-pending { background-color: #fef3c7; color: #92400e; }
        .status-approved { background-color: #dbeafe; color: #1e40af; }
        .status-in-transit { background-color: #e9d5ff; color: #6b21a8; }
        .status-received { background-color: #d1fae5; color: #065f46; }
        .status-cancelled { background-color: #fee2e2; color: #991b1b; }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #ddd;
        }
        
        td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .signature-section {
            margin-top: 40px;
            display: table;
            width: 100%;
        }
        
        .signature-row {
            display: table-row;
        }
        
        .signature-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 10px;
            vertical-align: top;
        }
        
        .signature-box {
            border: 1px solid #333;
            height: 80px;
            margin-bottom: 5px;
        }
        
        .signature-label {
            font-size: 10px;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .signature-name {
            font-size: 11px;
            margin-top: 3px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
            text-align: center;
        }
        
        .notes {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9fafb;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .notes h4 {
            font-size: 11px;
            margin-bottom: 5px;
        }
        
        .notes p {
            font-size: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>STOCK TRANSFER ORDER</h1>
        <h2>{{ $transfer->transfer_number }}</h2>
    </div>

    <!-- Status -->
    <div style="text-align: right; margin-bottom: 15px;">
        <span class="status-badge status-{{ str_replace('_', '-', $transfer->status) }}">
            {{ str_replace('_', ' ', strtoupper($transfer->status)) }}
        </span>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">From Branch:</div>
                    <div>{{ $transfer->fromBranch->name ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">To Branch:</div>
                    <div>{{ $transfer->toBranch->name ?? '-' }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">Requested By:</div>
                    <div>{{ $transfer->requestedBy->name ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Request Date:</div>
                    <div>{{ $transfer->request_date ? $transfer->request_date->format('d M Y, H:i') : '-' }}</div>
                </div>
            </div>
            @if($transfer->approvedBy)
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">Approved By:</div>
                    <div>{{ $transfer->approvedBy->name ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Approval Date:</div>
                    <div>{{ $transfer->approval_date ? $transfer->approval_date->format('d M Y, H:i') : '-' }}</div>
                </div>
            </div>
            @endif
            @if($transfer->shippedBy)
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">Shipped By:</div>
                    <div>{{ $transfer->shippedBy->name ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Ship Date:</div>
                    <div>{{ $transfer->shipped_date ? $transfer->shipped_date->format('d M Y, H:i') : '-' }}</div>
                </div>
            </div>
            @endif
            @if($transfer->receivedBy)
            <div class="info-row">
                <div class="info-cell">
                    <div class="info-label">Received By:</div>
                    <div>{{ $transfer->receivedBy->name ?? '-' }}</div>
                </div>
                <div class="info-cell">
                    <div class="info-label">Receive Date:</div>
                    <div>{{ $transfer->received_date ? $transfer->received_date->format('d M Y, H:i') : '-' }}</div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 40%;">Product</th>
                <th style="width: 15%;" class="text-right">Requested</th>
                <th style="width: 15%;" class="text-right">Approved</th>
                <th style="width: 15%;" class="text-right">Shipped</th>
                <th style="width: 10%;" class="text-right">Received</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->items as $index => $item)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <div style="font-weight: bold;">{{ $item->product->name ?? '-' }}</div>
                    @if($item->product->sku)
                    <div style="font-size: 10px; color: #666;">SKU: {{ $item->product->sku }}</div>
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->qty_requested, 2) }}</td>
                <td class="text-right">{{ $item->qty_approved ? number_format($item->qty_approved, 2) : '-' }}</td>
                <td class="text-right">{{ $item->qty_shipped ? number_format($item->qty_shipped, 2) : '-' }}</td>
                <td class="text-right">{{ $item->qty_received ? number_format($item->qty_received, 2) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-right">Total Items:</th>
                <th class="text-right">{{ $transfer->total_items }}</th>
                <th class="text-right">-</th>
                <th class="text-right">-</th>
                <th class="text-right">-</th>
            </tr>
        </tfoot>
    </table>

    <!-- Notes -->
    @if($transfer->notes)
    <div class="notes">
        <h4>Notes:</h4>
        <p>{{ $transfer->notes }}</p>
    </div>
    @endif

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-row">
            <div class="signature-cell">
                <div class="signature-box"></div>
                <div class="signature-label">Requested By</div>
                <div class="signature-name">{{ $transfer->requestedBy->name ?? '-' }}</div>
            </div>
            @if($transfer->approvedBy)
            <div class="signature-cell">
                <div class="signature-box"></div>
                <div class="signature-label">Approved By</div>
                <div class="signature-name">{{ $transfer->approvedBy->name ?? '-' }}</div>
            </div>
            @endif
            @if($transfer->shippedBy)
            <div class="signature-cell">
                <div class="signature-box"></div>
                <div class="signature-label">Shipped By</div>
                <div class="signature-name">{{ $transfer->shippedBy->name ?? '-' }}</div>
            </div>
            @endif
            @if($transfer->receivedBy)
            <div class="signature-cell">
                <div class="signature-box"></div>
                <div class="signature-label">Received By</div>
                <div class="signature-name">{{ $transfer->receivedBy->name ?? '-' }}</div>
            </div>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Generated on {{ now()->format('d M Y, H:i') }}</div>
        <div>This is a computer-generated document. No signature required.</div>
    </div>
</body>
</html>
