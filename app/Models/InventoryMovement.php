<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'branch_id',
        'user_id',
        'reference_number',
        'type', // in, out, adjustment, transfer
        'qty',
        'current_stock',
        'notes',
    ];

    protected $casts = [
        'qty' => 'decimal:4',
        'current_stock' => 'decimal:4',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
