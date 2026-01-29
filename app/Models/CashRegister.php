<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;

    protected $connection = 'tenant';
    protected $fillable = [
        'tenant_id',
        'branch_id',
        'user_id',
        'opened_at',
        'closed_at',
        'start_cash',
        'end_cash',
        'total_cash_sales',
        'total_non_cash_sales',
        'total_expenses',
        'diff_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
        'start_cash' => 'decimal:2',
        'end_cash' => 'decimal:2',
        'total_cash_sales' => 'decimal:2',
        'total_non_cash_sales' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'diff_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function expenses()
    {
        return $this->hasMany(CashExpense::class);
    }
}
