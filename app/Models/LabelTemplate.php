<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabelTemplate extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'tenant_id',
        'name',
        'template_type',
        'width_mm',
        'height_mm',
        'layout_json',
        'is_default',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'width_mm' => 'decimal:2',
        'height_mm' => 'decimal:2',
        'layout_json' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the creator
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get print jobs for this template
     */
    public function printJobs(): HasMany
    {
        return $this->hasMany(PrintJob::class);
    }

    /**
     * Scope for active templates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for default template
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Scope for template type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('template_type', $type);
    }

    /**
     * Get layout field
     */
    public function getLayoutAttribute($value)
    {
        return json_decode($this->layout_json, true);
    }

    /**
     * Set layout field
     */
    public function setLayoutAttribute($value)
    {
        $this->layout_json = json_encode($value);
    }

    /**
     * Get default template for type
     */
    public static function getDefaultForType($tenantId, $type)
    {
        return static::where('tenant_id', $tenantId)
            ->where('template_type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Create default templates for tenant
     */
    public static function createDefaults($tenantId, $createdBy = null)
    {
        $templates = [
            [
                'name' => 'Price Tag (Standard)',
                'template_type' => 'price_tag',
                'width_mm' => 50,
                'height_mm' => 30,
                'layout_json' => json_encode([
                    'fields' => [
                        ['type' => 'product_name', 'x' => 5, 'y' => 5, 'width' => 40, 'height' => 10, 'font_size' => 12, 'bold' => true],
                        ['type' => 'price', 'x' => 5, 'y' => 17, 'width' => 40, 'height' => 10, 'font_size' => 16, 'bold' => true, 'color' => '#d32f2f'],
                        ['type' => 'barcode', 'x' => 5, 'y' => 20, 'width' => 40, 'height' => 8, 'font_size' => 8],
                    ],
                    'show_logo' => false,
                    'show_sku' => false,
                ]),
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Barcode Label (Small)',
                'template_type' => 'barcode_label',
                'width_mm' => 38,
                'height_mm' => 21,
                'layout_json' => json_encode([
                    'fields' => [
                        ['type' => 'barcode', 'x' => 2, 'y' => 2, 'width' => 34, 'height' => 12, 'font_size' => 8],
                        ['type' => 'barcode_number', 'x' => 2, 'y' => 15, 'width' => 34, 'height' => 4, 'font_size' => 8],
                    ],
                    'show_product_name' => false,
                ]),
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Shelf Label',
                'template_type' => 'shelf_label',
                'width_mm' => 80,
                'height_mm' => 40,
                'layout_json' => json_encode([
                    'fields' => [
                        ['type' => 'product_name', 'x' => 5, 'y' => 5, 'width' => 70, 'height' => 12, 'font_size' => 14, 'bold' => true],
                        ['type' => 'sku', 'x' => 5, 'y' => 18, 'width' => 30, 'height' => 6, 'font_size' => 10],
                        ['type' => 'price', 'x' => 5, 'y' => 26, 'width' => 70, 'height' => 12, 'font_size' => 20, 'bold' => true, 'color' => '#d32f2f'],
                    ],
                    'show_category' => true,
                ]),
                'is_default' => true,
                'is_active' => true,
            ],
        ];

        foreach ($templates as $templateData) {
            static::create([
                'tenant_id' => $tenantId,
                'created_by' => $createdBy,
                ...$templateData,
            ]);
        }
    }
}
