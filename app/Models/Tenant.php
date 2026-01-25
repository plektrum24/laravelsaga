<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'owner_name',
        'business_type',
        'subscription_plan',
        'domain',
        'database_name',
        'is_active'
    ];

    // Auto-dispatch event when created
    protected $dispatchesEvents = [
        'created' => \App\Events\TenantCreated::class,
    ];
}
