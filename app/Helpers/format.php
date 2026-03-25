/**
 * Indonesian Currency Format Utility
 * 
 * Format numbers according to Indonesian locale (id-ID)
 * Uses dot (.) as thousand separator and comma (,) as decimal separator
 * 
 * Usage:
 * - In Blade: {{ formatIDR($amount) }}
 * - In JS: formatIDR(amount)
 * 
 * Examples:
 * - 1000 → 1.000
 * - 10000 → 10.000
 * - 1000000 → 1.000.000
 * - 1000.50 → 1.000,50
 */

if (!function_exists('formatIDR')) {
    function formatIDR($amount, $decimals = 0, $prefix = 'Rp ', $suffix = '')
    {
        if ($amount === null || $amount === '') {
            return $prefix . '0' . $suffix;
        }

        // Format with Indonesian locale
        $formatted = number_format(
            (float) $amount,
            $decimals,
            ',',  // Decimal separator
            '.'   // Thousand separator
        );

        return $prefix . $formatted . $suffix;
    }
}

if (!function_exists('formatNumber')) {
    function formatNumber($number, $decimals = 0)
    {
        return number_format(
            (float) $number,
            $decimals,
            ',',  // Decimal separator
            '.'   // Thousand separator
        );
    }
}
