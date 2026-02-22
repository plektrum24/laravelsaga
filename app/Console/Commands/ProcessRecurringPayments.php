<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use App\Services\SubscriptionService;
use App\Models\Invoice;
use App\Models\TenantSubscription;
use App\Notifications\SubscriptionExpiring;
use Illuminate\Console\Command;
use Carbon\Carbon;

class ProcessRecurringPayments extends Command
{
    protected $signature = 'saas:process-recurring';
    protected $description = 'Process recurring subscription payments and generate invoices';

    protected $invoiceService;
    protected $subscriptionService;

    public function __construct(InvoiceService $invoiceService, SubscriptionService $subscriptionService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
        $this->subscriptionService = $subscriptionService;
    }

    public function handle(): int
    {
        $this->info('Starting recurring payment processing...');

        // 1. Process expiring subscriptions (auto-renew)
        $this->info('Processing expiring subscriptions...');
        $renewed = $this->subscriptionService->processExpiringSubscriptions();
        $this->info("✓ Renewed {$renewed} subscriptions");

        // 2. Process expired trials
        $this->info('Processing expired trials...');
        $expired = $this->subscriptionService->processExpiredTrials();
        $this->info("✓ Expired {$expired} trials");

        // 3. Generate invoices for upcoming renewals (7 days before expiry)
        $this->info('Generating renewal invoices...');
        $invoicesGenerated = $this->invoiceService->generateRecurringInvoices();
        $this->info("✓ Generated {$invoicesGenerated} invoices");

        // 4. Mark overdue invoices
        $this->info('Checking for overdue invoices...');
        $overdueCount = $this->invoiceService->markOverdueInvoices();
        $this->info("✓ Marked {$overdueCount} invoices as overdue");

        // 5. Send expiry reminders
        $this->info('Sending expiry reminders...');
        $remindersSent = $this->sendExpiryReminders();
        $this->info("✓ Sent {$remindersSent} reminders");

        $this->info('Recurring payment processing completed!');

        return Command::SUCCESS;
    }

    protected function sendExpiryReminders(): int
    {
        $count = 0;
        $reminderDays = [7, 3, 1]; // Days before expiry to send reminder

        foreach ($reminderDays as $days) {
            $subscriptions = TenantSubscription::where('status', 'active')
                ->whereBetween('expires_at', [
                    Carbon::now()->addDays($days)->startOfDay(),
                    Carbon::now()->addDays($days)->endOfDay()
                ])
                ->get();

            foreach ($subscriptions as $subscription) {
                if ($subscription->tenant && $subscription->tenant->users->first()) {
                    $subscription->tenant->users->first()->notify(
                        new SubscriptionExpiring($subscription, $days)
                    );
                    $count++;
                }
            }
        }

        return $count;
    }
}
