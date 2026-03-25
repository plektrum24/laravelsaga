<?php

namespace App\Utils;

/**
 * Currency Helper - Indonesian Format
 * 
 * Formats numbers according to Indonesian locale:
 * - 1000 -> 1.000
 * - 10000 -> 10.000
 * - 1000000 -> 1.000.000
 */
class Currency
{
    /**
     * Format number to Indonesian currency format
     * 
     * @param float|int $amount
     * @param string $symbol
     * @param bool $showDecimal
     * @return string
     */
    public static function format(float|int $amount, string $symbol = 'Rp ', bool $showDecimal = false): string
    {
        if ($showDecimal) {
            return $symbol . number_format($amount, 2, ',', '.');
        }
        
        return $symbol . number_format($amount, 0, ',', '.');
    }

    /**
     * Format number without symbol
     * 
     * @param float|int $amount
     * @return string
     */
    public static function formatNumber(float|int $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }

    /**
     * Parse Indonesian format string to number
     * 
     * @param string $formatted
     * @return float
     */
    public static function parse(string $formatted): float
    {
        // Remove currency symbol
        $clean = str_replace(['Rp', 'rp', 'RP', '.', ' '], '', $formatted);
        
        // Replace comma with dot for decimal
        $clean = str_replace(',', '.', $clean);
        
        return (float) $clean;
    }

    /**
     * Format to thousands
     * 
     * @param float|int $amount
     * @return string
     */
    public static function formatThousands(float|int $amount): string
    {
        return number_format($amount, 0, ',', '.');
    }

    /**
     * Format to millions
     * 
     * @param float|int $amount
     * @param int $decimals
     * @return string
     */
    public static function formatMillions(float|int $amount, int $decimals = 2): string
    {
        $millions = $amount / 1000000;
        return number_format($millions, $decimals, ',', '.') . ' Jt';
    }
}
