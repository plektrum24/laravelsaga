<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSegment extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'segment_type',
        'segment_value',
        'score',
        'metadata',
        'calculated_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'calculated_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeType($query, $type)
    {
        return $query->where('segment_type', $type);
    }

    public function scopeHighValue($query)
    {
        return $query->where('score', '>=', 80);
    }

    public function scopeLowValue($query)
    {
        return $query->where('score', '<', 50);
    }
}
