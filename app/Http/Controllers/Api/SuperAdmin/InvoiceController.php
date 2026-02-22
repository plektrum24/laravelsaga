<?php

namespace App\Http\Controllers\Api\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * Display a listing of invoices
     */
    public function index(Request $request)
    {
        $query = Invoice::with(['tenant', 'subscription.plan']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tenant
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Search by invoice number
        if ($request->has('search')) {
            $query->where('invoice_number', 'like', "%{$request->search}%");
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
        $invoice->load(['tenant', 'subscription.plan']);

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
        return $this->invoiceService->downloadPdf($invoice);
    }

    /**
     * Mark invoice as paid manually
     */
    public function markAsPaid(Invoice $invoice, Request $request)
    {
        if ($invoice->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid'
            ], 400);
        }

        $invoice->markAsPaid(
            $request->get('payment_method', 'manual'),
            $request->get('payment_gateway_id')
        );

        // Regenerate PDF with paid status
        if ($invoice->pdf_path) {
            $this->invoiceService->generatePdf($invoice);
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as paid',
            'data' => $invoice->fresh()
        ]);
    }

    /**
     * Cancel invoice
     */
    public function cancel(Invoice $invoice)
    {
        if ($invoice->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel a paid invoice'
            ], 400);
        }

        $invoice->cancel();

        return response()->json([
            'success' => true,
            'message' => 'Invoice cancelled',
            'data' => $invoice->fresh()
        ]);
    }

    /**
     * Get invoice statistics
     */
    public function stats()
    {
        $stats = $this->invoiceService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Generate invoices manually (for testing)
     */
    public function generateRecurring()
    {
        $count = $this->invoiceService->generateRecurringInvoices();

        return response()->json([
            'success' => true,
            'message' => "Generated {$count} invoices",
            'data' => ['count' => $count]
        ]);
    }
}
