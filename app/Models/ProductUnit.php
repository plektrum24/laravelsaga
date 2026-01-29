<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ProductUnit extends Pivot
{
    // toggle trait
    use HasFactory;

    protected $connection = 'tenant';
    protected $table = 'product_units';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'unit_id',
        'conversion_qty',
        'buy_price',
        'sell_price',
        'is_base_unit',
        'weight',
    ];

    protected $casts = [
        'is_base_unit' => 'boolean',
        'conversion_qty' => 'decimal:4',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
