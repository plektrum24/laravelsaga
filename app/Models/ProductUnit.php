<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;
    // MultiTenantable might not be needed if directly linked to Product which has tenant_id, but safer to check schema. 
    // Schema update added tenant_id to all tables, so yes, use trait.
    use \App\Traits\MultiTenantable;

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
