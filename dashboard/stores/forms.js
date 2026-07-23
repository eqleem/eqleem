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

export function fieldTypeHasOptions(type) {
    return ['select', 'radio'].includes(type);
}

export function makeField(type = 'text') {
    const id = `fld_${Math.random().toString(36).slice(2, 10)}`;

    return {
        id,
        type,
        label: '',
        name: id,
        placeholder: '',
        required: false,
        info: '',
        options: fieldTypeHasOptions(type) ? ['', ''] : [],
    };
}

export const useFormsStore = defineStore('forms', {
    state: () => ({
        type: contentTypeBySlug('forms'),
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
    }),

    getters: {
        isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
        hasPages: (state) => state.meta.last_page > 1,
        fieldTypeOptions: (state) => state.detail?.field_type_options ?? {},
    },

    actions: {
        async fetchForms({ page = 1, search } = {}) {
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

                const payload = await api(`/forms?${params.toString()}`);

                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.items.length,
                };
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل النماذج.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async setSearch(search) {
            this.search = search;
            await this.fetchForms({ page: 1 });
        },

        async goToPage(page) {
            await this.fetchForms({ page });
        },

        async createForm(title) {
            this.saving = true;
            this.error = null;

            try {
                const payload = await api('/forms', {
                    method: 'POST',
                    body: { title },
                });

                this.detail = payload?.data ?? null;
                await this.fetchForms({ page: 1 });

                return this.detail;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر إنشاء النموذج.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async fetchForm(uuid) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/forms/${uuid}`);
                this.detail = payload?.data ?? null;

                return this.detail;
            } catch (error) {
                this.detail = null;
                this.detailError = error instanceof ApiError ? error.message : 'تعذر تحميل النموذج.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.detailLoading = false;
            }
        },

        async updateForm(uuid, data) {
            this.saving = true;

            try {
                const payload = await api(`/forms/${uuid}`, {
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

        async deleteForms(ids) {
            this.saving = true;
            this.error = null;

            try {
                await api('/forms', {
                    method: 'DELETE',
                    body: { ids: ids.map(Number) },
                });

                await this.fetchForms({ page: this.meta.current_page || 1 });
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر حذف النماذج.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async cloneForm(uuid) {
            this.saving = true;
            this.error = null;

            try {
                const payload = await api(`/forms/${uuid}/clone`, {
                    method: 'POST',
                });

                await this.fetchForms({ page: 1 });

                return payload?.data ?? null;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تكرار النموذج.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async toggleFormActive(uuid, active) {
            const payload = await api(`/forms/${uuid}/active`, {
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
    },
});
