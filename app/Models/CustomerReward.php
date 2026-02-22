<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerReward extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'customer_id',
        'reward_id',
        'points_redeemed',
        'status',
        'fulfilled_at',
        'expiry_date',
        'notes',
    ];

    protected $casts = [
        'points_redeemed' => 'integer',
        'fulfilled_at' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_FULFILLED = 'fulfilled';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the reward
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }

    /**
     * Scope for pending rewards
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for fulfilled rewards
     */
    public function scopeFulfilled($query)
    {
        return $query->where('status', self::STATUS_FULFILLED);
    }

    /**
     * Scope for expired rewards
     */
    public function scopeExpired($query)
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Scope for not expired
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_FULFILLED])
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
            });
    }

    /**
     * Mark as fulfilled
     */
    public function markAsFulfilled(): void
    {
        $this->update([
            'status' => self::STATUS_FULFILLED,
            'fulfilled_at' => now(),
        ]);
        
        $this->reward?->decrementStock();
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
        
        // Optionally refund points
    }

    /**
     * Cancel reward
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
        
        $this->reward?->incrementStock();
    }

    /**
     * Check if reward is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if can be fulfilled
     */
    public function canBeFulfilled(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }
}
