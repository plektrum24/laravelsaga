<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'user_id',
        'branch_id',
        'name',
        'nik',
        'role',
        'phone',
        'join_date',
        'basic_salary',
        'allowance',
        'transport_allowance',
        'meal_allowance',
        'position_allowance',
        'performance_bonus',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'is_active',
    ];

    protected $casts = [
        'join_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'transport_allowance' => 'decimal:2',
        'meal_allowance' => 'decimal:2',
        'position_allowance' => 'decimal:2',
        'performance_bonus' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Calculate net salary based on basic salary and all allowances.
     */
    public function getNetSalaryAttribute()
    {
        return $this->basic_salary +
            $this->allowance +
            $this->transport_allowance +
            $this->meal_allowance +
            $this->position_allowance +
            $this->performance_bonus;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }
}
