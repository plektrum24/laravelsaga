<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'unit_id',
        'qty',
        'current_stock', // Added for batch tracking
        'buy_price',
        'expiry_date',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'current_stock' => 'decimal:4',
        'buy_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'expiry_date' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    // ========================
    // Batch Tracking Helpers
    // ========================

    /**
     * Check if this batch is expired
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Check if this batch is expiring soon (within X days)
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiry_date) {
            return false;
        }
        return $this->expiry_date->isBetween(now(), now()->addDays($days));
    }

    /**
     * Deduct stock from this batch
     */
    public function deductStock(float $qty): void
    {
        $this->decrement('current_stock', $qty);
    }

    /**
     * Check if batch has available stock
     */
    public function hasStock(): bool
    {
        return $this->current_stock > 0;
    }

    /**
     * Scope: Only active batches with stock
     */
    public function scopeActive($query)
    {
        return $query->where('current_stock', '>', 0);
    }

    /**
     * Scope: Order by nearest expiry first (FEFO)
     */
    public function scopeFefo($query)
    {
        return $query->orderBy('expiry_date', 'asc');
    }
}
