<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardWidget extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'dashboard_id',
        'widget_type',
        'title',
        'position',
        'width',
        'height',
        'config_json',
        'is_active',
    ];

    protected $casts = [
        'config_json' => 'array',
        'is_active' => 'boolean',
    ];

    public function dashboard()
    {
        return $this->belongsTo(AnalyticsDashboard::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
