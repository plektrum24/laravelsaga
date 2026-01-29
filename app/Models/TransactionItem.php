<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    // MultiTenantable via Transaction parent relationship primarily, but can add if table has tenant_id. 
    // Migration "add_tenant_id_to_all_tables" included 'transaction_items'? 
    // Let's check the migration content again. It listed 'branches', 'categories', 'units', 'suppliers', 'customers', 'products', 'inventory_movements'. 
    // It DID NOT list 'transaction_items'. So NO MultiTenantable trait here intentionally as it relies on parent.

    protected $fillable = [
        'transaction_id',
        'product_id',
        'unit_id',
        'qty',
        'price',
        'cogs',
        'subtotal',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'price' => 'decimal:2',
        'cogs' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
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
