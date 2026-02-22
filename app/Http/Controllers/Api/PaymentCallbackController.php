<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentGatewayService;
use App\Services\InvoiceService;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentCallbackController extends Controller
{
    protected $paymentService;
    protected $invoiceService;

    public function __construct(PaymentGatewayService $paymentService, InvoiceService $invoiceService)
    {
        $this->paymentService = $paymentService;
        $this->invoiceService = $invoiceService;
    }

    /**
     * Handle Midtrans payment notification
     */
    public function midtransCallback(Request $request)
    {
        $notification = $request->all();

        Log::info('Midtrans callback received', $notification);

        // Verify notification signature (optional but recommended)
        // $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $invoice = $this->paymentService->processNotification($notification);

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process notification'
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment notification processed',
            'data' => [
                'invoice_id' => $invoice->id,
                'status' => $invoice->status,
            ]
        ]);
    }

    /**
     * Payment finish callback (redirect from payment gateway)
     */
    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        $statusCode = $request->get('status_code');
        $transactionId = $request->get('transaction_id');

        Log::info('Payment finish callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
        ]);

        $invoice = Invoice::where('invoice_number', $orderId)->first();

        if (!$invoice) {
            return redirect()->route('tenant.invoices.index')
                ->with('error', 'Invoice not found');
        }

        // Check payment status
        if ($statusCode === '200' || $statusCode === '201') {
            return redirect()->route('tenant.invoices.show', $invoice->id)
                ->with('success', 'Payment completed successfully!');
        }

        return redirect()->route('tenant.invoices.show', $invoice->id)
            ->with('error', 'Payment pending or failed. Please try again.');
    }

    /**
     * Get payment status for invoice
     */
    public function status($invoiceNumber)
    {
        $response = $this->paymentService->getPaymentStatus($invoiceNumber);

        if (!$response) {
            return response()->json([
                'success' => false,
                'message' => 'Payment status not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $response
        ]);
    }

    /**
     * Initiate payment for invoice
     */
    public function initiate(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|in:credit_card,bank_transfer,gopay,shopeepay',
        ]);

        $tenant = $invoice->tenant;

        $customerDetails = [
            'first_name' => $tenant->owner_name ?? $tenant->name,
            'email' => $tenant->email,
            'phone' => $tenant->phone,
        ];

        if ($request->payment_method === 'credit_card') {
            // Get Snap token for popup payment
            $token = $this->paymentService->getSnapToken($invoice, $customerDetails);

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to initiate payment'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'snap_token' => $token,
                    'payment_url' => "https://app.midtrans.com/snap/v2/vtweb/{$token}"
                ]
            ]);
        }

        // For other payment methods, create transaction
        $result = $this->paymentService->createPaymentTransaction($invoice, $customerDetails);

        if (!$result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }

    /**
     * Cancel payment
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|exists:invoices,invoice_number',
        ]);

        $invoice = Invoice::where('invoice_number', $request->invoice_number)->first();

        if (!$invoice) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        if ($invoice->isPaid()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel paid invoice'
            ], 400);
        }

        $result = $this->paymentService->cancelPayment($invoice->invoice_number);

        if ($result) {
            $invoice->cancel();
        }

        return response()->json([
            'success' => $result,
            'message' => $result ? 'Payment cancelled' : 'Failed to cancel payment'
        ]);
    }
}
