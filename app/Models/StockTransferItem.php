<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockTransferItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'transfer_id',
        'product_id',
        'unit_id',
        'qty_requested',
        'qty_approved',
        'qty_shipped',
        'qty_received',
        'qty_discrepancy',
        'notes',
    ];

    protected $casts = [
        'qty_requested' => 'decimal:4',
        'qty_approved' => 'decimal:4',
        'qty_shipped' => 'decimal:4',
        'qty_received' => 'decimal:4',
        'qty_discrepancy' => 'decimal:4',
    ];

    /**
     * Get the transfer
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(StockTransfer::class);
    }

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Calculate discrepancy
     */
    public function calculateDiscrepancy(): void
    {
        $this->qty_discrepancy = $this->qty_shipped - $this->qty_received;
        $this->save();
    }

    /**
     * Check if item has discrepancy
     */
    public function hasDiscrepancy(): bool
    {
        return $this->qty_discrepancy > 0;
    }
}
