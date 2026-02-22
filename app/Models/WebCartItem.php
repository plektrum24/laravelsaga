<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebCartItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'cart_id',
        'product_id',
        'qty',
        'price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the cart
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(WebCart::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get subtotal
     */
    public function getSubtotalAttribute(): float
    {
        return $this->qty * $this->price;
    }

    /**
     * Update quantity
     */
    public function updateQuantity($qty): void
    {
        if ($qty <= 0) {
            $this->delete();
        } else {
            $this->qty = $qty;
            $this->save();
        }
    }
}
