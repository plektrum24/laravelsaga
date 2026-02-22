<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentGatewayService
{
    protected $serverKey;
    protected $clientKey;
    protected $isProduction;
    protected $baseUrl;

    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key');
        $this->clientKey = config('services.midtrans.client_key');
        $this->isProduction = config('services.midtrans.is_production', false);
        $this->baseUrl = $this->isProduction
            ? 'https://app.midtrans.com'
            : 'https://app.sandbox.midtrans.com';
    }

    /**
     * Create subscription charge (for recurring payments)
     */
    public function createSubscription(TenantSubscription $subscription): ?string
    {
        $plan = $subscription->plan;
        $amount = $subscription->billing_cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->baseUrl}/api/v1/subscribe", [
                    'name' => $subscription->tenant->name . ' - ' . $plan->name,
                    'amount' => $amount,
                    'currency' => 'IDR',
                    'payment_type' => 'credit_card',
                    'customer_details' => [
                        'email' => $subscription->tenant->email,
                    ],
                    'metadata' => [
                        'subscription_id' => $subscription->id,
                        'tenant_id' => $subscription->tenant_id,
                        'plan_id' => $plan->id,
                    ]
                ]);

            if ($response->successful()) {
                $subscriptionId = $response->json('id');
                $subscription->update([
                    'midtrans_subscription_id' => $subscriptionId,
                ]);
                return $subscriptionId;
            }

            Log::error('Midtrans subscription creation failed', [
                'subscription_id' => $subscription->id,
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans subscription error', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Create payment transaction for invoice
     */
    public function createPaymentTransaction(Invoice $invoice, array $customerDetails): ?array
    {
        $transactionDetails = [
            'transaction_details' => [
                'order_id' => $invoice->invoice_number,
                'gross_amount' => (int) $invoice->total,
            ],
            'customer_details' => $customerDetails,
            'callbacks' => [
                'finish' => route('payment.callback'),
            ]
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->baseUrl}/api/v2/charge", $transactionDetails);

            if ($response->successful()) {
                $data = $response->json();

                // Update invoice with payment gateway ID
                $invoice->update([
                    'payment_gateway_id' => $data['transaction_id'] ?? null,
                    'payment_gateway_response' => json_encode($data),
                ]);

                return [
                    'redirect_url' => $data['redirect_url'] ?? null,
                    'token' => $data['token'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                ];
            }

            Log::error('Midtrans payment creation failed', [
                'invoice_id' => $invoice->id,
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans payment error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get payment status
     */
    public function getPaymentStatus(string $orderId): ?array
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->get("{$this->baseUrl}/api/v2/{$orderId}/status");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans status check error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Process payment notification/callback
     */
    public function processNotification(array $notification): ?Invoice
    {
        $orderId = $notification['order_id'] ?? null;
        $transactionStatus = $notification['transaction_status'] ?? null;
        $fraudStatus = $notification['fraud_status'] ?? null;

        if (!$orderId) {
            return null;
        }

        $invoice = Invoice::where('invoice_number', $orderId)->first();

        if (!$invoice) {
            Log::warning('Invoice not found for notification', ['order_id' => $orderId]);
            return null;
        }

        // Verify transaction status
        $statusResponse = $this->getPaymentStatus($orderId);

        if (!$statusResponse) {
            Log::error('Failed to verify payment status', ['order_id' => $orderId]);
            return null;
        }

        // Determine payment status
        $paymentStatus = $this->determinePaymentStatus($transactionStatus, $fraudStatus);

        if ($paymentStatus === 'paid') {
            $invoice->markAsPaid('midtrans', $statusResponse['transaction_id'] ?? null);

            // Resume subscription if it was suspended
            if ($invoice->subscription && $invoice->subscription->status === 'suspended') {
                $invoice->subscription->resume();
            }

            Log::info('Payment processed successfully', [
                'invoice_id' => $invoice->id,
                'transaction_id' => $statusResponse['transaction_id']
            ]);
        } elseif ($paymentStatus === 'pending') {
            $invoice->update(['status' => 'sent']);
        } elseif ($paymentStatus === 'failed' || $paymentStatus === 'cancelled') {
            $invoice->update(['status' => 'cancelled']);
        }

        return $invoice;
    }

    /**
     * Cancel payment/subscription
     */
    public function cancelPayment(string $orderId): bool
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->baseUrl}/api/v2/{$orderId}/cancel");

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Midtrans cancel error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Refund payment
     */
    public function refundPayment(string $orderId, int $amount, string $reason = ''): bool
    {
        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->baseUrl}/api/v2/{$orderId}/refund", [
                    'amount' => $amount,
                    'reason' => $reason,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Midtrans refund error', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Determine payment status based on Midtrans response
     */
    private function determinePaymentStatus(string $transactionStatus, ?string $fraudStatus): string
    {
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept' ? 'paid' : 'failed';
        }

        if ($transactionStatus === 'settlement') {
            return 'paid';
        }

        if ($transactionStatus === 'pending') {
            return 'pending';
        }

        if (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            return 'failed';
        }

        return 'unknown';
    }

    /**
     * Get Midtrans snap token for payment popup
     */
    public function getSnapToken(Invoice $invoice, array $customerDetails): ?string
    {
        $transactionDetails = [
            'transaction_details' => [
                'order_id' => $invoice->invoice_number,
                'gross_amount' => (int) $invoice->total,
            ],
            'customer_details' => $customerDetails,
        ];

        try {
            $response = Http::withBasicAuth($this->serverKey, '')
                ->post("{$this->baseUrl}/api/v2/snap/transactions", $transactionDetails);

            if ($response->successful()) {
                return $response->json('token');
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Midtrans snap token error', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
