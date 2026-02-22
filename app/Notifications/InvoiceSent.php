<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoiceSent extends Notification
{
    use Queueable;

    protected $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Invoice #' . $this->invoice->invoice_number . ' from SAGA POS')
            ->greeting('Hello ' . ($this->invoice->tenant->name ?? 'Valued Customer') . '!')
            ->line('A new invoice has been generated for your subscription.')
            ->line('Invoice Details:')
            ->line('- Invoice Number: ' . $this->invoice->invoice_number)
            ->line('- Amount: Rp ' . number_format($this->invoice->total, 0, ',', '.'))
            ->line('- Due Date: ' . $this->invoice->due_date->format('d M Y'))
            ->action('View Invoice', url('/tenant/invoices/' . $this->invoice->id))
            ->line('Please make payment before the due date to avoid service interruption.')
            ->salutation('Thank you for using SAGA POS!');
    }

    public function toArray($notifiable): array
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->invoice_number,
            'amount' => $this->invoice->total,
            'due_date' => $this->invoice->due_date?->toDateString(),
            'type' => 'invoice_sent',
        ];
    }
}
