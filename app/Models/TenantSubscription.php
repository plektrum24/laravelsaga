<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;

class TenantSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'started_at',
        'expires_at',
        'cancelled_at',
        'trial_ends_at',
        'billing_cycle',
        'auto_renew',
        'midtrans_subscription_id',
        'payment_gateway_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    /**
     * Status constants
     */
    const STATUS_TRIAL = 'trial';
    const STATUS_ACTIVE = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    /**
     * Billing cycle constants
     */
    const BILLING_MONTHLY = 'monthly';
    const BILLING_YEARLY = 'yearly';

    /**
     * Get tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get subscription plan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    /**
     * Get invoices
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'subscription_id');
    }

    /**
     * Get latest invoice
     */
    public function latestInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class, 'subscription_id')->latest();
    }

    /**
     * Scope for active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_TRIAL, self::STATUS_ACTIVE]);
    }

    /**
     * Scope for expiring soon
     */
    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('expires_at', '<=', Carbon::now()->addDays($days));
    }

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_TRIAL, self::STATUS_ACTIVE]);
    }

    /**
     * Check if subscription is in trial
     */
    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    /**
     * Check if subscription has expired
     */
    public function isExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED ||
               ($this->expires_at && $this->expires_at->isPast());
    }

    /**
     * Check if subscription is expiring soon
     */
    public function isExpiringSoon(int $days = 7): bool
    {
        if (!$this->expires_at) {
            return false;
        }

        return $this->expires_at->diffInDays(Carbon::now()) <= $days;
    }

    /**
     * Get days until expiry
     */
    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->expires_at) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($this->expires_at, false));
    }

    /**
     * Get days remaining in trial
     */
    public function getTrialDaysRemainingAttribute(): ?int
    {
        if (!$this->trial_ends_at) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($this->trial_ends_at, false));
    }

    /**
     * Check if plan can be downgraded
     */
    public function canDowngrade(): bool
    {
        // Cannot downgrade during trial
        if ($this->isTrial()) {
            return false;
        }

        // Cannot downgrade if already on free plan
        return !$this->plan->isFree();
    }

    /**
     * Activate subscription
     */
    public function activate(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
            'started_at' => Carbon::now(),
        ]);
    }

    /**
     * Suspend subscription
     */
    public function suspend(): void
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
        ]);
    }

    /**
     * Resume subscription
     */
    public function resume(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Cancel subscription
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => Carbon::now(),
            'auto_renew' => false,
        ]);
    }

    /**
     * Expire subscription
     */
    public function expire(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    /**
     * Extend subscription
     */
    public function extend(int $days): void
    {
        $newExpiry = $this->expires_at
            ? $this->expires_at->addDays($days)
            : Carbon::now()->addDays($days);

        $this->update([
            'expires_at' => $newExpiry,
        ]);
    }

    /**
     * Get next billing date
     */
    public function getNextBillingDateAttribute(): ?Carbon
    {
        if (!$this->expires_at) {
            return null;
        }

        return $this->expires_at;
    }

    /**
     * Get billing amount based on cycle
     */
    public function getBillingAmountAttribute(): float
    {
        if (!$this->plan) {
            return 0;
        }

        return $this->billing_cycle === self::BILLING_YEARLY
            ? $this->plan->price_yearly
            : $this->plan->price_monthly;
    }
}
