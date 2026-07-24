/** Sentinel sent to the API when the user removes a cover. */
export const COVER_CLEAR = '__clear__';

/**
 * @param {unknown} value
 * @returns {value is string}
 */
export function isCssCover(value) {
    return typeof value === 'string'
        && (value.startsWith('color:') || value.startsWith('gradient:'));
}

/**
 * @param {unknown} value
 * @returns {string|null}
 */
export function cssCoverBackground(value) {
    if (! isCssCover(value)) {
        return null;
    }

    return value.replace(/^(color|gradient):/, '');
}

/**
 * @param {'color'|'gradient'} type
 * @param {string} css
 */
export function encodeCssCover(type, css) {
    return `${type}:${css}`;
}

/** Solid color swatches for the Gallery tab. */
export const COVER_COLORS = [
    { id: 'rose', value: '#e11d48' },
    { id: 'red', value: '#dc2626' },
    { id: 'orange', value: '#ea580c' },
    { id: 'amber', value: '#f59e0b' },
    { id: 'yellow', value: '#eab308' },
    { id: 'lime', value: '#84cc16' },
    { id: 'green', value: '#22c55e' },
    { id: 'emerald', value: '#10b981' },
    { id: 'teal', value: '#14b8a6' },
    { id: 'cyan', value: '#06b6d4' },
    { id: 'sky', value: '#0ea5e9' },
    { id: 'blue', value: '#3b82f6' },
    { id: 'indigo', value: '#6366f1' },
    { id: 'violet', value: '#8b5cf6' },
    { id: 'purple', value: '#a855f7' },
    { id: 'fuchsia', value: '#d946ef' },
    { id: 'pink', value: '#ec4899' },
    { id: 'cream', value: '#f5f0e8' },
    { id: 'stone', value: '#a8a29e' },
    { id: 'slate', value: '#64748b' },
    { id: 'zinc', value: '#3f3f46' },
    { id: 'night', value: '#0f172a' },
];

/** Gradient swatches for the Gallery tab. */
export const COVER_GRADIENTS = [
    { id: 'teal-blue', value: 'linear-gradient(135deg, #0d9488 0%, #2563eb 100%)' },
    { id: 'pink-magenta', value: 'linear-gradient(135deg, #f472b6 0%, #c026d3 100%)' },
    { id: 'orange-red', value: 'linear-gradient(135deg, #fb923c 0%, #dc2626 100%)' },
    { id: 'indigo-violet', value: 'linear-gradient(135deg, #6366f1 0%, #a855f7 100%)' },
    { id: 'emerald-lime', value: 'linear-gradient(135deg, #059669 0%, #84cc16 100%)' },
    { id: 'slate-night', value: 'linear-gradient(135deg, #334155 0%, #0f172a 100%)' },
    { id: 'sky-indigo', value: 'linear-gradient(135deg, #38bdf8 0%, #4f46e5 100%)' },
    { id: 'rose-orange', value: 'linear-gradient(135deg, #fb7185 0%, #f97316 100%)' },
    { id: 'amber-rose', value: 'linear-gradient(135deg, #fbbf24 0%, #f43f5e 100%)' },
    { id: 'cyan-emerald', value: 'linear-gradient(135deg, #22d3ee 0%, #059669 100%)' },
    { id: 'violet-fuchsia', value: 'linear-gradient(135deg, #8b5cf6 0%, #db2777 100%)' },
    { id: 'blue-cyan', value: 'linear-gradient(135deg, #2563eb 0%, #06b6d4 100%)' },
    { id: 'warm-sunset', value: 'linear-gradient(135deg, #f97316 0%, #db2777 50%, #7c3aed 100%)' },
    { id: 'cool-aurora', value: 'linear-gradient(135deg, #22d3ee 0%, #818cf8 50%, #c084fc 100%)' },
    { id: 'forest', value: 'linear-gradient(135deg, #14532d 0%, #166534 50%, #4d7c0f 100%)' },
    { id: 'ocean', value: 'linear-gradient(180deg, #0c4a6e 0%, #0369a1 50%, #22d3ee 100%)' },
    { id: 'midnight', value: 'linear-gradient(135deg, #020617 0%, #1e3a8a 50%, #4c1d95 100%)' },
    { id: 'peach', value: 'linear-gradient(135deg, #ffedd5 0%, #fdba74 50%, #fb7185 100%)' },
];
