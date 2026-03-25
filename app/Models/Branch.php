<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'province',
        'postal_code',
        'phone',
        'email',
        'is_main',
        'status',
        'manager_name',
        'manager_phone',
    ];

    protected $casts = [
        'is_main' => 'boolean',
    ];

    /**
     * Get all users for the branch
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all transactions for the branch
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all employees for the branch
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Check if branch is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if branch is main branch
     */
    public function isMain(): bool
    {
        return $this->is_main;
    }

    /**
     * Get full address formatted
     */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->province,
            $this->postal_code
        ]);
        
        return implode(', ', $parts) ?: '-';
    }

    /**
     * Get manager info formatted
     */
    public function getManagerInfoAttribute(): string
    {
        if ($this->manager_name && $this->manager_phone) {
            return "{$this->manager_name} ({$this->manager_phone})";
        }
        
        return $this->manager_name ?: '-';
    }
}
