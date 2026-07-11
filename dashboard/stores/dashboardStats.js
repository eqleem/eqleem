import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

const CLIENT_TTL_MS = 3 * 60 * 1000;

const emptyMetric = () => ({ value: 0, growth: 0 });
const emptySales = () => ({
    value: 0,
    growth: 0,
    value_formatted: '0',
    currency: 'SAR',
});

export const useDashboardStatsStore = defineStore('dashboardStats', {
    state: () => ({
        rangeDays: 0,
        orders: emptyMetric(),
        sales: emptySales(),
        visits: emptyMetric(),
        clients: emptyMetric(),
        loading: false,
        loaded: false,
        fetchedAt: null,
        error: null,
        inflight: null,
    }),

    getters: {
        isStale: (state) => {
            if (!state.fetchedAt) {
                return true;
            }

            return Date.now() - state.fetchedAt >= CLIENT_TTL_MS;
        },
    },

    actions: {
        async fetchStats({ force = false } = {}) {
            if (!force && this.loaded && !this.isStale) {
                return this;
            }

            if (this.inflight) {
                return this.inflight;
            }

            this.loading = true;
            this.error = null;

            this.inflight = (async () => {
                try {
                    const params = force ? '?fresh=1' : '';
                    const payload = await api(`/dashboard/stats${params}`);
                    const data = payload?.data ?? payload;

                    this.rangeDays = data.range_days ?? 0;
                    this.orders = {
                        value: Number(data.orders?.value ?? 0),
                        growth: Number(data.orders?.growth ?? 0),
                    };
                    this.sales = {
                        value: Number(data.sales?.value ?? 0),
                        growth: Number(data.sales?.growth ?? 0),
                        value_formatted: data.sales?.value_formatted ?? '0',
                        currency: data.sales?.currency ?? 'SAR',
                    };
                    this.visits = {
                        value: Number(data.visits?.value ?? 0),
                        growth: Number(data.visits?.growth ?? 0),
                    };
                    this.clients = {
                        value: Number(data.clients?.value ?? 0),
                        growth: Number(data.clients?.growth ?? 0),
                    };
                    this.loaded = true;
                    this.fetchedAt = Date.now();
                } catch (error) {
                    this.error = error instanceof ApiError ? error.message : 'Failed to load dashboard stats';
                } finally {
                    this.loading = false;
                    this.inflight = null;
                }

                return this;
            })();

            return this.inflight;
        },

        invalidate() {
            this.fetchedAt = null;
        },
    },
});
