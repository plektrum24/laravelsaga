<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'address',
    ];

    /**
     * Get customer's current tier
     */
    public function currentTier(): HasOne
    {
        return $this->hasOne(CustomerTier::class)->current();
    }

    /**
     * Get customer's tier history
     */
    public function tierHistory(): HasMany
    {
        return $this->hasMany(CustomerTier::class);
    }

    /**
     * Get customer's transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get customer's points ledger
     */
    public function points(): HasMany
    {
        return $this->hasMany(CustomerPoint::class);
    }

    /**
     * Calculate total spend in last 12 months
     */
    public function calculateLastYearSpend(): float
    {
        return $this->transactions()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subYear())
            ->sum('grand_total');
    }

    /**
     * Calculate total visits in last 12 months
     */
    public function calculateLastYearVisits(): int
    {
        return $this->transactions()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subYear())
            ->count();
    }

    /**
     * Check and update tier qualification
     */
    public function assessAndUpdateTier(): ?CustomerTier
    {
        $tenantId = $this->tenant_id;
        $totalSpend = $this->calculateLastYearSpend();
        $totalVisits = $this->calculateLastYearVisits();
        
        // Find highest tier customer qualifies for
        $qualifiedTier = MembershipTier::where('tenant_id', $tenantId)
            ->where('active', true)
            ->where('min_spend', '<=', $totalSpend)
            ->where('min_visits', '<=', $totalVisits)
            ->orderBy('priority', 'desc')
            ->first();
        
        if (!$qualifiedTier) {
            // Assign Bronze tier as default
            $qualifiedTier = MembershipTier::where('tenant_id', $tenantId)
                ->where('name', 'Bronze')
                ->where('active', true)
                ->first();
        }
        
        if (!$qualifiedTier) {
            return null;
        }
        
        $currentTier = $this->currentTier;
        
        // Check if tier changed
        if ($currentTier && $currentTier->tier_id === $qualifiedTier->id) {
            return $currentTier; // No change needed
        }
        
        // Create new tier assignment
        $newTier = CustomerTier::create([
            'customer_id' => $this->id,
            'tier_id' => $qualifiedTier->id,
            'qualified_at' => now(),
            'valid_until' => now()->addYear(),
            'previous_tier_id' => $currentTier?->tier_id,
        ]);
        
        return $newTier;
    }

    /**
     * Get tier benefits
     */
    public function getTierBenefits(): array
    {
        $tier = $this->currentTier;
        return $tier ? $tier->getBenefits() : [];
    }

    /**
     * Get specific tier benefit value
     */
    public function getTierBenefit($key, $default = null)
    {
        $benefits = $this->getTierBenefits();
        return $benefits[$key] ?? $default;
    }

    /**
     * Get points multiplier based on tier
     */
    public function getPointsMultiplier(): float
    {
        return $this->getTierBenefit('points_multiplier', 1.0);
    }

    /**
     * Get discount percent based on tier
     */
    public function getTierDiscountPercent(): float
    {
        return $this->getTierBenefit('discount_percent', 0.0);
    }

    /**
     * Get tier badge color
     */
    public function getTierBadgeColor(): string
    {
        return $this->currentTier?->tier->badge_color ?? '#6B7280';
    }

    /**
     * Get tier name
     */
    public function getTierName(): ?string
    {
        return $this->currentTier?->tier->name;
    }
}
