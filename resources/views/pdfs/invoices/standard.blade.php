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
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
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
            padding-bottom: 20px;
            border-bottom: 3px solid #3b82f6;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #3b82f6;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h1 {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 5px;
        }
        .invoice-number {
            color: #6b7280;
            font-size: 14px;
        }
        .info-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }
        .bill-to, .invoice-details {
            width: 48%;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .detail-row {
            margin-bottom: 5px;
        }
        .detail-label {
            color: #6b7280;
            font-size: 11px;
        }
        .detail-value {
            color: #1f2937;
            font-size: 13px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead {
            background-color: #f3f4f6;
        }
        .items-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #d1d5db;
        }
        .items-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table tr:last-child td {
            border-bottom: none;
        }
        .text-right {
            text-align: right;
        }
        .totals-section {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 30px;
        }
        .totals-table {
            width: 300px;
        }
        .totals-table tr td {
            padding: 8px 12px;
            border-bottom: 1px solid #e5e7eb;
        }
        .totals-table tr.total-row {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 14px;
        }
        .totals-table tr.total-row td {
            border-bottom: 2px solid #3b82f6;
            padding: 12px;
        }
        .notes-section {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        .notes-title {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }
        .notes-content {
            color: #6b7280;
            font-size: 11px;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-sent {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-overdue {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .status-draft {
            background-color: #f3f4f6;
            color: #374151;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 80px;
            color: rgba(220, 38, 38, 0.1);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
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
            <div class="invoice-title">
                <h1>INVOICE</h1>
                <div class="invoice-number">{{ $invoice->invoice_number }}</div>
            </div>
        </div>

        <!-- Info Section -->
        <div class="info-section">
            <div class="bill-to">
                <div class="section-title">Bill To</div>
                <div class="detail-row">
                    <div class="detail-value" style="font-weight: 600;">{{ $tenant->name }}</div>
                </div>
                @if($tenant->email)
                <div class="detail-row">
                    <div class="detail-value">{{ $tenant->email }}</div>
                </div>
                @endif
                @if($tenant->phone)
                <div class="detail-row">
                    <div class="detail-value">{{ $tenant->phone }}</div>
                </div>
                @endif
                @if($tenant->address)
                <div class="detail-row">
                    <div class="detail-value">{{ $tenant->address }}</div>
                </div>
                @endif
            </div>

            <div class="invoice-details">
                <div class="section-title">Invoice Details</div>
                <div class="detail-row">
                    <span class="detail-label">Invoice Date:</span>
                    <span class="detail-value">{{ $invoice->created_at->format('d M Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value">{{ $invoice->due_date ? $invoice->due_date->format('d M Y') : 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="status-badge status-{{ $invoice->status }}">
                        {{ ucfirst($invoice->status) }}
                    </span>
                </div>
                @if($plan)
                <div class="detail-row">
                    <span class="detail-label">Plan:</span>
                    <span class="detail-value">{{ $plan->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Billing Cycle:</span>
                    <span class="detail-value">{{ ucfirst($subscription->billing_cycle) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 60%;">Description</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: 600;">
                            @if($plan)
                                {{ $plan->name }} Subscription
                            @else
                                Subscription Fee
                            @endif
                        </div>
                        @if($invoice->notes)
                            <div style="color: #6b7280; font-size: 11px; margin-top: 4px;">
                                {{ $invoice->notes }}
                            </div>
                        @endif
                        @if($subscription)
                            <div style="color: #6b7280; font-size: 11px; margin-top: 4px;">
                                Period: {{ $subscription->billing_cycle }}
                            </div>
                        @endif
                    </td>
                    <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td>Subtotal</td>
                    <td class="text-right">Rp {{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->tax > 0)
                <tr>
                    <td>Tax</td>
                    <td class="text-right">Rp {{ number_format($invoice->tax, 0, ',', '.') }}</td>
                </tr>
                @endif
                @if($invoice->discount > 0)
                <tr>
                    <td>Discount</td>
                    <td class="text-right">- Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>Total</td>
                    <td class="text-right">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Notes -->
        @if($invoice->notes || true)
        <div class="notes-section">
            <div class="notes-title">Payment Instructions</div>
            <div class="notes-content">
                <p style="margin-bottom: 6px;">Please make payment within 14 days of invoice date.</p>
                <p>For questions regarding this invoice, please contact support@sagaposo.com</p>
            </div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div>Thank you for your business!</div>
            <div style="margin-top: 6px;">
                SAGA POS | support@sagaposo.com | https://sagaposo.com
            </div>
            <div style="margin-top: 6px;">
                Generated: {{ $generatedAt->format('d M Y H:i') }}
            </div>
        </div>
    </div>

    @if($invoice->status === 'paid')
    <div class="watermark">PAID</div>
    @endif
</body>
</html>
