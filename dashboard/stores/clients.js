import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

export const useClientsStore = defineStore('clients', {
    state: () => ({
        items: [],
        meta: {
            current_page: 1,
            last_page: 1,
            per_page: 20,
            total: 0,
        },
        search: '',
        loading: false,
        loaded: false,
        error: null,
        detail: null,
        detailLoading: false,
        detailError: null,
        saving: false,
        orders: {
            items: [],
            meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
            search: '',
            loading: false,
            loaded: false,
            error: null,
            clientUuid: null,
        },
        invoices: {
            items: [],
            meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
            search: '',
            loading: false,
            loaded: false,
            error: null,
            clientUuid: null,
        },
    }),

    getters: {
        isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
        hasPages: (state) => state.meta.last_page > 1,
        ordersEmpty: (state) => state.orders.loaded && !state.orders.loading && state.orders.items.length === 0,
        ordersHasPages: (state) => state.orders.meta.last_page > 1,
        invoicesEmpty: (state) => state.invoices.loaded && !state.invoices.loading && state.invoices.items.length === 0,
        invoicesHasPages: (state) => state.invoices.meta.last_page > 1,
    },

    actions: {
        async fetchClients({ page = 1, search } = {}) {
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

                const payload = await api(`/clients?${params.toString()}`);

                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.items.length,
                };
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'Failed to load clients';

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
            await this.fetchClients({ page: 1 });
        },

        async goToPage(page) {
            await this.fetchClients({ page });
        },

        async fetchClient(uuid) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/clients/${uuid}`);
                this.detail = payload?.data ?? payload;

                return this.detail;
            } catch (error) {
                this.detail = null;
                this.detailError = error instanceof ApiError ? error.message : 'Failed to load client';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.detailLoading = false;
            }
        },

        async createClient(data) {
            this.saving = true;

            try {
                const payload = await api('/clients', {
                    method: 'POST',
                    body: data,
                });

                const client = payload?.data ?? payload;

                this.items = [client, ...this.items.filter((item) => item.id !== client.id)];
                this.meta.total += 1;
                this.loaded = true;

                return { client, message: payload?.message ?? null };
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.saving = false;
            }
        },

        async searchClients(search, { perPage = 8 } = {}) {
            const query = String(search ?? '').trim();

            if (!query) {
                return [];
            }

            try {
                const params = new URLSearchParams();
                params.set('page', '1');
                params.set('per_page', String(perPage));
                params.set('search', query);

                const payload = await api(`/clients?${params.toString()}`);

                return Array.isArray(payload?.data) ? payload.data : [];
            } catch (error) {
                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            }
        },

        async fetchClientOrders(uuid, { page = 1, search } = {}) {
            if (search !== undefined) {
                this.orders.search = search;
            }

            this.orders.loading = true;
            this.orders.error = null;
            this.orders.clientUuid = uuid;

            try {
                const params = new URLSearchParams();
                params.set('page', String(page));
                params.set('per_page', String(this.orders.meta.per_page || 20));

                const query = this.orders.search.trim();
                if (query) {
                    params.set('search', query);
                }

                const payload = await api(`/clients/${uuid}/orders?${params.toString()}`);

                this.orders.items = Array.isArray(payload?.data) ? payload.data : [];
                this.orders.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.orders.items.length,
                };
                this.orders.loaded = true;
            } catch (error) {
                this.orders.error = error instanceof ApiError ? error.message : 'Failed to load orders';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.orders.loading = false;
            }
        },

        async setClientOrdersSearch(uuid, search) {
            this.orders.search = search;
            await this.fetchClientOrders(uuid, { page: 1 });
        },

        async goToClientOrdersPage(uuid, page) {
            await this.fetchClientOrders(uuid, { page });
        },

        async fetchClientInvoices(uuid, { page = 1, search } = {}) {
            if (search !== undefined) {
                this.invoices.search = search;
            }

            this.invoices.loading = true;
            this.invoices.error = null;
            this.invoices.clientUuid = uuid;

            try {
                const params = new URLSearchParams();
                params.set('page', String(page));
                params.set('per_page', String(this.invoices.meta.per_page || 20));

                const query = this.invoices.search.trim();
                if (query) {
                    params.set('search', query);
                }

                const payload = await api(`/clients/${uuid}/invoices?${params.toString()}`);

                this.invoices.items = Array.isArray(payload?.data) ? payload.data : [];
                this.invoices.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.invoices.items.length,
                };
                this.invoices.loaded = true;
            } catch (error) {
                this.invoices.error = error instanceof ApiError ? error.message : 'Failed to load invoices';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.invoices.loading = false;
            }
        },

        async setClientInvoicesSearch(uuid, search) {
            this.invoices.search = search;
            await this.fetchClientInvoices(uuid, { page: 1 });
        },

        async goToClientInvoicesPage(uuid, page) {
            await this.fetchClientInvoices(uuid, { page });
        },

        removeLocal(ids) {
            const set = new Set(ids);
            this.items = this.items.filter((item) => !set.has(item.id));
            this.meta.total = Math.max(0, this.meta.total - ids.length);
        },

        clearDetail() {
            this.detail = null;
            this.detailError = null;
            this.orders = {
                items: [],
                meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
                search: '',
                loading: false,
                loaded: false,
                error: null,
                clientUuid: null,
            };
            this.invoices = {
                items: [],
                meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
                search: '',
                loading: false,
                loaded: false,
                error: null,
                clientUuid: null,
            };
        },
    },
});
