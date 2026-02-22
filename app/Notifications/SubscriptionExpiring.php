<?php

namespace App\Notifications;

use App\Models\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class SubscriptionExpiring extends Notification
{
    use Queueable;

    protected $subscription;
    protected $daysUntilExpiry;

    public function __construct(TenantSubscription $subscription, int $daysUntilExpiry)
    {
        $this->subscription = $subscription;
        $this->daysUntilExpiry = $daysUntilExpiry;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $planName = $this->subscription->plan?->name ?? 'your plan';
        $expiryDate = $this->subscription->expires_at?->format('d M Y') ?? 'soon';

        return (new MailMessage)
            ->subject('Subscription Expiring in ' . $this->daysUntilExpiry . ' Days')
            ->greeting('Hello ' . ($this->subscription->tenant->name ?? 'Valued Customer') . '!')
            ->line('This is a friendly reminder that your ' . $planName . ' subscription will expire in ' . $this->daysUntilExpiry . ' days.')
            ->line('Expiry Date: ' . $expiryDate)
            ->line('To continue enjoying uninterrupted service, please renew your subscription.')
            ->action('Renew Now', url('/tenant/subscription'))
            ->line('If you have any questions, please contact our support team.')
            ->salutation('Thank you for being a valued customer!');
    }

    public function toArray($notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_name' => $this->subscription->plan?->name,
            'expires_at' => $this->subscription->expires_at?->toDateString(),
            'days_until_expiry' => $this->daysUntilExpiry,
            'type' => 'subscription_expiring',
        ];
    }
}
