import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

function emptyMeta() {
    return { current_page: 1, last_page: 1, per_page: 20, total: 0 };
}

export const useBookingsStore = defineStore('bookings', {
    state: () => ({
        items: [],
        meta: emptyMeta(),
        search: '',
        status: '',
        loading: false,
        loaded: false,
        error: null,
        creating: false,
        detail: null,
        detailLoading: false,
        detailError: null,
    }),

    getters: {
        isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
        hasPages: (state) => state.meta.last_page > 1,
    },

    actions: {
        async fetchBooking(id) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/bookings/${id}`);
                this.detail = payload?.data ?? payload;

                return this.detail;
            } catch (error) {
                this.detail = null;
                this.detailError = error instanceof ApiError ? error.message : 'Failed to load booking';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.detailLoading = false;
            }
        },

        clearDetail() {
            this.detail = null;
            this.detailError = null;
            this.detailLoading = false;
        },

        async fetchBookings({ page = 1, search, status } = {}) {
            if (search !== undefined) {
                this.search = search;
            }

            if (status !== undefined) {
                this.status = status ?? '';
            }

            this.loading = true;
            this.error = null;

            try {
                const params = new URLSearchParams();
                params.set('page', String(page));
                params.set('per_page', String(this.meta.per_page || 20));

                const query = this.search.trim();
                if (query) {
                    params.set('search', query);
                }

                const statusFilter = String(this.status ?? '').trim();
                if (statusFilter) {
                    params.set('status', statusFilter);
                }

                const payload = await api(`/bookings?${params.toString()}`);

                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.items.length,
                };
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'Failed to load bookings';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        async setSearch(search, { fetch = true } = {}) {
            this.search = search;

            if (fetch) {
                await this.fetchBookings({ page: 1 });
            }
        },

        async setStatus(status, { fetch = true } = {}) {
            this.status = status ?? '';

            if (fetch) {
                await this.fetchBookings({ page: 1 });
            }
        },

        async goToPage(page) {
            await this.fetchBookings({ page });
        },

        /**
         * Fetch bookings overlapping a visible calendar range (no store mutation of list items).
         *
         * @returns {Promise<object[]>}
         */
        async fetchCalendarBookings({ from, to, search, status } = {}) {
            try {
                const params = new URLSearchParams();
                params.set('page', '1');
                params.set('per_page', '200');

                if (from) {
                    params.set('from', String(from));
                }

                if (to) {
                    params.set('to', String(to));
                }

                const query = String(search ?? this.search ?? '').trim();
                if (query) {
                    params.set('search', query);
                }

                const statusFilter = String(status ?? this.status ?? '').trim();
                if (statusFilter) {
                    params.set('status', statusFilter);
                }

                const payload = await api(`/bookings?${params.toString()}`);

                return Array.isArray(payload?.data) ? payload.data : [];
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            }
        },

        async searchContent(type, search) {
            const query = String(search ?? '').trim();

            if (!query) {
                return [];
            }

            try {
                const params = new URLSearchParams();
                params.set('type', type);
                params.set('search', query);

                const payload = await api(`/orders/content-search?${params.toString()}`);

                return Array.isArray(payload?.data) ? payload.data : [];
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            }
        },

        async fetchAvailability({ contentId, calendarId = null, date = null } = {}) {
            try {
                const params = new URLSearchParams();
                params.set('content_id', String(contentId));

                if (calendarId) {
                    params.set('calendar_id', String(calendarId));
                }

                if (date) {
                    params.set('date', String(date));
                }

                const payload = await api(`/bookings/availability?${params.toString()}`);

                return payload?.data ?? payload;
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            }
        },

        async createBooking(data) {
            this.creating = true;

            try {
                const payload = await api('/bookings', {
                    method: 'POST',
                    body: data,
                });

                const booking = payload?.data ?? payload;

                this.items = [booking, ...this.items.filter((item) => item.id !== booking.id)];
                this.meta.total += 1;
                this.loaded = true;

                return { booking, message: payload?.message ?? null };
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.creating = false;
            }
        },
    },
});
