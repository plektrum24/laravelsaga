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
    ];

    protected $casts = [
        'track_stock' => 'boolean',
        'is_active' => 'boolean',
        'stock' => 'decimal:4',
        'buy_price' => 'decimal:2',
        'sell_price' => 'decimal:2',
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
}
