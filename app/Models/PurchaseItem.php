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
        'buy_price',
        'expiry_date',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
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
}
