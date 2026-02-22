<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'price_monthly',
        'price_yearly',
        'features',
        'limits',
        'trial_days',
        'is_active',
        'priority',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'features' => 'array',
        'limits' => 'array',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Scope for active plans
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordering by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority');
    }

    /**
     * Get tenant subscriptions
     */
    public function tenantSubscriptions(): HasMany
    {
        return $this->hasMany(TenantSubscription::class);
    }

    /**
     * Check if plan has a feature
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return in_array($feature, $features, true);
    }

    /**
     * Get limit for a metric
     */
    public function getLimit(string $metric, int $default = 0): int
    {
        $limits = $this->limits ?? [];
        return $limits[$metric] ?? $default;
    }

    /**
     * Check if plan is free
     */
    public function isFree(): bool
    {
        return $this->code === 'free';
    }

    /**
     * Get formatted monthly price
     */
    public function getFormattedMonthlyPriceAttribute(): string
    {
        if ($this->price_monthly == 0) {
            return 'Free';
        }
        return 'Rp ' . number_format($this->price_monthly, 0, ',', '.');
    }

    /**
     * Get formatted yearly price
     */
    public function getFormattedYearlyPriceAttribute(): string
    {
        if ($this->price_yearly == 0) {
            return 'Free';
        }
        return 'Rp ' . number_format($this->price_yearly, 0, ',', '.');
    }

    /**
     * Calculate savings for yearly billing
     */
    public function getYearlySavingsAttribute(): float
    {
        if ($this->price_monthly == 0 || $this->price_yearly == 0) {
            return 0;
        }

        $monthlyTotal = $this->price_monthly * 12;
        $savings = $monthlyTotal - $this->price_yearly;

        return max(0, $savings);
    }

    /**
     * Get savings percentage
     */
    public function getYearlySavingsPercentAttribute(): float
    {
        if ($this->price_monthly == 0) {
            return 0;
        }

        $monthlyTotal = $this->price_monthly * 12;
        if ($monthlyTotal == 0) {
            return 0;
        }

        return round(($this->yearly_savings / $monthlyTotal) * 100, 1);
    }
}
