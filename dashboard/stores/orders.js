import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

function emptyMeta() {
    return { current_page: 1, last_page: 1, per_page: 20, total: 0 };
}

function createListStore(name, endpoint) {
    return defineStore(name, {
        state: () => ({
            items: [],
            meta: emptyMeta(),
            search: '',
            loading: false,
            loaded: false,
            error: null,
            detail: null,
            detailLoading: false,
            detailError: null,
        }),

        getters: {
            isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
            hasPages: (state) => state.meta.last_page > 1,
        },

        actions: {
            async fetchList({ page = 1, search } = {}) {
                if (search !== undefined) {
                    this.search = search;
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

                    const payload = await api(`${endpoint}?${params.toString()}`);

                    this.items = Array.isArray(payload?.data) ? payload.data : [];
                    this.meta = {
                        current_page: payload?.meta?.current_page ?? page,
                        last_page: payload?.meta?.last_page ?? 1,
                        per_page: payload?.meta?.per_page ?? 20,
                        total: payload?.meta?.total ?? this.items.length,
                    };
                    this.loaded = true;
                } catch (error) {
                    this.error = error instanceof ApiError ? error.message : `Failed to load ${name}`;

                    if (error instanceof ApiError && error.status === 401) {
                        window.location.href = '/login';
                    }

                    throw error;
                } finally {
                    this.loading = false;
                }
            },

            async setSearch(search) {
                this.search = search;
                await this.fetchList({ page: 1 });
            },

            async goToPage(page) {
                await this.fetchList({ page });
            },

            async fetchDetail(id) {
                this.detailLoading = true;
                this.detailError = null;

                try {
                    const payload = await api(`${endpoint}/${id}`);
                    this.detail = payload?.data ?? payload;

                    return this.detail;
                } catch (error) {
                    this.detail = null;
                    this.detailError = error instanceof ApiError ? error.message : `Failed to load ${name}`;

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
            },
        },
    });
}

export const useOrdersStore = defineStore('orders', {
    state: () => ({
        items: [],
        meta: emptyMeta(),
        search: '',
        loading: false,
        loaded: false,
        error: null,
        detail: null,
        detailLoading: false,
        detailError: null,
        creating: false,
    }),

    getters: {
        isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
        hasPages: (state) => state.meta.last_page > 1,
    },

    actions: {
        async fetchOrders({ page = 1, search } = {}) {
            if (search !== undefined) {
                this.search = search;
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

                const payload = await api(`/orders?${params.toString()}`);

                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.items.length,
                };
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'Failed to load orders';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        async setSearch(search) {
            this.search = search;
            await this.fetchOrders({ page: 1 });
        },

        async goToPage(page) {
            await this.fetchOrders({ page });
        },

        async fetchOrder(uuid) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/orders/${uuid}`);
                this.detail = payload?.data ?? payload;

                return this.detail;
            } catch (error) {
                this.detail = null;
                this.detailError = error instanceof ApiError ? error.message : 'Failed to load order';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.detailLoading = false;
            }
        },

        async searchContent(type, search) {
            const query = String(search ?? '').trim();

            if (!query || type === 'other') {
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

        async createDraftContent({ type, title, unit_price = 0 }) {
            try {
                const payload = await api('/orders/content', {
                    method: 'POST',
                    body: { type, title, unit_price },
                });

                return payload?.data ?? payload;
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            }
        },

        async createOrder(data) {
            this.creating = true;

            try {
                const payload = await api('/orders', {
                    method: 'POST',
                    body: data,
                });

                const order = payload?.data ?? payload;

                // Keep list rows lean (client is a string name on the index resource).
                const listRow = {
                    id: order.id,
                    uuid: order.uuid,
                    number: order.number,
                    status: order.status,
                    status_label: order.status_label,
                    status_color: order.status_color,
                    payment_status: order.payment_status,
                    payment_status_label: order.payment_status_label,
                    payment_status_color: order.payment_status_color,
                    grand_total: order.grand_total,
                    grand_total_formatted: order.grand_total_formatted,
                    currency_code: order.currency_code,
                    client: order.client?.name ?? null,
                    created: order.created,
                    issued_at: order.issued_at,
                };

                this.items = [listRow, ...this.items.filter((item) => item.id !== order.id)];
                this.meta.total += 1;
                this.loaded = true;
                this.detail = order;

                return { order, message: payload?.message ?? null };
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.creating = false;
            }
        },

        async recordPayment(uuid, data) {
            try {
                const payload = await api(`/orders/${uuid}/payments`, {
                    method: 'POST',
                    body: data,
                });

                const order = payload?.data ?? payload;
                this.detail = order;

                const index = this.items.findIndex((item) => item.uuid === uuid);
                if (index !== -1) {
                    this.items[index] = {
                        ...this.items[index],
                        payment_status: order.payment_status,
                        payment_status_label: order.payment_status_label,
                        payment_status_color: order.payment_status_color,
                        grand_total: order.grand_total,
                        grand_total_formatted: order.grand_total_formatted,
                    };
                }

                return { order, message: payload?.message ?? null };
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            }
        },

        removeLocal(ids) {
            const set = new Set(ids);
            this.items = this.items.filter((item) => !set.has(item.id));
            this.meta.total = Math.max(0, this.meta.total - ids.length);
        },

        clearDetail() {
            this.detail = null;
            this.detailError = null;
        },
    },
});

export const usePaymentsStore = createListStore('payments', '/payments');
export const useInvoicesStore = createListStore('invoices', '/invoices');
export const useFormSubmissionsStore = createListStore('formSubmissions', '/form-submissions');
