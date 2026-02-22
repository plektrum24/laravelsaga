<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MembershipTier extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'name',
        'min_spend',
        'min_visits',
        'benefits',
        'badge_color',
        'priority',
        'active',
    ];

    protected $casts = [
        'min_spend' => 'decimal:2',
        'min_visits' => 'integer',
        'benefits' => 'array',
        'priority' => 'integer',
        'active' => 'boolean',
    ];

    /**
     * Get customers in this tier
     */
    public function customerTiers(): HasMany
    {
        return $this->hasMany(CustomerTier::class);
    }

    /**
     * Scope for active tiers
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope ordered by priority
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Get tier by name
     */
    public static function getByName($name)
    {
        return static::where('name', $name)->first();
    }

    /**
     * Check if customer qualifies for this tier
     */
    public function customerQualifies($customer): bool
    {
        $totalSpend = $customer->transactions()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subYear())
            ->sum('grand_total');
        
        $totalVisits = $customer->transactions()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subYear())
            ->count();
        
        return $totalSpend >= $this->min_spend && $totalVisits >= $this->min_visits;
    }

    /**
     * Get benefit value
     */
    public function getBenefit($key, $default = null)
    {
        return $this->benefits[$key] ?? $default;
    }

    /**
     * Check if tier has benefit
     */
    public function hasBenefit($key): bool
    {
        return isset($this->benefits[$key]);
    }
}
