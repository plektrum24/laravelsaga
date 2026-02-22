<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockTransfer extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'transfer_number',
        'from_branch_id',
        'to_branch_id',
        'requested_by',
        'approved_by',
        'shipped_by',
        'received_by',
        'status',
        'request_date',
        'approval_date',
        'shipped_date',
        'received_date',
        'notes',
        'total_items',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'approval_date' => 'datetime',
        'shipped_date' => 'datetime',
        'received_date' => 'datetime',
        'total_items' => 'integer',
    ];

    // Status constants
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_APPROVAL = 'pending_approval';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_RECEIVED = 'received';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the source branch
     */
    public function fromBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    /**
     * Get the destination branch
     */
    public function toBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    /**
     * Get the user who requested the transfer
     */
    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * Get the user who approved the transfer
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who shipped the transfer
     */
    public function shippedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipped_by');
    }

    /**
     * Get the user who received the transfer
     */
    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get transfer items
     */
    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItem::class);
    }

    /**
     * Generate transfer number
     */
    public static function generateTransferNumber(): string
    {
        $date = date('Ymd');
        $random = mt_rand(1000, 9999);
        return "TO-{$date}-{$random}";
    }

    /**
     * Check if transfer can be submitted
     */
    public function canSubmit(): bool
    {
        return $this->status === self::STATUS_DRAFT && $this->items()->count() > 0;
    }

    /**
     * Check if transfer can be approved
     */
    public function canApprove(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Check if transfer can be shipped
     */
    public function canShip(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if transfer can be received
     */
    public function canReceive(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    /**
     * Check if transfer can be cancelled
     */
    public function canCancel(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING_APPROVAL,
            self::STATUS_APPROVED,
        ]);
    }

    /**
     * Submit transfer for approval
     */
    public function submit(): bool
    {
        if (!$this->canSubmit()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_PENDING_APPROVAL,
            'request_date' => now(),
        ]);

        return true;
    }

    /**
     * Approve transfer
     */
    public function approve($userId): bool
    {
        if (!$this->canApprove()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approval_date' => now(),
        ]);

        return true;
    }

    /**
     * Reject transfer
     */
    public function reject(): bool
    {
        if ($this->status !== self::STATUS_PENDING_APPROVAL) {
            return false;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);

        return true;
    }

    /**
     * Ship transfer
     */
    public function ship($userId): bool
    {
        if (!$this->canShip()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_IN_TRANSIT,
            'shipped_by' => $userId,
            'shipped_date' => now(),
        ]);

        return true;
    }

    /**
     * Receive transfer
     */
    public function receive($userId): bool
    {
        if (!$this->canReceive()) {
            return false;
        }

        $this->update([
            'status' => self::STATUS_RECEIVED,
            'received_by' => $userId,
            'received_date' => now(),
        ]);

        return true;
    }

    /**
     * Cancel transfer
     */
    public function cancel(): bool
    {
        if (!$this->canCancel()) {
            return false;
        }

        $this->update(['status' => self::STATUS_CANCELLED]);

        return true;
    }

    /**
     * Update total items count
     */
    public function updateTotalItems(): void
    {
        $this->update([
            'total_items' => $this->items()->count(),
        ]);
    }
}
