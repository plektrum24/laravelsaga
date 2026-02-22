<?php

namespace App\Services;

use App\Models\Tenant;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Models\TenantPaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingService
{
    protected $gateway;
    protected $config;

    public function __construct()
    {
        $this->gateway = config('payment.default_gateway', 'midtrans');
        $this->config = config('payment.gateways.' . $this->gateway, []);
    }

    /**
     * Create subscription for tenant
     */
    public function createSubscription(Tenant $tenant, SubscriptionPlan $plan, $billingCycle = 'monthly')
    {
        DB::connection('tenant')->beginTransaction();

        try {
            // Update tenant subscription
            $tenant->update([
                'subscription_id' => $plan->id,
                'subscription_status' => $plan->trial_days > 0 ? 'trial' : 'active',
                'trial_ends_at' => $plan->trial_days > 0 ? now()->addDays($plan->trial_days) : null,
                'subscription_expires_at' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                'auto_renew' => true,
            ]);

            // Create first invoice
            $invoice = $this->createInvoice($tenant, $plan, $billingCycle);

            DB::connection('tenant')->commit();

            return [
                'success' => true,
                'subscription' => $tenant,
                'invoice' => $invoice,
            ];

        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create invoice for subscription
     */
    public function createInvoice(Tenant $tenant, SubscriptionPlan $plan, $billingCycle = 'monthly')
    {
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $subtotal = $price;
        $tax = $subtotal * 0.11; // 11% PPN
        $total = $subtotal + $tax;

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $plan->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_type' => 'subscription',
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => 0,
            'total' => $total,
            'status' => 'sent',
            'due_date' => now()->addDays(30),
        ]);

        // Send invoice email
        $this->sendInvoiceEmail($invoice);

        return $invoice;
    }

    /**
     * Process payment for invoice
     */
    public function processPayment(Invoice $invoice, $paymentMethod = 'credit_card')
    {
        if ($invoice->status === 'paid') {
            return ['success' => false, 'message' => 'Invoice already paid'];
        }

        switch ($this->gateway) {
            case 'midtrans':
                return $this->processMidtransPayment($invoice, $paymentMethod);
            case 'xendit':
                return $this->processXenditPayment($invoice, $paymentMethod);
            default:
                return $this->processManualPayment($invoice);
        }
    }

    /**
     * Process Midtrans payment
     */
    protected function processMidtransPayment(Invoice $invoice, $paymentMethod)
    {
        $serverKey = $this->config['server_key'] ?? '';
        $isProduction = $this->config['production'] ?? false;

        $baseUrl = $isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $tenant = $invoice->tenant;

        $payload = [
            'transaction_details' => [
                'order_id' => $invoice->invoice_number,
                'gross_amount' => (int) $invoice->total,
            ],
            'customer_details' => [
                'first_name' => $tenant->owner_name ?? $tenant->name,
                'email' => $tenant->users->first()?->email ?? '',
                'phone' => $tenant->phone ?? '',
            ],
            'enabled_payments' => [$paymentMethod],
        ];

        $response = Http::withBasicAuth($serverKey, '')
            ->post($baseUrl, $payload);

        if ($response->successful()) {
            $data = $response->json();

            $invoice->update([
                'payment_gateway_id' => $data['token'] ?? null,
                'payment_method' => $paymentMethod,
            ]);

            return [
                'success' => true,
                'snap_token' => $data['token'] ?? null,
                'snap_url' => $data['redirect_url'] ?? null,
                'invoice' => $invoice,
            ];
        }

        return [
            'success' => false,
            'message' => 'Payment gateway error',
            'error' => $response->json(),
        ];
    }

    /**
     * Process Xendit payment
     */
    protected function processXenditPayment(Invoice $invoice, $paymentMethod)
    {
        $secretKey = $this->config['secret_key'] ?? '';

        $tenant = $invoice->tenant;

        $response = Http::withBasicAuth($secretKey, '')
            ->post('https://api.xendit.co/v2/invoices', [
                'external_id' => $invoice->invoice_number,
                'amount' => (int) $invoice->total,
                'description' => 'Subscription Invoice - ' . $tenant->name,
                'customer_details' => [
                    'given_names' => $tenant->owner_name ?? $tenant->name,
                    'email' => $tenant->users->first()?->email ?? '',
                    'mobile_number' => $tenant->phone ?? '',
                ],
            ]);

        if ($response->successful()) {
            $data = $response->json();

            $invoice->update([
                'payment_gateway_id' => $data['id'] ?? null,
                'payment_method' => $paymentMethod,
            ]);

            return [
                'success' => true,
                'invoice_url' => $data['invoice_url'] ?? null,
                'invoice' => $invoice,
            ];
        }

        return [
            'success' => false,
            'message' => 'Payment gateway error',
            'error' => $response->json(),
        ];
    }

    /**
     * Process manual payment (bank transfer/COD)
     */
    protected function processManualPayment(Invoice $invoice)
    {
        $invoice->update([
            'payment_method' => 'bank_transfer',
            'status' => 'sent',
        ]);

        return [
            'success' => true,
            'message' => 'Invoice created. Please transfer to BCA 1234567890',
            'invoice' => $invoice,
        ];
    }

    /**
     * Handle payment webhook/callback
     */
    public function handlePaymentCallback($invoiceNumber, $paymentStatus, $transactionId = null)
    {
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

        if (!$invoice) {
            return ['success' => false, 'message' => 'Invoice not found'];
        }

        switch ($paymentStatus) {
            case 'capture':
            case 'settlement':
                // Payment successful
                $invoice->markAsPaid();

                // Update tenant subscription status
                $tenant = $invoice->tenant;
                if ($tenant->subscription_status === 'trial' || $tenant->subscription_status === 'active') {
                    $tenant->update([
                        'subscription_status' => 'active',
                        'subscription_expires_at' => now()->addMonth(),
                    ]);
                }

                // Send payment confirmation email
                $this->sendPaymentConfirmationEmail($invoice);

                break;

            case 'pending':
                // Waiting for payment
                $invoice->update(['status' => 'sent']);
                break;

            case 'cancel':
            case 'expire':
            case 'deny':
                // Payment failed
                $invoice->update(['status' => 'cancelled']);
                break;
        }

        return ['success' => true, 'invoice' => $invoice];
    }

    /**
     * Process recurring billing
     */
    public function processRecurringBilling()
    {
        $today = now();
        $processed = 0;
        $failed = 0;

        // Get subscriptions expiring soon
        $subscriptions = Tenant::where('subscription_status', 'active')
            ->where('auto_renew', true)
            ->whereDate('subscription_expires_at', '<=', $today->addDays(7))
            ->with('subscription')
            ->get();

        foreach ($subscriptions as $tenant) {
            if (!$tenant->subscription) {
                continue;
            }

            $billingCycle = $tenant->subscription_expires_at->diffInDays($today) > 300 ? 'yearly' : 'monthly';

            // Create renewal invoice
            $invoice = $this->createInvoice($tenant, $tenant->subscription, $billingCycle);

            if ($invoice) {
                $processed++;
            } else {
                $failed++;
            }
        }

        return [
            'processed' => $processed,
            'failed' => $failed,
        ];
    }

    /**
     * Check and update overdue invoices
     */
    public function checkOverdueInvoices()
    {
        $overdue = Invoice::where('status', 'sent')
            ->where('due_date', '<', now())
            ->get();

        foreach ($overdue as $invoice) {
            $invoice->markAsOverdue();

            // Send overdue notification
            $this->sendOverdueNotification($invoice);
        }

        return $overdue->count();
    }

    /**
     * Suspend tenant for non-payment
     */
    public function suspendForNonPayment(Tenant $tenant)
    {
        $tenant->update([
            'subscription_status' => 'suspended',
            'is_active' => false,
        ]);

        // Send suspension notification
        $this->sendSuspensionNotification($tenant);

        return true;
    }

    /**
     * Generate invoice number
     */
    protected function generateInvoiceNumber()
    {
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        return "INV-{$date}-{$random}";
    }

    /**
     * Send invoice email
     */
    protected function sendInvoiceEmail(Invoice $invoice)
    {
        // This would use Laravel's Mail facade
        // For now, just log it
        \Log::info('Invoice email sent: ' . $invoice->invoice_number);
    }

    /**
     * Send payment confirmation email
     */
    protected function sendPaymentConfirmationEmail(Invoice $invoice)
    {
        \Log::info('Payment confirmation sent: ' . $invoice->invoice_number);
    }

    /**
     * Send overdue notification
     */
    protected function sendOverdueNotification(Invoice $invoice)
    {
        \Log::info('Overdue notification sent: ' . $invoice->invoice_number);
    }

    /**
     * Send suspension notification
     */
    protected function sendSuspensionNotification(Tenant $tenant)
    {
        \Log::info('Suspension notification sent to tenant: ' . $tenant->name);
    }
}
