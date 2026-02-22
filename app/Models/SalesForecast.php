<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesForecast extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'branch_id',
        'category_id',
        'forecast_date',
        'predicted_sales',
        'actual_sales',
        'confidence_score',
        'model_version',
    ];

    protected $casts = [
        'forecast_date' => 'date',
        'predicted_sales' => 'decimal:2',
        'actual_sales' => 'decimal:2',
        'confidence_score' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeFuture($query)
    {
        return $query->where('forecast_date', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('forecast_date', '<=', now());
    }
}
