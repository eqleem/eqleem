import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';
import { lineChartOptions } from '../components/home/chartOptions.js';

const CLIENT_TTL_MS = 3 * 60 * 1000;

const CHARTS = ['orders', 'sales', 'visits', 'clients'];

const emptySlot = () => ({
    title: '',
    label: '',
    rangeDays: 0,
    options: null,
    loading: false,
    loaded: false,
    fetchedAt: null,
    error: null,
    inflight: null,
});

export const useDashboardChartsStore = defineStore('dashboardCharts', {
    state: () => ({
        charts: Object.fromEntries(CHARTS.map((chart) => [chart, emptySlot()])),
    }),

    actions: {
        isStale(chart) {
            const slot = this.charts[chart];

            if (!slot?.fetchedAt) {
                return true;
            }

            return Date.now() - slot.fetchedAt >= CLIENT_TTL_MS;
        },

        async fetchChart(chart, { force = false } = {}) {
            if (!CHARTS.includes(chart)) {
                return null;
            }

            const slot = this.charts[chart];

            if (!force && slot.loaded && !this.isStale(chart)) {
                return slot;
            }

            if (slot.inflight) {
                return slot.inflight;
            }

            slot.loading = true;
            slot.error = null;

            slot.inflight = (async () => {
                try {
                    const params = force ? '?fresh=1' : '';
                    const payload = await api(`/dashboard/charts/${chart}${params}`);
                    const data = payload?.data ?? payload;

                    slot.title = data.title ?? '';
                    slot.label = data.label ?? '';
                    slot.rangeDays = data.range_days ?? 0;
                    slot.options = data.options ?? lineChartOptions(slot.label || 'العدد', [], []);
                    slot.loaded = true;
                    slot.fetchedAt = Date.now();
                } catch (error) {
                    slot.error = error instanceof ApiError ? error.message : 'Failed to load chart';
                } finally {
                    slot.loading = false;
                    slot.inflight = null;
                }

                return slot;
            })();

            return slot.inflight;
        },

        invalidate(chart) {
            if (chart) {
                this.charts[chart].fetchedAt = null;

                return;
            }

            CHARTS.forEach((name) => {
                this.charts[name].fetchedAt = null;
            });
        },
    },
});
