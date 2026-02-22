<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'phone',
        'owner_name',
        'business_type',
        'subscription_plan',
        'domain',
        'database_name',
        'status',
        'is_active',
        'valid_until',
        'subscription_id',
        'subscription_status',
        'trial_ends_at',
        'subscription_expires_at',
        'auto_renew',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_until' => 'date',
        'trial_ends_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
        'auto_renew' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class);
    }

    public function currentSubscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class)->where('status', 'active');
    }

    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(Invoice::class, TenantSubscription::class);
    }

    public function usage(): HasMany
    {
        return $this->hasMany(TenantUsage::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(TenantPaymentMethod::class);
    }

    public function supportTickets(): HasMany
    {
        return $this->hasMany(SupportTicket::class);
    }

    public function hasExpired(): bool
    {
        return $this->valid_until && \Carbon\Carbon::parse($this->valid_until)->endOfDay()->isPast();
    }

    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial' && 
               $this->trial_ends_at && 
               $this->trial_ends_at->isFuture();
    }

    public function isActive(): bool
    {
        return $this->is_active && 
               in_array($this->subscription_status, ['active', 'trial']);
    }

    public function isSuspended(): bool
    {
        return $this->subscription_status === 'suspended';
    }

    public function canAccessFeature($feature): bool
    {
        if (!$this->subscription) {
            return false;
        }
        
        return $this->subscription->hasFeature($feature);
    }

    public function getUsageLimit($metric): int
    {
        if (!$this->subscription) {
            return 0;
        }
        
        return $this->subscription->getLimit($metric, 0);
    }

    public function isUnlimited($metric): bool
    {
        if (!$this->subscription) {
            return false;
        }
        
        return $this->subscription->isUnlimited($metric);
    }
}
