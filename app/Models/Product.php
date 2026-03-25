<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory; // SoftDeletes if column exists, checking schema... migration didn't show softDeletes in snippet, sticking to base.

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'category_id',
        'sku',
        'barcode',
        'name',
        'description',
        'image_url',
        'stock',
        'min_stock',
        'buy_price',
        'sell_price',
        'track_stock',
        'is_active',
        'enable_tiered_pricing',
        'pricing_tier_config',
    ];

    protected $casts = [
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'stock' => 'decimal:4',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
        'enable_tiered_pricing' => 'boolean',
        'pricing_tier_config' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Helper to check if item is a service (Barber/Car Wash/Cafe Service)
    public function isService()
    {
        return !$this->track_stock;
    }

    /**
     * Get pricing tiers for this product
     */
    public function getPricingTiers()
    {
        if (!$this->enable_tiered_pricing) {
            return [];
        }

        return $this->pricing_tier_config['tiers'] ?? [];
    }

    /**
     * Get price for a specific quantity
     */
    public function getPriceForQuantity($qty)
    {
        if (!$this->enable_tiered_pricing) {
            return $this->sell_price;
        }

        $tiers = $this->getPricingTiers();
        if (empty($tiers)) {
            return $this->sell_price;
        }

        // Find applicable tier (highest min_qty that qty qualifies for)
        $applicableTier = collect($tiers)
            ->sortByDesc('min_qty')
            ->firstWhere('min_qty', '<=', $qty);

        return $applicableTier ? $applicableTier['price'] : $this->sell_price;
    }

    /**
     * Get pricing info for API response
     */
    public function getPricingInfo($qty = 1)
    {
        $unitPrice = $this->getPriceForQuantity($qty);
        $basePrice = $this->sell_price;

        return [
            'unit_price' => $unitPrice,
            'quantity' => $qty,
            'total' => $unitPrice * $qty,
            'base_total' => $basePrice * $qty,
            'savings' => ($basePrice - $unitPrice) * $qty,
            'discount_percent' => $basePrice > 0
                ? round((($basePrice - $unitPrice) / $basePrice) * 100, 1)
                : 0,
        ];
    }
}
