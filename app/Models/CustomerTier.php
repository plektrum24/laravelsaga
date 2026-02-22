<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerTier extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'customer_id',
        'tier_id',
        'qualified_at',
        'valid_until',
        'previous_tier_id',
    ];

    protected $casts = [
        'qualified_at' => 'datetime',
        'valid_until' => 'datetime',
    ];

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the tier
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class, 'tier_id');
    }

    /**
     * Get the previous tier
     */
    public function previousTier(): BelongsTo
    {
        return $this->belongsTo(MembershipTier::class, 'previous_tier_id');
    }

    /**
     * Scope for current active tier
     */
    public function scopeCurrent($query)
    {
        return $query->whereNull('valid_until')
            ->orWhere('valid_until', '>', now());
    }

    /**
     * Check if tier is active
     */
    public function isActive(): bool
    {
        return $this->valid_until === null || $this->valid_until->isFuture();
    }

    /**
     * Get tier benefits
     */
    public function getBenefits(): array
    {
        return $this->tier->benefits ?? [];
    }

    /**
     * Get benefit value
     */
    public function getBenefit($key, $default = null)
    {
        return $this->tier->getBenefit($key, $default);
    }
}
