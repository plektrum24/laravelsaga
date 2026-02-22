<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportExecutionLog extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'report_id',
        'status',
        'records_processed',
        'error_message',
        'file_path',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(AutomatedReport::class, 'report_id');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
