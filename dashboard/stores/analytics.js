import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

const emptySummary = () => ({
    views: 0,
    visitors: 0,
    bounce_rate: '0%',
    average_visit_time: '0s',
});

const emptyChart = () => ({
    labels: [],
    datasets: [],
});

export const useAnalyticsStore = defineStore('analytics', {
    state: () => ({
        dateRangeDays: 30,
        requestCategory: '',
        summary: emptySummary(),
        chart: emptyChart(),
        topPages: [],
        topReferrers: [],
        browsers: [],
        devices: [],
        countries: [],
        operatingSystems: [],
        dateRange: null,
        loading: false,
        loaded: false,
        error: null,
        inflight: null,
    }),

    actions: {
        async fetchOverview({ force = false } = {}) {
            if (this.loaded && !force) {
                return this;
            }

            if (this.inflight) {
                return this.inflight;
            }

            this.loading = true;
            this.error = null;

            this.inflight = (async () => {
                try {
                    const params = new URLSearchParams();
                    params.set('date_range', String(this.dateRangeDays));
                    params.set('with_percentages', '1');

                    if (this.requestCategory) {
                        params.set('request_category', this.requestCategory);
                    }

                    if (force) {
                        params.set('fresh', '1');
                    }

                    const payload = await api(`/analytics/overview?${params.toString()}`);
                    const data = payload?.data ?? payload;

                    this.summary = {
                        views: Number(data.summary?.views ?? 0),
                        visitors: Number(data.summary?.visitors ?? 0),
                        bounce_rate: data.summary?.bounce_rate ?? '0%',
                        average_visit_time: data.summary?.average_visit_time ?? '0s',
                    };
                    this.chart = {
                        labels: data.chart?.labels ?? [],
                        datasets: data.chart?.datasets ?? [],
                    };
                    this.topPages = data.top_pages ?? [];
                    this.topReferrers = data.top_referrers ?? [];
                    this.browsers = data.browsers ?? [];
                    this.devices = data.devices ?? [];
                    this.countries = data.countries ?? [];
                    this.operatingSystems = data.operating_systems ?? [];
                    this.dateRange = data.date_range ?? null;
                    this.loaded = true;
                } catch (error) {
                    this.error = error instanceof ApiError ? error.message : 'Failed to load analytics';

                    if (error instanceof ApiError && error.status === 401) {
                        window.location.href = '/login';
                    }
                } finally {
                    this.loading = false;
                    this.inflight = null;
                }

                return this;
            })();

            return this.inflight;
        },

        setFilters({ dateRangeDays, requestCategory } = {}) {
            let changed = false;

            if (dateRangeDays !== undefined) {
                const next = Number(dateRangeDays);

                if (this.dateRangeDays !== next) {
                    this.dateRangeDays = next;
                    changed = true;
                }
            }

            if (requestCategory !== undefined) {
                const next = requestCategory ?? '';

                if (this.requestCategory !== next) {
                    this.requestCategory = next;
                    changed = true;
                }
            }

            if (changed) {
                this.loaded = false;
            }
        },
    },
});
