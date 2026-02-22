<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebOrder;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Initiate payment
     * POST /api/payments/initiate
     */
    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|exists:web_orders,order_number',
            'payment_method' => 'required|string',
        ]);

        $order = WebOrder::where('order_number', $validated['order_number'])->first();

        $result = $this->paymentService->createPayment($order, $validated['payment_method']);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Payment initiated',
                'data' => $result,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Payment initiation failed',
            ], 400);
        }
    }

    /**
     * Payment callback (for payment gateway notifications)
     * POST /api/payments/callback
     */
    public function callback(Request $request)
    {
        // This endpoint receives notifications from payment gateways
        $orderNumber = $request->input('order_id') ?? $request->input('external_id');
        $paymentStatus = $request->input('transaction_status') ?? $request->input('status');
        $transactionId = $request->input('transaction_id');

        $result = $this->paymentService->handleCallback(
            $orderNumber,
            $paymentStatus,
            $transactionId
        );

        return response()->json($result);
    }

    /**
     * Verify payment status
     * GET /api/payments/verify/{orderNumber}
     */
    public function verify($orderNumber)
    {
        $result = $this->paymentService->verifyPayment($orderNumber);

        return response()->json($result);
    }

    /**
     * Get payment methods
     * GET /api/payments/methods
     */
    public function getMethods()
    {
        $methods = [
            [
                'code' => 'cod',
                'name' => 'Cash on Delivery (COD)',
                'description' => 'Bayar saat paket diterima',
                'icon' => 'cash',
                'enabled' => true,
            ],
            [
                'code' => 'bank_transfer',
                'name' => 'Bank Transfer',
                'description' => 'Transfer ke rekening bank',
                'icon' => 'bank',
                'enabled' => true,
            ],
            [
                'code' => 'credit_card',
                'name' => 'Credit/Debit Card',
                'description' => 'Visa, Mastercard, JCB',
                'icon' => 'card',
                'enabled' => config('payment.gateways.midtrans.server_key') ? true : false,
            ],
            [
                'code' => 'gopay',
                'name' => 'GoPay',
                'description' => 'E-wallet Gojek',
                'icon' => 'gopay',
                'enabled' => config('payment.gateways.midtrans.server_key') ? true : false,
            ],
            [
                'code' => 'ovo',
                'name' => 'OVO',
                'description' => 'E-wallet OVO',
                'icon' => 'ovo',
                'enabled' => config('payment.gateways.midtrans.server_key') ? true : false,
            ],
            [
                'code' => 'dana',
                'name' => 'DANA',
                'description' => 'E-wallet DANA',
                'icon' => 'dana',
                'enabled' => config('payment.gateways.midtrans.server_key') ? true : false,
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $methods,
        ]);
    }

    /**
     * Cancel payment
     * POST /api/payments/cancel
     */
    public function cancel(Request $request)
    {
        $validated = $request->validate([
            'order_number' => 'required|exists:web_orders,order_number',
        ]);

        $order = WebOrder::where('order_number', $validated['order_number'])->first();

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot cancel paid order',
            ], 400);
        }

        $order->update([
            'payment_status' => 'cancelled',
            'status' => WebOrder::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->qty);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment cancelled',
        ]);
    }

    /**
     * Get payment status
     * GET /api/payments/status/{orderNumber}
     */
    public function status($orderNumber)
    {
        $order = WebOrder::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'payment_status' => $order->payment_status,
                'order_status' => $order->status,
                'total' => $order->total,
                'payment_method' => $order->payment_method,
            ],
        ]);
    }
}
