<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebOrderItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'order_id',
        'product_id',
        'qty',
        'price',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Get the order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(WebOrder::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calculate subtotal
     */
    public function calculateSubtotal(): void
    {
        $this->subtotal = $this->qty * $this->price;
        $this->save();
    }
}
