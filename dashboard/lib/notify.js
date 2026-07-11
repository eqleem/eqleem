import { t } from './i18n.js';
import { ApiError } from './api.js';

/**
 * Show a lightweight toast notification (see components/ui/Notify.vue).
 *
 * @param {{ text: string, type?: 'success' | 'error' | 'info' | 'warning', duration?: number }} options
 */
export function notify({ text, type = 'success', duration = 3500 }) {
    window.dispatchEvent(new CustomEvent('dashboard:notify', {
        detail: {
            text: t(text),
            type,
            duration,
        },
    }));
}

export function notifySuccess(message = 'Saved') {
    notify({ text: message, type: 'success' });
}

export function notifyError(message = 'Could not save changes.') {
    notify({ text: message, type: 'error' });
}

export function notifyApiSuccess(payload, fallback = 'Saved') {
    notifySuccess(payload?.message ?? fallback);
}

export function notifyApiError(error, fallback = 'Could not save changes.') {
    if (error instanceof ApiError) {
        const fieldError = Object.values(error.errors ?? {}).flat().find(Boolean);

        notify({
            text: fieldError ?? error.message ?? fallback,
            type: 'error',
        });

        return;
    }

    notifyError(fallback);
}
