<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanAndGoItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'session_id',
        'product_id',
        'qty',
        'price',
    ];

    protected $casts = [
        'qty' => 'integer',
        'price' => 'decimal:2',
    ];

    public function session()
    {
        return $this->belongsTo(ScanAndGoSession::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getSubtotalAttribute()
    {
        return $this->qty * $this->price;
    }
}
