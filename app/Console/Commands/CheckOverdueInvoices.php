<?php

namespace App\Console\Commands;

use App\Services\InvoiceService;
use App\Models\Invoice;
use Illuminate\Console\Command;

class CheckOverdueInvoices extends Command
{
    protected $signature = 'saas:check-overdue';
    protected $description = 'Check and mark overdue invoices';

    protected $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        parent::__construct();
        $this->invoiceService = $invoiceService;
    }

    public function handle(): int
    {
        $this->info('Checking for overdue invoices...');

        $count = $this->invoiceService->markOverdueInvoices();

        $this->info("✓ Marked {$count} invoices as overdue");

        // Get overdue invoices for notification
        $overdueInvoices = Invoice::where('status', 'overdue')
            ->whereDate('due_date', '<', now())
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $daysOverdue = $invoice->due_date->diffInDays(now());
            $this->warn("Invoice {$invoice->invoice_number} is {$daysOverdue} days overdue");

            // TODO: Send overdue notification
        }

        return Command::SUCCESS;
    }
}
