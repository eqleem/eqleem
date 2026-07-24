import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';
import { syncListImage } from '../lib/syncListImage.js';
import { contentTypeBySlug } from '../data/page.js';

function emptyMeta() {
    return {
        current_page: 1,
        last_page: 1,
        per_page: 20,
        total: 0,
    };
}

function redirectIfUnauthorized(error) {
    if (error instanceof ApiError && error.status === 401) {
        window.location.href = '/login';
    }
}

export const useNewsletterStore = defineStore('newsletter', {
    state: () => ({
        type: contentTypeBySlug('newsletter'),
        items: [],
        meta: emptyMeta(),
        search: '',
        loading: false,
        loaded: false,
        error: null,
        saving: false,

        detail: null,
        detailLoading: false,
        detailError: null,

        settings: {
            section_title: '',
            section_description: '',
        },
        settingsLoading: false,
        settingsLoaded: false,
        settingsError: null,
    }),

    getters: {
        isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
        hasPages: (state) => state.meta.last_page > 1,
        mailStatusOptions: (state) => state.detail?.mail_status_options ?? {},
    },

    actions: {
        async fetchIssues({ page = 1, search } = {}) {
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

                const payload = await api(`/newsletter?${params.toString()}`);

                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.items.length,
                };
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل النشرات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async setSearch(search) {
            this.search = search;
            await this.fetchIssues({ page: 1 });
        },

        async goToPage(page) {
            await this.fetchIssues({ page });
        },

        async createIssue(title) {
            this.saving = true;
            this.error = null;

            try {
                const payload = await api('/newsletter', {
                    method: 'POST',
                    body: { title },
                });

                this.detail = payload?.data ?? null;
                await this.fetchIssues({ page: 1 });

                return this.detail;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر إنشاء النشرة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async fetchIssue(uuid) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/newsletter/${uuid}`);
                this.detail = payload?.data ?? null;

                return this.detail;
            } catch (error) {
                this.detail = null;
                this.detailError = error instanceof ApiError ? error.message : 'تعذر تحميل النشرة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.detailLoading = false;
            }
        },

        async updateIssue(uuid, data) {
            this.saving = true;

            try {
                const payload = await api(`/newsletter/${uuid}`, {
                    method: 'PUT',
                    body: data,
                });

                this.detail = payload?.data ?? this.detail;

                return this.detail;
            } catch (error) {
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async deleteIssues(ids) {
            this.saving = true;
            this.error = null;

            try {
                await api('/newsletter', {
                    method: 'DELETE',
                    body: { ids: ids.map(Number) },
                });

                await this.fetchIssues({ page: this.meta.current_page || 1 });
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر حذف النشرات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async uploadFeaturedImage(uuid, file) {
            const body = new FormData();
            body.append('file', file);

            const payload = await api(`/newsletter/${uuid}/featured-image`, {
                method: 'POST',
                body,
            });

            const featuredImage = payload?.data?.featured_image ?? this.detail?.featured_image ?? null;

            if (this.detail) {
                this.detail.featured_image = featuredImage;
            }

            syncListImage(this.items, uuid, featuredImage);

            return featuredImage;
        },

        async deleteFeaturedImage(uuid) {
            const payload = await api(`/newsletter/${uuid}/featured-image`, {
                method: 'DELETE',
            });

            const featuredImage = payload?.data?.featured_image ?? null;

            if (this.detail) {
                this.detail.featured_image = featuredImage;
            }

            syncListImage(this.items, uuid, featuredImage);

            return featuredImage;
        },

        async fetchSettings({ force = false } = {}) {
            if (this.settingsLoading) {
                return;
            }

            if (this.settingsLoaded && !force) {
                return;
            }

            this.settingsLoading = true;
            this.settingsError = null;

            try {
                const payload = await api('/newsletter/settings');
                this.settings = {
                    section_title: payload?.data?.section_title ?? '',
                    section_description: payload?.data?.section_description ?? '',
                };
                this.settingsLoaded = true;
            } catch (error) {
                this.settingsError = error instanceof ApiError ? error.message : 'تعذر تحميل الإعدادات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.settingsLoading = false;
            }
        },

        async updateSettings(data) {
            this.saving = true;
            this.settingsError = null;

            try {
                const payload = await api('/newsletter/settings', {
                    method: 'PUT',
                    body: data,
                });

                this.settings = {
                    section_title: payload?.data?.section_title ?? data.section_title,
                    section_description: payload?.data?.section_description ?? data.section_description,
                };
                this.settingsLoaded = true;

                return this.settings;
            } catch (error) {
                this.settingsError = error instanceof ApiError ? error.message : 'تعذر حفظ الإعدادات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },
    },
});
