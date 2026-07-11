import ar from '../lang/ar.json';

/**
 * Resolve dashboard UI strings — server messages (English keys) or client fallbacks.
 */
export function t(message) {
    if (!message) {
        return '';
    }

    const key = String(message).trim();

    return ar[key] ?? key;
}
