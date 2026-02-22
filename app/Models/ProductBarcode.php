<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBarcode extends Model
{
    use HasFactory;

    protected $connection = 'tenant';

    protected $fillable = [
        'product_id',
        'barcode',
        'barcode_type',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    /**
     * Get the product
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Scope for primary barcode
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope for specific barcode type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('barcode_type', $type);
    }

    /**
     * Generate EAN-13 barcode
     */
    public static function generateEAN13($prefix = ''): string
    {
        $code = $prefix . str_pad(mt_rand(0, 9999999999), 12, '0', STR_PAD_LEFT);
        $code = substr($code, 0, 12);
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $code . $checkDigit;
    }

    /**
     * Generate UPC-A barcode
     */
    public static function generateUPCA($prefix = ''): string
    {
        $code = $prefix . str_pad(mt_rand(0, 999999999), 10, '0', STR_PAD_LEFT);
        $code = substr($code, 0, 10);
        
        // Calculate check digit
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int)$code[$i] * ($i % 2 === 0 ? 3 : 1);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return $code . $checkDigit;
    }

    /**
     * Generate Code-128 barcode
     */
    public static function generateCode128($length = 12): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $code;
    }

    /**
     * Validate EAN-13 barcode
     */
    public static function validateEAN13($barcode): bool
    {
        if (!is_numeric($barcode) || strlen($barcode) !== 13) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int)$barcode[$i] * ($i % 2 === 0 ? 1 : 3);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return (int)$barcode[12] === $checkDigit;
    }

    /**
     * Validate UPC-A barcode
     */
    public static function validateUPCA($barcode): bool
    {
        if (!is_numeric($barcode) || strlen($barcode) !== 12) {
            return false;
        }
        
        $sum = 0;
        for ($i = 0; $i < 11; $i++) {
            $sum += (int)$barcode[$i] * ($i % 2 === 0 ? 3 : 1);
        }
        $checkDigit = (10 - ($sum % 10)) % 10;
        
        return (int)$barcode[11] === $checkDigit;
    }
}
