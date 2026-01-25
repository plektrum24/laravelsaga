<?php

namespace App\Modules\Retail\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;
use App\Models\User;

class Product extends Model
{
    protected $guarded = ['id'];

    // Explicitly casting tenant_id if needed, but not required.

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class);
    }
}
