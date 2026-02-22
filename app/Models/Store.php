<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'name',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'latitude',
        'longitude',
        'phone',
        'email',
        'opening_hours',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'opening_hours' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Scope for active stores
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Calculate distance from coordinates
     */
    public function getDistanceFromAttribute($latitude, $longitude)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371; // km

        $latFrom = deg2rad($latitude);
        $lonFrom = deg2rad($longitude);
        $latTo = deg2rad($this->latitude);
        $lonTo = deg2rad($this->longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }

    /**
     * Scope for stores near coordinates
     */
    public function scopeNear($query, $latitude, $longitude, $radius = 10)
    {
        // Simple bounding box query (for better performance)
        $latRange = $radius / 111.045; // 1 degree ≈ 111.045 km
        $lonRange = $radius / (111.045 * cos(deg2rad($latitude)));

        return $query->whereBetween('latitude', [$latitude - $latRange, $latitude + $latRange])
            ->whereBetween('longitude', [$longitude - $lonRange, $longitude + $lonRange]);
    }
}
