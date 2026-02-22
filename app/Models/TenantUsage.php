<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUsage extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'metric',
        'current_value',
        'limit_value',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'current_value' => 'integer',
        'limit_value' => 'integer',
    ];

    /**
     * Common metric names
     */
    const METRIC_USERS = 'users';
    const METRIC_PRODUCTS = 'products';
    const METRIC_ORDERS = 'orders';
    const METRIC_BRANCHES = 'branches';
    const METRIC_STORAGE = 'storage_mb';

    /**
     * Get tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for current period
     */
    public function scopeCurrentPeriod($query)
    {
        return $query->where('period_start', '<=', now())
                     ->where('period_end', '>=', now());
    }

    /**
     * Scope for specific metric
     */
    public function scopeMetric($query, string $metric)
    {
        return $query->where('metric', $metric);
    }

    /**
     * Scope for tenant
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Check if usage is over limit
     */
    public function isOverLimit(): bool
    {
        return $this->limit_value > 0 && $this->current_value > $this->limit_value;
    }

    /**
     * Get usage percentage
     */
    public function getUsagePercentAttribute(): float
    {
        if ($this->limit_value <= 0) {
            return 0;
        }

        return min(100, round(($this->current_value / $this->limit_value) * 100, 2));
    }

    /**
     * Get remaining quota
     */
    public function getRemainingAttribute(): int
    {
        if ($this->limit_value <= 0) {
            return -1; // Unlimited
        }

        return max(0, $this->limit_value - $this->current_value);
    }

    /**
     * Check if unlimited
     */
    public function isUnlimited(): bool
    {
        return $this->limit_value <= 0;
    }

    /**
     * Increment usage
     */
    public function incrementUsage(int $amount = 1): void
    {
        $this->increment('current_value', $amount);
    }

    /**
     * Decrement usage
     */
    public function decrementUsage(int $amount = 1): void
    {
        $this->decrement('current_value', $amount);
    }

    /**
     * Update usage value
     */
    public function updateUsage(int $value): void
    {
        $this->update(['current_value' => $value]);
    }

    /**
     * Get or create usage record for tenant
     */
    public static function getOrCreate(
        int $tenantId,
        string $metric,
        int $limit = 0,
        ?\Carbon\Carbon $periodStart = null
    ): self {
        $periodStart = $periodStart ?? Carbon::now()->startOfMonth();
        $periodEnd = $periodStart->copy()->endOfMonth();

        return static::firstOrCreate(
            [
                'tenant_id' => $tenantId,
                'metric' => $metric,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ],
            [
                'current_value' => 0,
                'limit_value' => $limit,
            ]
        );
    }
}
