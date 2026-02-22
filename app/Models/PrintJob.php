<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrintJob extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'template_id',
        'product_ids',
        'quantity',
        'status',
        'printer_name',
        'created_by',
        'completed_at',
        'error_message',
    ];

    protected $casts = [
        'product_ids' => 'array',
        'quantity' => 'integer',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the template
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(LabelTemplate::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope for pending jobs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed jobs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed jobs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Mark job as completed
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark job as failed
     */
    public function markAsFailed($errorMessage)
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Get products for this job
     */
    public function getProducts()
    {
        return Product::whereIn('id', $this->product_ids)->get();
    }

    /**
     * Get product count
     */
    public function getProductCount()
    {
        return is_array($this->product_ids) ? count($this->product_ids) : 0;
    }
}
