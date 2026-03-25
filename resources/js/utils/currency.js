/**
 * Indonesian Currency Format Utility
 * 
 * Formats numbers according to Indonesian locale:
 * - Thousand separator: dot (.)
 * - Decimal separator: comma (,)
 * - Examples: 1.000, 10.000, 1.000.000
 */

/**
 * Format number to Indonesian Rupiah
 * @param {number} amount - The amount to format
 * @param {boolean} showSymbol - Whether to show "Rp" symbol
 * @returns {string} Formatted currency string
 */
export function formatCurrency(amount, showSymbol = true) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        amount = 0;
    }
    
    const formatted = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
    
    return showSymbol ? `Rp ${formatted}` : formatted;
}

/**
 * Format number to Indonesian Rupiah with decimals
 * @param {number} amount - The amount to format
 * @returns {string} Formatted currency string with decimals
 */
export function formatCurrencyWithDecimals(amount) {
    if (amount === null || amount === undefined || isNaN(amount)) {
        amount = 0;
    }
    
    const formatted = new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount);
    
    return `Rp ${formatted}`;
}

/**
 * Parse Indonesian Rupiah string to number
 * @param {string} currencyString - The currency string (e.g., "Rp 1.000.000")
 * @returns {number} Parsed number
 */
export function parseCurrency(currencyString) {
    if (!currencyString) return 0;
    
    // Remove "Rp" and all dots (thousand separators)
    const cleaned = currencyString
        .replace(/Rp\s?/gi, '')
        .replace(/\./g, '')
        .replace(/,/g, '.');
    
    return parseFloat(cleaned) || 0;
}

/**
 * Format number with thousand separator only (no Rp symbol)
 * @param {number} number - The number to format
 * @returns {string} Formatted number string
 */
export function formatNumber(number) {
    if (number === null || number === undefined || isNaN(number)) {
        number = 0;
    }
    
    return new Intl.NumberFormat('id-ID').format(number);
}

/**
 * Format compact number (e.g., 1.5K, 2.3M)
 * @param {number} number - The number to format
 * @returns {string} Formatted compact string
 */
export function formatCompact(number) {
    if (number === null || number === undefined || isNaN(number)) {
        number = 0;
    }
    
    return new Intl.NumberFormat('id-ID', {
        notation: 'compact',
        compactDisplay: 'short',
        maximumFractionDigits: 1,
    }).format(number);
}

/**
 * Default export with all functions
 */
export default {
    formatCurrency,
    formatCurrencyWithDecimals,
    parseCurrency,
    formatNumber,
    formatCompact,
};
