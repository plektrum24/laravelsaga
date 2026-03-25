<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ForecastTargetItem extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'forecast_target_id',
        'product_id',
        'recommended_qty',
        'unit_cost',
        'total_cost',
        'expected_revenue',
        'expected_profit',
        'priority',
    ];

    protected $casts = [
        'recommended_qty' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'expected_revenue' => 'decimal:2',
        'expected_profit' => 'decimal:2',
        'priority' => 'integer',
    ];

    public function forecastTarget()
    {
        return $this->belongsTo(ForecastTarget::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get profit margin
     */
    public function getProfitMarginAttribute()
    {
        if ($this->expected_revenue == 0) {
            return 0;
        }
        return round(($this->expected_profit / $this->expected_revenue) * 100, 2);
    }
}
