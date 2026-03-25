<?php

if (! function_exists('rupiah')) {
    /**
     * Format number to Indonesian Rupiah
     * 
     * @param  float|int  $number
     * @param  bool  $includeSymbol
     * @param  int  $decimals
     * @return string
     */
    function rupiah($number, $includeSymbol = true, $decimals = 0)
    {
        if (is_null($number) || !is_numeric($number)) {
            $number = 0;
        }

        $formatted = number_format($number, $decimals, ',', '.');

        if ($includeSymbol) {
            return 'Rp ' . $formatted;
        }

        return $formatted;
    }
}

if (! function_exists('format_number')) {
    /**
     * Format number with Indonesian thousand separator
     * 
     * @param  float|int  $number
     * @return string
     */
    function format_number($number)
    {
        if (is_null($number) || !is_numeric($number)) {
            $number = 0;
        }

        return number_format($number, 0, ',', '.');
    }
}

if (! function_exists('parse_rupiah')) {
    /**
     * Parse Indonesian Rupiah string to number
     * 
     * @param  string  $string
     * @return float
     */
    function parse_rupiah($string)
    {
        if (is_null($string)) {
            return 0;
        }

        // Remove "Rp" and spaces
        $cleaned = preg_replace('/Rp\s?/i', '', $string);
        
        // Remove thousand separator (dot)
        $cleaned = str_replace('.', '', $cleaned);
        
        // Replace decimal separator (comma) with dot
        $cleaned = str_replace(',', '.', $cleaned);

        return (float) $cleaned;
    }
}

if (! function_exists('format_currency')) {
    /**
     * Format currency with automatic thousand separators
     * Supports multiple locales
     * 
     * @param  float|int  $number
     * @param  string  $locale
     * @param  string  $currency
     * @return string
     */
    function format_currency($number, $locale = 'id_ID', $currency = 'IDR')
    {
        if (is_null($number) || !is_numeric($number)) {
            $number = 0;
        }

        return new \NumberFormatter($locale, \NumberFormatter::CURRENCY)
            ->formatCurrency($number, $currency);
    }
}

if (! function_exists('format_date')) {
    /**
     * Format date to Indonesian format
     * 
     * @param  \DateTime|string  $date
     * @param  string  $format
     * @return string
     */
    function format_date($date, $format = 'd M Y')
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        $months = [
            1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
            'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
        ];

        if ($format === 'd M Y') {
            return $date->format('d') . ' ' . $months[(int) $date->format('n')] . ' ' . $date->format('Y');
        }

        return $date->format($format);
    }
}

if (! function_exists('format_datetime')) {
    /**
     * Format datetime to Indonesian format
     * 
     * @param  \DateTime|string  $datetime
     * @param  bool  $showSeconds
     * @return string
     */
    function format_datetime($datetime, $showSeconds = false)
    {
        if (is_string($datetime)) {
            $datetime = new \DateTime($datetime);
        }

        $format = $showSeconds ? 'd M Y H:i:s' : 'd M Y H:i';
        return format_date($datetime, $format);
    }
}

if (! function_exists('time_ago')) {
    /**
     * Convert datetime to time ago format
     * 
     * @param  \DateTime|string  $datetime
     * @return string
     */
    function time_ago($datetime)
    {
        if (is_string($datetime)) {
            $datetime = new \DateTime($datetime);
        }

        $now = new \DateTime();
        $diff = $now->diff($datetime);

        if ($diff->y > 0) {
            return $diff->y . ' tahun yang lalu';
        }

        if ($diff->m > 0) {
            return $diff->m . ' bulan yang lalu';
        }

        if ($diff->d > 0) {
            return $diff->d . ' hari yang lalu';
        }

        if ($diff->h > 0) {
            return $diff->h . ' jam yang lalu';
        }

        if ($diff->i > 0) {
            return $diff->i . ' menit yang lalu';
        }

        return 'Baru saja';
    }
}
