<?php

namespace App\Modules\Retail\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    protected $fillable = ['product_id', 'unit_id', 'conversion_qty', 'buy_price', 'sell_price', 'weight', 'is_base_unit'];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }
}
