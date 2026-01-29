<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'valid_until' => 'date',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function hasExpired()
    {
        return $this->valid_until && \Carbon\Carbon::parse($this->valid_until)->endOfDay()->isPast();
    }
}
