<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
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
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #4F46E5;
        }
        
        .invoice-details {
            text-align: right;
        }
        
        .invoice-details h1 {
            font-size: 28px;
            color: #4F46E5;
            margin-bottom: 5px;
        }
        
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        
        .bill-to {
            margin-bottom: 30px;
        }
        
        .bill-to h2 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .bill-to p {
            margin-bottom: 5px;
        }
        
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .invoice-table th {
            background-color: #4F46E5;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        
        .invoice-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .invoice-table tr:last-child td {
            border-bottom: none;
        }
        
        .totals {
            width: 100%;
            margin-bottom: 30px;
        }
        
        .totals-row {
            display: flex;
            justify-content: flex-end;
            padding: 8px 15px;
        }
        
        .totals-row.subtotal {
            background-color: #f9fafb;
        }
        
        .totals-row.tax {
            background-color: #f9fafb;
        }
        
        .totals-row.total {
            background-color: #4F46E5;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }
        
        .totals-label {
            width: 200px;
            text-align: right;
            margin-right: 20px;
        }
        
        .totals-value {
            width: 150px;
            text-align: right;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-draft { background-color: #f3f4f6; color: #374151; }
        .status-sent { background-color: #dbeafe; color: #1e40af; }
        .status-paid { background-color: #d1fae5; color: #065f46; }
        .status-overdue { background-color: #fee2e2; color: #991b1b; }
        .status-cancelled { background-color: #f3f4f6; color: #374151; }
        
        .payment-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .payment-info h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #4F46E5;
        }
        
        .payment-info p {
            margin-bottom: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">
                SAGA POS
            </div>
            <div class="invoice-details">
                <h1>INVOICE</h1>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
                <div style="margin-top: 5px;">
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ $invoice->status }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div class="bill-to">
            <h2>Bill To:</h2>
            <p><strong>{{ $tenant->name }}</strong></p>
            <p>{{ $tenant->address }}</p>
            <p>{{ $tenant->phone }}</p>
            <p>{{ $tenant->users->first()->email ?? '' }}</p>
        </div>

        <!-- Payment Info -->
        @if($invoice->status !== 'paid')
        <div class="payment-info">
            <h3>Payment Information</h3>
            <p><strong>Bank Transfer:</strong> BCA 1234567890</p>
            <p><strong>Account Name:</strong> PT SAGA POS</p>
            <p><strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>
        </div>
        @endif

        <!-- Invoice Items -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="width: 100px;">Period</th>
                    <th style="width: 100px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $plan->name ?? 'Subscription' }}</strong>
                        <div style="font-size: 11px; color: #666; margin-top: 5px;">
                            {{ $plan->code ?? 'subscription' }} Plan
                        </div>
                    </td>
                    <td>
                        {{ $invoice->created_at->format('M Y') }} - {{ $invoice->due_date->format('M Y') }}
                    </td>
                    <td style="text-align: right;">
                        Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="totals-row subtotal">
                <div class="totals-label">Subtotal:</div>
                <div class="totals-value">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</div>
            </div>
            <div class="totals-row tax">
                <div class="totals-label">Tax (11%):</div>
                <div class="totals-value">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</div>
            </div>
            @if($invoice->discount > 0)
            <div class="totals-row">
                <div class="totals-label">Discount:</div>
                <div class="totals-value">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</div>
            </div>
            @endif
            <div class="totals-row total">
                <div class="totals-label">Total:</div>
                <div class="totals-value">Rp {{ number_format($invoice->total, 0, ',', '.') }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For questions or concerns, please contact support@sagaposo.com</p>
            <p style="margin-top: 10px;">Generated on {{ now()->format('d M Y, H:i') }}</p>
        </div>
    </div>
</body>
</html>
