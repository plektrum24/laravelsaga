<?php

namespace App\Notifications;

use App\Models\SupportTicket;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TicketStatusUpdated extends Notification
{
    use Queueable;

    protected $ticket;
    protected $oldStatus;
    protected $newStatus;

    public function __construct(SupportTicket $ticket, string $oldStatus, string $newStatus)
    {
        $this->ticket = $ticket;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Support Ticket #' . $this->ticket->id . ' Status Updated')
            ->greeting('Hello ' . ($this->ticket->creator->name ?? 'Valued Customer') . '!')
            ->line('Your support ticket status has been updated.')
            ->line('Ticket: ' . $this->ticket->subject)
            ->line('Previous Status: ' . ucfirst($this->oldStatus))
            ->line('New Status: ' . ucfirst($this->newStatus))
            ->action('View Ticket', url('/tenant/tickets/' . $this->ticket->id))
            ->salutation('Thank you for your patience!');
    }

    public function toArray($notifiable): array
    {
        return [
            'ticket_id' => $this->ticket->id,
            'ticket_subject' => $this->ticket->subject,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'type' => 'ticket_status_updated',
        ];
    }
}
