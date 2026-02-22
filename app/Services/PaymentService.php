<?php

namespace App\Services;

use App\Models\WebOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected $gateway;
    protected $config;

    public function __construct()
    {
        // Default to manual payment (can be changed to Midtrans/Xendit)
        $this->gateway = config('payment.default_gateway', 'manual');
        $this->config = config('payment.gateways.' . $this->gateway, []);
    }

    /**
     * Create payment transaction
     */
    public function createPayment(WebOrder $order, $paymentMethod)
    {
        switch ($this->gateway) {
            case 'midtrans':
                return $this->createMidtransPayment($order, $paymentMethod);
            case 'xendit':
                return $this->createXenditPayment($order, $paymentMethod);
            case 'manual':
            default:
                return $this->createManualPayment($order, $paymentMethod);
        }
    }

    /**
     * Manual payment (COD/Bank Transfer)
     */
    protected function createManualPayment(WebOrder $order, $paymentMethod)
    {
        // For manual payment, just update order
        $order->update([
            'payment_method' => $paymentMethod,
            'payment_status' => 'pending',
        ]);

        return [
            'success' => true,
            'payment_type' => 'manual',
            'payment_method' => $paymentMethod,
            'instructions' => $this->getManualInstructions($paymentMethod),
            'order' => $order,
        ];
    }

    /**
     * Midtrans payment
     */
    protected function createMidtransPayment(WebOrder $order, $paymentMethod)
    {
        try {
            $serverKey = $this->config['server_key'] ?? '';
            $isProduction = $this->config['production'] ?? false;
            
            $baseUrl = $isProduction 
                ? 'https://app.midtrans.com/snap/v1/transactions'
                : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

            $payload = [
                'transaction_details' => [
                    'order_id' => $order->order_number,
                    'gross_amount' => (int) $order->total,
                ],
                'customer_details' => [
                    'first_name' => $order->customer_name ?? 'Customer',
                    'email' => $order->customer_email ?? '',
                    'phone' => $order->customer_phone ?? '',
                ],
                'enabled_payments' => [$paymentMethod],
            ];

            $response = Http::withBasicAuth($serverKey, '')
                ->post($baseUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                $order->update([
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'payment_type' => 'midtrans',
                    'snap_token' => $data['token'] ?? null,
                    'snap_url' => $data['redirect_url'] ?? null,
                    'order' => $order,
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment gateway error',
                'error' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Midtrans payment error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Xendit payment
     */
    protected function createXenditPayment(WebOrder $order, $paymentMethod)
    {
        try {
            $secretKey = $this->config['secret_key'] ?? '';
            
            $response = Http::withBasicAuth($secretKey, '')
                ->post('https://api.xendit.co/v2/invoices', [
                    'external_id' => $order->order_number,
                    'amount' => (int) $order->total,
                    'description' => 'Order #' . $order->order_number,
                    'customer_details' => [
                        'given_names' => $order->customer_name ?? 'Customer',
                        'email' => $order->customer_email ?? '',
                        'mobile_number' => $order->customer_phone ?? '',
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $order->update([
                    'payment_method' => $paymentMethod,
                    'payment_status' => 'pending',
                ]);

                return [
                    'success' => true,
                    'payment_type' => 'xendit',
                    'invoice_url' => $data['invoice_url'] ?? null,
                    'order' => $order,
                ];
            }

            return [
                'success' => false,
                'message' => 'Payment gateway error',
                'error' => $response->json(),
            ];

        } catch (\Exception $e) {
            Log::error('Xendit payment error: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Handle payment callback/notification
     */
    public function handleCallback($orderNumber, $paymentStatus, $transactionId = null)
    {
        $order = WebOrder::where('order_number', $orderNumber)->first();
        
        if (!$order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        switch ($paymentStatus) {
            case 'capture':
            case 'settlement':
                // Payment successful
                $order->update([
                    'payment_status' => 'paid',
                    'status' => WebOrder::STATUS_CONFIRMED,
                    'confirmed_at' => now(),
                ]);
                
                // Send notification
                $this->sendPaymentSuccessNotification($order);
                
                break;
                
            case 'pending':
                // Waiting for payment
                $order->update([
                    'payment_status' => 'pending',
                ]);
                break;
                
            case 'cancel':
            case 'expire':
            case 'deny':
                // Payment failed/cancelled
                $order->update([
                    'payment_status' => 'cancelled',
                    'status' => WebOrder::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);
                
                // Restore stock
                $this->restoreOrderStock($order);
                
                break;
        }

        return ['success' => true, 'order' => $order];
    }

    /**
     * Get manual payment instructions
     */
    protected function getManualInstructions($paymentMethod)
    {
        $instructions = [
            'cod' => [
                'title' => 'Cash on Delivery (COD)',
                'steps' => [
                    'Pesanan akan dikirim ke alamat Anda',
                    'Bayar saat paket diterima',
                    'Siapkan uang tunai sesuai total pesanan',
                ],
            ],
            'bank_transfer' => [
                'title' => 'Bank Transfer',
                'steps' => [
                    'Transfer ke rekening: BCA 1234567890',
                    'Atas nama: PT Toko Anda',
                    'Konfirmasi pembayaran via WhatsApp',
                ],
            ],
        ];

        return $instructions[$paymentMethod] ?? null;
    }

    /**
     * Send payment success notification
     */
    protected function sendPaymentSuccessNotification(WebOrder $order)
    {
        // Send email notification
        // This would use Laravel's Mail facade
        Log::info('Payment success notification sent for order: ' . $order->order_number);
    }

    /**
     * Restore stock when order is cancelled
     */
    protected function restoreOrderStock(WebOrder $order)
    {
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->qty);
        }
    }

    /**
     * Verify payment status
     */
    public function verifyPayment($orderNumber)
    {
        switch ($this->gateway) {
            case 'midtrans':
                return $this->verifyMidtransPayment($orderNumber);
            case 'xendit':
                return $this->verifyXenditPayment($orderNumber);
            default:
                $order = WebOrder::where('order_number', $orderNumber)->first();
                return [
                    'success' => true,
                    'status' => $order ? $order->payment_status : 'not_found',
                ];
        }
    }

    /**
     * Verify Midtrans payment status
     */
    protected function verifyMidtransPayment($orderNumber)
    {
        try {
            $serverKey = $this->config['server_key'] ?? '';
            
            $response = Http::withBasicAuth($serverKey, '')
                ->get('https://api.midtrans.com/v2/' . $orderNumber . '/status');

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'status' => $data['transaction_status'] ?? 'unknown',
                    'data' => $data,
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Verify Xendit payment status
     */
    protected function verifyXenditPayment($orderNumber)
    {
        try {
            $secretKey = $this->config['secret_key'] ?? '';
            
            $response = Http::withBasicAuth($secretKey, '')
                ->get('https://api.xendit.co/v2/invoices?external_id=' . $orderNumber);

            if ($response->successful()) {
                $data = $response->json();
                $invoice = $data[0] ?? null;
                
                return [
                    'success' => true,
                    'status' => $invoice['status'] ?? 'unknown',
                    'data' => $invoice,
                ];
            }

            return ['success' => false, 'message' => 'Verification failed'];

        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
