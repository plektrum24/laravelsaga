<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebCart extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'session_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get cart items
     */
    public function items(): HasMany
    {
        return $this->hasMany(WebCartItem::class);
    }

    /**
     * Get cart total
     */
    public function getTotalAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->qty * $item->price;
        });
    }

    /**
     * Get item count
     */
    public function getItemCountAttribute(): int
    {
        return $this->items->sum('qty');
    }

    /**
     * Add item to cart
     */
    public function addItem($productId, $qty = 1, $price = null): WebCartItem
    {
        $item = WebCartItem::where('cart_id', $this->id)
            ->where('product_id', $productId)
            ->first();

        if ($item) {
            $item->qty += $qty;
            $item->save();
            return $item;
        }

        $product = Product::find($productId);
        
        return WebCartItem::create([
            'cart_id' => $this->id,
            'product_id' => $productId,
            'qty' => $qty,
            'price' => $price ?? ($product ? $product->sell_price : 0),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem($productId): void
    {
        WebCartItem::where('cart_id', $this->id)
            ->where('product_id', $productId)
            ->delete();
    }

    /**
     * Clear cart
     */
    public function clear(): void
    {
        $this->items()->delete();
    }

    /**
     * Get or create cart for customer/session
     */
    public static function getOrCreate($tenantId, $customerId = null, $sessionId = null): self
    {
        $query = static::where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($customerId) {
            $cart = $query->where('customer_id', $customerId)->first();
        } elseif ($sessionId) {
            $cart = $query->where('session_id', $sessionId)->first();
        } else {
            $cart = null;
        }

        if (!$cart) {
            $cart = static::create([
                'tenant_id' => $tenantId,
                'customer_id' => $customerId,
                'session_id' => $sessionId,
                'is_active' => true,
            ]);
        }

        return $cart;
    }

    /**
     * Scope for active carts
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
