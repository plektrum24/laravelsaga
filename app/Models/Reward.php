<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'reward_catalog';

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'points_cost',
        'stock',
        'image_url',
        'terms_conditions',
        'active_from',
        'active_to',
        'status',
    ];

    protected $casts = [
        'points_cost' => 'integer',
        'stock' => 'integer',
        'active_from' => 'datetime',
        'active_to' => 'datetime',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Get customer rewards for this reward
     */
    public function customerRewards(): HasMany
    {
        return $this->hasMany(CustomerReward::class, 'reward_id');
    }

    /**
     * Get tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for active rewards
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->where(function ($q) {
                $q->whereNull('active_from')
                  ->orWhere('active_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('active_to')
                  ->orWhere('active_to', '>=', now());
            });
    }

    /**
     * Scope for available rewards (in stock)
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('stock')
              ->orWhere('stock', '>', 0);
        });
    }

    /**
     * Check if reward is available
     */
    public function isAvailable(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }
        
        if ($this->active_from && $this->active_from->isFuture()) {
            return false;
        }
        
        if ($this->active_to && $this->active_to->isPast()) {
            return false;
        }
        
        if ($this->stock !== null && $this->stock <= 0) {
            return false;
        }
        
        return true;
    }

    /**
     * Decrement stock
     */
    public function decrementStock(): void
    {
        if ($this->stock !== null) {
            $this->decrement('stock', 1);
        }
    }

    /**
     * Increment stock
     */
    public function incrementStock(): void
    {
        if ($this->stock !== null) {
            $this->increment('stock', 1);
        }
    }
}
