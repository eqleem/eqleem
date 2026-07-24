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

export const useServicesStore = defineStore('servicesCatalog', {
    state: () => ({
        type: contentTypeBySlug('services'),
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

        categories: [],
        parentOptions: [],
        categoriesLoading: false,
        categoriesLoaded: false,
        categoriesError: null,
        categoriesSearch: '',

        settings: {
            section_title: '',
            section_description: '',
        },
        settingsLoading: false,
        settingsLoaded: false,
        settingsError: null,

        calendars: [],
        calendarsLoading: false,
        calendarsLoaded: false,
        calendarsError: null,
        calendarsSearch: '',
    }),

    getters: {
        isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
        hasPages: (state) => state.meta.last_page > 1,
        categoriesEmpty: (state) => state.categoriesLoaded && !state.categoriesLoading && state.categories.length === 0,
        categoryOptions: (state) => state.detail?.category_options ?? [],
        calendarOptions: (state) => state.detail?.calendar_options ?? [],
    },

    actions: {
        async fetchServices({ page = 1, search } = {}) {
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

                const payload = await api(`/services?${params.toString()}`);

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
            await this.fetchServices({ page: 1 });
        },

        async goToPage(page) {
            await this.fetchServices({ page });
        },

        async createService(title) {
            this.saving = true;
            this.error = null;

            try {
                const payload = await api('/services', {
                    method: 'POST',
                    body: { title },
                });

                this.detail = payload?.data ?? null;
                await this.fetchServices({ page: 1 });

                return this.detail;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر إنشاء الخدمة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async fetchService(uuid) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/services/${uuid}`);
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

        async updateService(uuid, data) {
            this.saving = true;

            try {
                const payload = await api(`/services/${uuid}`, {
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

        async deleteServices(ids) {
            this.saving = true;
            this.error = null;

            try {
                await api('/services', {
                    method: 'DELETE',
                    body: { ids: ids.map(Number) },
                });

                await this.fetchServices({ page: this.meta.current_page || 1 });
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر حذف الخدمات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async toggleServiceActive(uuid, active) {
            const payload = await api(`/services/${uuid}/active`, {
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

            const payload = await api(`/services/${uuid}/images`, {
                method: 'POST',
                body,
            });

            if (this.detail) {
                this.detail.images = payload?.data?.images ?? this.detail.images;
            }

            return payload?.data?.images ?? [];
        },

        async reorderImages(uuid, order) {
            const payload = await api(`/services/${uuid}/images/reorder`, {
                method: 'PUT',
                body: { order },
            });

            if (this.detail) {
                this.detail.images = payload?.data?.images ?? this.detail.images;
            }

            return payload?.data?.images ?? [];
        },

        async deleteImage(uuid, mediaId) {
            const payload = await api(`/services/${uuid}/images/${mediaId}`, {
                method: 'DELETE',
            });

            if (this.detail) {
                this.detail.images = payload?.data?.images ?? this.detail.images;
            }

            return payload?.data?.images ?? [];
        },

        async fetchCategories({ search, force = false } = {}) {
            if (search !== undefined) {
                this.categoriesSearch = search;
            }

            if (this.categoriesLoaded && !force && search === undefined) {
                return;
            }

            this.categoriesLoading = true;
            this.categoriesError = null;

            try {
                const params = new URLSearchParams();
                const query = this.categoriesSearch.trim();

                if (query) {
                    params.set('search', query);
                }

                const qs = params.toString();
                const payload = await api(`/services/categories${qs ? `?${qs}` : ''}`);

                this.categories = Array.isArray(payload?.data?.categories) ? payload.data.categories : [];
                this.parentOptions = Array.isArray(payload?.data?.parent_options) ? payload.data.parent_options : [];
                this.categoriesLoaded = true;
            } catch (error) {
                this.categoriesError = error instanceof ApiError ? error.message : 'تعذر تحميل التصنيفات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.categoriesLoading = false;
            }
        },

        async createCategory(data) {
            this.saving = true;
            this.categoriesError = null;

            try {
                const payload = await api('/services/categories', {
                    method: 'POST',
                    body: data,
                });

                this.categories = Array.isArray(payload?.data?.categories) ? payload.data.categories : this.categories;

                return payload?.data?.category ?? null;
            } catch (error) {
                this.categoriesError = error instanceof ApiError ? error.message : 'تعذر إنشاء التصنيف.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async updateCategory(id, data) {
            this.saving = true;
            this.categoriesError = null;

            try {
                const payload = await api(`/services/categories/${id}`, {
                    method: 'PUT',
                    body: data,
                });

                this.categories = Array.isArray(payload?.data?.categories) ? payload.data.categories : this.categories;

                return payload?.data?.category ?? null;
            } catch (error) {
                this.categoriesError = error instanceof ApiError ? error.message : 'تعذر تحديث التصنيف.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async deleteCategory(id) {
            this.saving = true;
            this.categoriesError = null;

            try {
                const payload = await api(`/services/categories/${id}`, {
                    method: 'DELETE',
                });

                this.categories = Array.isArray(payload?.data?.categories) ? payload.data.categories : this.categories;
            } catch (error) {
                this.categoriesError = error instanceof ApiError ? error.message : 'تعذر حذف التصنيف.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async reorderCategories(order) {
            const previous = [...this.categories];

            try {
                const payload = await api('/services/categories/reorder', {
                    method: 'PUT',
                    body: { order },
                });

                this.categories = Array.isArray(payload?.data?.categories) ? payload.data.categories : this.categories;
            } catch (error) {
                this.categories = previous;
                this.categoriesError = error instanceof ApiError ? error.message : 'تعذر إعادة الترتيب.';
                redirectIfUnauthorized(error);
                throw error;
            }
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
                const payload = await api('/services/settings');
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
                const payload = await api('/services/settings', {
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

        parentOptionsFor(excludeIds = []) {
            const excluded = new Set(excludeIds.map(Number));
            const options = [{ id: '', label: 'بدون تصنيف أب' }];

            for (const item of this.categories) {
                if (excluded.has(Number(item.id))) {
                    continue;
                }

                options.push({
                    id: String(item.id),
                    label: `${'— '.repeat(item.depth ?? 0)}${item.name}`,
                });
            }

            return options;
        },

        descendantIds(categoryId) {
            const ids = [Number(categoryId)];
            let changed = true;

            while (changed) {
                changed = false;

                for (const item of this.categories) {
                    if (item.parent_id !== null && ids.includes(Number(item.parent_id)) && !ids.includes(Number(item.id))) {
                        ids.push(Number(item.id));
                        changed = true;
                    }
                }
            }

            return ids;
        },

        async fetchCalendars({ search } = {}) {
            if (search !== undefined) {
                this.calendarsSearch = search;
            }

            this.calendarsLoading = true;
            this.calendarsError = null;

            try {
                const params = new URLSearchParams();
                const query = this.calendarsSearch.trim();

                if (query) {
                    params.set('search', query);
                }

                const qs = params.toString();
                const payload = await api(`/services/calendars${qs ? `?${qs}` : ''}`);

                this.calendars = Array.isArray(payload?.data) ? payload.data : [];
                this.calendarsLoaded = true;
            } catch (error) {
                this.calendarsError = error instanceof ApiError ? error.message : 'تعذر تحميل مقدمي الخدمات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.calendarsLoading = false;
            }
        },

        async fetchCalendar(id) {
            const payload = await api(`/services/calendars/${id}`);

            return payload?.data ?? null;
        },

        async createCalendar(data) {
            this.saving = true;
            this.calendarsError = null;

            try {
                await api('/services/calendars', {
                    method: 'POST',
                    body: data,
                });

                await this.fetchCalendars();
            } catch (error) {
                this.calendarsError = error instanceof ApiError ? error.message : 'تعذر إنشاء مقدم الخدمة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async updateCalendar(id, data) {
            this.saving = true;
            this.calendarsError = null;

            try {
                await api(`/services/calendars/${id}`, {
                    method: 'PUT',
                    body: data,
                });

                await this.fetchCalendars();
            } catch (error) {
                this.calendarsError = error instanceof ApiError ? error.message : 'تعذر تحديث مقدم الخدمة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async deleteCalendar(id) {
            this.saving = true;
            this.calendarsError = null;

            try {
                await api(`/services/calendars/${id}`, {
                    method: 'DELETE',
                });

                await this.fetchCalendars();
            } catch (error) {
                this.calendarsError = error instanceof ApiError ? error.message : 'تعذر حذف مقدم الخدمة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },
    },
});
