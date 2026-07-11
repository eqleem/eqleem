const CURRENCY_SUFFIX = /[\s\u00A0]*(ر\.?\s*س\.?|SAR|R\.?\s?S)\s*$/iu;

/**
 * Strip trailing currency markers from API-formatted money strings.
 */
export function stripCurrencySuffix(value) {
    return String(value ?? '')
        .replace(/\u20C1/g, '')
        .replace(CURRENCY_SUFFIX, '')
        .trim();
}

/**
 * Format a decimal/major-unit amount for display (SAR).
 */
export function formatMoneyAmount(value, { maximumFractionDigits = 2 } = {}) {
    const number = Number(value);

    if (!Number.isFinite(number)) {
        return '0';
    }

    return new Intl.NumberFormat('ar-SA', { maximumFractionDigits }).format(number);
}

/**
 * Format minor units (halalas) as a display amount.
 */
export function formatMoneyMinor(minor, options = {}) {
    return formatMoneyAmount(Number(minor) / 100, options);
}

/**
 * @deprecated Prefer the <Money /> component for display.
 */
export function money(value) {
    return formatMoneyAmount(value || 0, { maximumFractionDigits: 0 });
}
