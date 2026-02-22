<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'subscription_id',
        'invoice_number',
        'amount',
        'tax',
        'discount',
        'total',
        'status',
        'due_date',
        'paid_at',
        'payment_method',
        'payment_gateway_id',
        'payment_gateway_response',
        'pdf_path',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'payment_gateway_response' => 'array',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';
    const STATUS_PAID = 'paid';
    const STATUS_OVERDUE = 'overdue';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (!$invoice->invoice_number) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    /**
     * Get tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get subscription
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(TenantSubscription::class, 'subscription_id');
    }

    /**
     * Scope for unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', [self::STATUS_DRAFT, self::STATUS_SENT, self::STATUS_OVERDUE]);
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_OVERDUE)
                     ->orWhere(function ($q) {
                         $q->where('status', self::STATUS_SENT)
                           ->where('due_date', '<', Carbon::now());
                     });
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    /**
     * Scope for tenant
     */
    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(): string
    {
        $date = Carbon::now()->format('Ymd');
        $random = strtoupper(substr(uniqid(), -6));

        return "INV-{$date}-{$random}";
    }

    /**
     * Check if invoice is paid
     */
    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->status === self::STATUS_OVERDUE ||
               ($this->due_date && $this->due_date->isPast() && !$this->isPaid());
    }

    /**
     * Check if invoice is draft
     */
    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
        ]);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(?string $paymentMethod = null, ?string $gatewayId = null): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => Carbon::now(),
            'payment_method' => $paymentMethod,
            'payment_gateway_id' => $gatewayId,
        ]);
    }

    /**
     * Mark invoice as overdue
     */
    public function markAsOverdue(): void
    {
        $this->update([
            'status' => self::STATUS_OVERDUE,
        ]);
    }

    /**
     * Cancel invoice
     */
    public function cancel(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    /**
     * Get formatted tax
     */
    public function getFormattedTaxAttribute(): string
    {
        return 'Rp ' . number_format($this->tax, 0, ',', '.');
    }

    /**
     * Get formatted discount
     */
    public function getFormattedDiscountAttribute(): string
    {
        return 'Rp ' . number_format($this->discount, 0, ',', '.');
    }

    /**
     * Get formatted total
     */
    public function getFormattedTotalAttribute(): string
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }

    /**
     * Get days until due
     */
    public function getDaysUntilDueAttribute(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($this->due_date, false));
    }

    /**
     * Get days overdue
     */
    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->isOverdue() || !$this->due_date) {
            return null;
        }

        return max(0, Carbon::now()->diffInDays($this->due_date, false));
    }

    /**
     * Recalculate total
     */
    public function recalculateTotal(): void
    {
        $this->total = $this->amount + $this->tax - $this->discount;
        $this->save();
    }
}
