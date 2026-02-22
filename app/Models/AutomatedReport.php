<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutomatedReport extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'name',
        'report_type',
        'schedule',
        'recipients',
        'filters',
        'format',
        'is_active',
        'last_run_at',
        'next_run_at',
    ];

    protected $casts = [
        'recipients' => 'array',
        'filters' => 'array',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function executionLogs()
    {
        return $this->hasMany(ReportExecutionLog::class, 'report_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDue($query)
    {
        return $query->where('next_run_at', '<=', now())
            ->orWhereNull('next_run_at');
    }
}
