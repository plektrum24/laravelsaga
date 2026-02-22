<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InvoiceService
{
    /**
     * Generate PDF for invoice
     */
    public function generatePdf(Invoice $invoice): string
    {
        $invoice->load(['tenant', 'subscription.plan']);

        $data = [
            'invoice' => $invoice,
            'tenant' => $invoice->tenant,
            'subscription' => $invoice->subscription,
            'plan' => $invoice->subscription?->plan,
            'generatedAt' => Carbon::now(),
        ];

        $pdf = Pdf::loadView('pdfs.invoices.standard', $data);
        $pdf->setPaper('a4', 'portrait');

        // Save to storage
        $filename = "invoices/{$invoice->invoice_number}.pdf";
        $path = storage_path('app/public/' . $filename);

        // Ensure directory exists
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $pdf->save($path);

        // Update invoice with PDF path
        $invoice->update([
            'pdf_path' => $filename
        ]);

        return $filename;
    }

    /**
     * Download invoice PDF
     */
    public function downloadPdf(Invoice $invoice)
    {
        if (!$invoice->pdf_path) {
            $this->generatePdf($invoice);
        }

        $path = storage_path('app/public/' . $invoice->pdf_path);

        if (!file_exists($path)) {
            $this->generatePdf($invoice);
        }

        return response()->download($path, $invoice->invoice_number . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    /**
     * Send invoice via email (placeholder - implement with mail service)
     */
    public function sendEmail(Invoice $invoice): bool
    {
        // TODO: Implement email sending
        // For now, just mark as sent
        if ($invoice->status === 'draft') {
            $invoice->markAsSent();
        }

        return true;
    }

    /**
     * Generate invoices for all active subscriptions
     */
    public function generateRecurringInvoices(): int
    {
        $count = 0;
        $subscriptions = \App\Models\TenantSubscription::where('status', 'active')
            ->where('auto_renew', true)
            ->where('expires_at', '<=', Carbon::now()->addDays(7))
            ->get();

        foreach ($subscriptions as $subscription) {
            try {
                $this->createRenewalInvoice($subscription);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Failed to generate invoice for subscription {$subscription->id}", [
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $count;
    }

    /**
     * Create renewal invoice
     */
    private function createRenewalInvoice(\App\Models\TenantSubscription $subscription): Invoice
    {
        $plan = $subscription->plan;
        $amount = $subscription->billing_cycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        $invoice = Invoice::create([
            'tenant_id' => $subscription->tenant_id,
            'subscription_id' => $subscription->id,
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'amount' => $amount,
            'tax' => 0,
            'discount' => 0,
            'total' => $amount,
            'status' => 'sent',
            'due_date' => Carbon::now()->addDays(14),
            'notes' => "Subscription renewal - {$plan->name} ({$subscription->billing_cycle})",
        ]);

        // Generate PDF
        $this->generatePdf($invoice);

        // Send email
        $this->sendEmail($invoice);

        return $invoice;
    }

    /**
     * Get invoice statistics
     */
    public function getStats(): array
    {
        return [
            'total' => Invoice::count(),
            'paid' => Invoice::where('status', 'paid')->count(),
            'pending' => Invoice::whereIn('status', ['draft', 'sent'])->count(),
            'overdue' => Invoice::where('status', 'overdue')->count(),
            'total_revenue' => Invoice::where('status', 'paid')->sum('total'),
            'pending_revenue' => Invoice::whereIn('status', ['draft', 'sent'])->sum('total'),
        ];
    }

    /**
     * Mark overdue invoices
     */
    public function markOverdueInvoices(): int
    {
        $count = 0;
        $invoices = Invoice::where('status', 'sent')
            ->where('due_date', '<', Carbon::now())
            ->get();

        foreach ($invoices as $invoice) {
            $invoice->markAsOverdue();
            $count++;
        }

        return $count;
    }

    /**
     * Process payment for invoice
     */
    public function processPayment(Invoice $invoice, string $paymentMethod, ?string $gatewayId = null): bool
    {
        if ($invoice->isPaid()) {
            return false;
        }

        $invoice->markAsPaid($paymentMethod, $gatewayId);

        // Generate updated PDF with paid stamp
        if ($invoice->pdf_path) {
            $this->generatePdf($invoice);
        }

        // Update subscription status if needed
        $subscription = $invoice->subscription;
        if ($subscription && $subscription->status === 'suspended') {
            $subscription->resume();
        }

        return true;
    }
}
