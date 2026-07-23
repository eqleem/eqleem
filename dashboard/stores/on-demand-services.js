import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';
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

export const useOnDemandServicesStore = defineStore('onDemandServicesCatalog', {
    state: () => ({
        type: contentTypeBySlug('on-demand-services'),
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
        unitOptions: (state) => state.detail?.unit_options ?? [],
    },

    actions: {
        async fetchOnDemandServices({ page = 1, search } = {}) {
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

                const payload = await api(`/on-demand-services?${params.toString()}`);

                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.items.length,
                };
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل الخدمات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async setSearch(search) {
            this.search = search;
            await this.fetchOnDemandServices({ page: 1 });
        },

        async goToPage(page) {
            await this.fetchOnDemandServices({ page });
        },

        async createOnDemandService(title) {
            this.saving = true;
            this.error = null;

            try {
                const payload = await api('/on-demand-services', {
                    method: 'POST',
                    body: { title },
                });

                this.detail = payload?.data ?? null;
                await this.fetchOnDemandServices({ page: 1 });

                return this.detail;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر إنشاء الخدمة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async fetchOnDemandService(uuid) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/on-demand-services/${uuid}`);
                this.detail = payload?.data ?? null;

                return this.detail;
            } catch (error) {
                this.detail = null;
                this.detailError = error instanceof ApiError ? error.message : 'تعذر تحميل الخدمة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.detailLoading = false;
            }
        },

        async updateOnDemandService(uuid, data) {
            this.saving = true;

            try {
                const payload = await api(`/on-demand-services/${uuid}`, {
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

        async deleteOnDemandServices(ids) {
            this.saving = true;
            this.error = null;

            try {
                await api('/on-demand-services', {
                    method: 'DELETE',
                    body: { ids: ids.map(Number) },
                });

                await this.fetchOnDemandServices({ page: this.meta.current_page || 1 });
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر حذف الخدمات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async toggleOnDemandServiceActive(uuid, active) {
            const payload = await api(`/on-demand-services/${uuid}/active`, {
                method: 'PUT',
                body: { active },
            });

            const updated = payload?.data;
            const index = this.items.findIndex((item) => item.uuid === uuid);

            if (index !== -1 && updated) {
                this.items[index] = { ...this.items[index], ...updated };
            }

            if (this.detail?.uuid === uuid && updated) {
                this.detail = { ...this.detail, ...updated };
            }

            return updated;
        },

        async uploadImage(uuid, file) {
            const body = new FormData();
            body.append('file', file);

            const payload = await api(`/on-demand-services/${uuid}/images`, {
                method: 'POST',
                body,
            });

            if (this.detail) {
                this.detail.images = payload?.data?.images ?? this.detail.images;
            }

            return payload?.data?.images ?? [];
        },

        async reorderImages(uuid, order) {
            const payload = await api(`/on-demand-services/${uuid}/images/reorder`, {
                method: 'PUT',
                body: { order },
            });

            if (this.detail) {
                this.detail.images = payload?.data?.images ?? this.detail.images;
            }

            return payload?.data?.images ?? [];
        },

        async deleteImage(uuid, mediaId) {
            const payload = await api(`/on-demand-services/${uuid}/images/${mediaId}`, {
                method: 'DELETE',
            });

            if (this.detail) {
                this.detail.images = payload?.data?.images ?? this.detail.images;
            }

            return payload?.data?.images ?? [];
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
                const payload = await api('/on-demand-services/settings');
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
                const payload = await api('/on-demand-services/settings', {
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
