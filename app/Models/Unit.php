<?php

namespace App\Models;

use App\Traits\MultiTenantable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, MultiTenantable;

    protected $fillable = [
        'tenant_id',
        'name',
        'abbreviation',
    ];

    public function productUnits()
    {
        return $this->hasMany(ProductUnit::class);
    }
}
