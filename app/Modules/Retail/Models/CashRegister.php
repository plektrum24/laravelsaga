<?php

namespace App\Modules\Retail\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant;
use App\Models\Branch;

class CashRegister extends Model
{
    protected $guarded = ['id'];

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
