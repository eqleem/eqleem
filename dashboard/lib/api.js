/**
 * First-party SPA API client (Sanctum stateful session + CSRF cookie).
 * Always sends Accept: application/json so Action jsonResponse / validation errors stay JSON.
 */
function csrfToken() {
    const match = document.cookie.match(/(?:^|; )XSRF-TOKEN=([^;]*)/);

    return match ? decodeURIComponent(match[1]) : null;
}

export class ApiError extends Error {
    constructor(message, { status, errors = {}, data = null } = {}) {
        super(message);
        this.name = 'ApiError';
        this.status = status;
        this.errors = errors;
        this.data = data;
    }
}

export async function api(path, options = {}) {
    const headers = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(options.headers ?? {}),
    };

    const token = csrfToken();

    if (token) {
        headers['X-XSRF-TOKEN'] = token;
    }

    let body = options.body;

    if (body !== undefined && body !== null && !(body instanceof FormData)) {
        headers['Content-Type'] = 'application/json';
        body = JSON.stringify(body);
    }

    const response = await fetch(`/api${path}`, {
        credentials: 'same-origin',
        ...options,
        headers,
        body,
    });

    const contentType = response.headers.get('content-type') ?? '';
    const isJson = contentType.includes('application/json');
    const payload = isJson ? await response.json() : null;

    if (!response.ok) {
        throw new ApiError(payload?.message ?? `Request failed (${response.status})`, {
            status: response.status,
            errors: payload?.errors ?? {},
            data: payload,
        });
    }

    return payload;
}
