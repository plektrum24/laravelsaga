<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    protected $invoiceService;
    protected $paymentService;

    public function __construct(InvoiceService $invoiceService, PaymentGatewayService $paymentService)
    {
        $this->invoiceService = $invoiceService;
        $this->paymentService = $paymentService;
    }

    /**
     * Display a listing of tenant invoices
     */
    public function index(Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $query = Invoice::forTenant($tenant->id)
            ->with('subscription.plan');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $invoices
        ]);
    }

    /**
     * Display the specified invoice
     */
    public function show(Invoice $invoice)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $invoice->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $invoice->load('subscription.plan');

        return response()->json([
            'success' => true,
            'data' => $invoice
        ]);
    }

    /**
     * Download invoice PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $invoice->tenant_id !== $tenant->id) {
            abort(403);
        }

        return $this->invoiceService->downloadPdf($invoice);
    }

    /**
     * Pay invoice
     */
    public function payInvoice(Invoice $invoice, Request $request)
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant || $invoice->tenant_id !== $tenant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        $request->validate([
            'payment_method' => 'required|in:credit_card,bank_transfer,gopay,shopeepay',
        ]);

        $customerDetails = [
            'first_name' => $tenant->owner_name ?? $tenant->name,
            'email' => $tenant->email,
            'phone' => $tenant->phone,
        ];

        $result = $this->paymentService->createPaymentTransaction($invoice, $customerDetails);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment initiated',
            'data' => $result
        ]);
    }

    /**
     * Get invoice summary
     */
    public function summary()
    {
        $tenant = Auth::user()->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'Tenant not found'
            ], 404);
        }

        $summary = Invoice::forTenant($tenant->id)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "paid" THEN total ELSE 0 END) as paid_total,
                SUM(CASE WHEN status IN ("draft", "sent") THEN total ELSE 0 END) as pending_total,
                SUM(CASE WHEN status = "overdue" THEN total ELSE 0 END) as overdue_total
            ')
            ->first();

        // Get latest unpaid invoice
        $latestUnpaid = Invoice::forTenant($tenant->id)
            ->unpaid()
            ->latest()
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'summary' => $summary,
                'latest_unpaid' => $latestUnpaid,
            ]
        ]);
    }
}
