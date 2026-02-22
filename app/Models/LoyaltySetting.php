<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltySetting extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'earn_rate',
        'earn_currency',
        'point_value',
        'min_redemption_points',
        'max_redemption_percent',
        'points_expiry_months',
        'enabled',
    ];

    protected $casts = [
        'earn_rate' => 'decimal:2',
        'point_value' => 'decimal:4',
        'min_redemption_points' => 'integer',
        'max_redemption_percent' => 'decimal:2',
        'points_expiry_months' => 'integer',
        'enabled' => 'boolean',
    ];

    /**
     * Get settings for a specific tenant
     */
    public static function forTenant($tenantId): ?self
    {
        return static::where('tenant_id', $tenantId)->first();
    }

    /**
     * Get or create default settings for tenant
     */
    public static function getOrCreateForTenant($tenantId): self
    {
        $settings = static::where('tenant_id', $tenantId)->first();
        
        if (!$settings) {
            $settings = static::create([
                'tenant_id' => $tenantId,
                'earn_rate' => 10000,
                'earn_currency' => 'IDR',
                'point_value' => 100,
                'min_redemption_points' => 100,
                'max_redemption_percent' => 50.00,
                'points_expiry_months' => 12,
                'enabled' => true,
            ]);
        }
        
        return $settings;
    }

    /**
     * Calculate points for an amount
     */
    public function calculatePoints($amount): int
    {
        if (!$this->enabled) {
            return 0;
        }
        
        return (int) floor($amount / $this->earn_rate);
    }

    /**
     * Calculate value of points
     */
    public function calculateValue($points): float
    {
        return $points * $this->point_value;
    }
}
