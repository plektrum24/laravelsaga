<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastTarget extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'target_revenue',
        'target_duration_days',
        'current_trajectory',
        'gap',
        'status',
        'generated_at',
        'achieved_at',
    ];

    protected $casts = [
        'target_revenue' => 'decimal:2',
        'current_trajectory' => 'decimal:2',
        'gap' => 'decimal:2',
        'target_duration_days' => 'integer',
        'generated_at' => 'datetime',
        'achieved_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ForecastTargetItem::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get progress percentage
     */
    public function getProgressAttribute()
    {
        if ($this->target_revenue == 0) {
            return 0;
        }
        return round(($this->current_trajectory / $this->target_revenue) * 100, 2);
    }

    /**
     * Check if on track
     */
    public function getOnTrackAttribute()
    {
        $elapsedDays = now()->diffInDays($this->generated_at, false);
        if ($elapsedDays < 0 || $this->target_duration_days <= 0) {
            return false;
        }

        $expectedProgress = ($elapsedDays / $this->target_duration_days) * 100;
        return $this->progress >= $expectedProgress;
    }

    /**
     * Scope for active targets
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
