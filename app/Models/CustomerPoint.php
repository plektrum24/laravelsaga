<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomerPoint extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'customer_id',
        'tenant_id',
        'points',
        'type',
        'reference_type',
        'reference_id',
        'expiry_date',
        'balance_after',
        'notes',
    ];

    protected $casts = [
        'points' => 'decimal:2',
        'expiry_date' => 'datetime',
        'balance_after' => 'decimal:2',
    ];

    // Point transaction types
    const TYPE_EARN = 'earn';
    const TYPE_REDEEM = 'redeem';
    const TYPE_ADJUST = 'adjust';
    const TYPE_EXPIRE = 'expire';
    const TYPE_REFUND = 'refund';

    /**
     * Get the customer that owns this point record
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the reference model (transaction, adjustment, etc.)
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope for earned points
     */
    public function scopeEarned($query)
    {
        return $query->where('type', self::TYPE_EARN);
    }

    /**
     * Scope for redeemed points
     */
    public function scopeRedeemed($query)
    {
        return $query->where('type', self::TYPE_REDEEM);
    }

    /**
     * Scope for expired points
     */
    public function scopeExpired($query)
    {
        return $query->where('type', self::TYPE_EXPIRE);
    }

    /**
     * Scope for non-expired points
     */
    public function scopeActive($query)
    {
        return $query->where('type', '!=', self::TYPE_EXPIRE)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>', now());
            });
    }

    /**
     * Scope for expiring soon (within X days)
     */
    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('type', self::TYPE_EARN)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    /**
     * Calculate customer's current balance
     */
    public static function calculateBalance($customerId): float
    {
        $earned = static::where('customer_id', $customerId)
            ->where('type', self::TYPE_EARN)
            ->sum('points');
        
        $redeemed = static::where('customer_id', $customerId)
            ->where('type', self::TYPE_REDEEM)
            ->sum('points');
        
        $expired = static::where('customer_id', $customerId)
            ->where('type', self::TYPE_EXPIRE)
            ->sum('points');
        
        return $earned - $redeemed - $expired;
    }

    /**
     * Get balance with expiry breakdown
     */
    public static function getBalanceWithBreakdown($customerId): array
    {
        $earned = static::where('customer_id', $customerId)
            ->where('type', self::TYPE_EARN)
            ->sum('points');
        
        $redeemed = static::where('customer_id', $customerId)
            ->where('type', self::TYPE_REDEEM)
            ->sum('points');
        
        $expired = static::where('customer_id', $customerId)
            ->where('type', self::TYPE_EXPIRE)
            ->sum('points');
        
        $expiringSoon = static::where('customer_id', $customerId)
            ->where('type', self::TYPE_EARN)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays(30)])
            ->sum('points');
        
        $balance = $earned - $redeemed - $expired;
        
        return [
            'balance' => $balance,
            'total_earned' => $earned,
            'total_redeemed' => $redeemed,
            'total_expired' => $expired,
            'expiring_soon' => $expiringSoon,
        ];
    }
}
