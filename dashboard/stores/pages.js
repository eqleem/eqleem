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

export const usePagesStore = defineStore('pages', {
    state: () => ({
        type: contentTypeBySlug('pages'),
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

        blocks: [],
        blockTypes: [],
        blocksLoading: false,
        blocksError: null,
        editingBlock: null,
        editingBlockLoading: false,
        editingBlockError: null,
    }),

    getters: {
        isEmpty: (state) => state.loaded && !state.loading && state.items.length === 0,
        hasPages: (state) => state.meta.last_page > 1,
        deletableItems: (state) => state.items.filter((item) => !item.is_system_page),
    },

    actions: {
        async fetchPages({ page = 1, search } = {}) {
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

                const payload = await api(`/pages?${params.toString()}`);

                this.items = Array.isArray(payload?.data) ? payload.data : [];
                this.meta = {
                    current_page: payload?.meta?.current_page ?? page,
                    last_page: payload?.meta?.last_page ?? 1,
                    per_page: payload?.meta?.per_page ?? 20,
                    total: payload?.meta?.total ?? this.items.length,
                };
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل الصفحات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async setSearch(search) {
            this.search = search;
            await this.fetchPages({ page: 1 });
        },

        async goToPage(page) {
            await this.fetchPages({ page });
        },

        async createPage(title) {
            this.saving = true;
            this.error = null;

            try {
                const payload = await api('/pages', {
                    method: 'POST',
                    body: { title },
                });

                this.detail = payload?.data ?? null;
                await this.fetchPages({ page: 1 });

                return this.detail;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر إنشاء الصفحة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async fetchPage(uuid) {
            this.detailLoading = true;
            this.detailError = null;

            try {
                const payload = await api(`/pages/${uuid}`);
                this.detail = payload?.data ?? null;

                return this.detail;
            } catch (error) {
                this.detail = null;
                this.detailError = error instanceof ApiError ? error.message : 'تعذر تحميل الصفحة.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.detailLoading = false;
            }
        },

        async updatePage(uuid, data) {
            this.saving = true;

            try {
                const payload = await api(`/pages/${uuid}`, {
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

        async deletePages(ids) {
            this.saving = true;
            this.error = null;

            try {
                await api('/pages', {
                    method: 'DELETE',
                    body: { ids: ids.map(Number) },
                });

                await this.fetchPages({ page: this.meta.current_page || 1 });
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر حذف الصفحات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async togglePageActive(uuid, active) {
            const payload = await api(`/pages/${uuid}/active`, {
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

        async fetchBlocks(uuid) {
            this.blocksLoading = true;
            this.blocksError = null;

            try {
                const payload = await api(`/pages/${uuid}/blocks`);
                this.blocks = Array.isArray(payload?.data) ? payload.data : [];
                this.blockTypes = Array.isArray(payload?.block_types) ? payload.block_types : [];

                return this.blocks;
            } catch (error) {
                this.blocksError = error instanceof ApiError ? error.message : 'تعذر تحميل البلوكات.';
                redirectIfUnauthorized(error);
                throw error;
            } finally {
                this.blocksLoading = false;
            }
        },

        applyBlocks(payload) {
            this.blocks = Array.isArray(payload?.data) ? payload.data : this.blocks;
            if (Array.isArray(payload?.block_types)) {
                this.blockTypes = payload.block_types;
            }
        },

        async createBlock(uuid, type) {
            this.saving = true;

            try {
                const payload = await api(`/pages/${uuid}/blocks`, {
                    method: 'POST',
                    body: { type },
                });

                await this.fetchBlocks(uuid);

                return payload?.data ?? null;
            } catch (error) {
                this.blocksError = error instanceof ApiError ? error.message : 'تعذر إضافة البلوك.';
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async reorderBlocks(uuid, order) {
            const previous = this.blocks.map((block) => ({ ...block }));
            const ordered = order
                .map((id) => this.blocks.find((block) => block.id === id))
                .filter(Boolean);

            this.blocks = ordered;

            try {
                const payload = await api(`/pages/${uuid}/blocks/reorder`, {
                    method: 'PUT',
                    body: { order },
                });

                this.applyBlocks(payload);
            } catch (error) {
                this.blocks = previous;
                this.blocksError = error instanceof ApiError ? error.message : 'تعذر إعادة ترتيب البلوكات.';
                throw error;
            }
        },

        async toggleBlockActive(uuid, id, active) {
            const block = this.blocks.find((item) => item.id === id);

            if (block) {
                block.active = active;
            }

            try {
                const payload = await api(`/pages/${uuid}/blocks/${id}/active`, {
                    method: 'PUT',
                    body: { active },
                });

                const updated = payload?.data;
                const index = this.blocks.findIndex((item) => item.id === id);

                if (index !== -1 && updated) {
                    this.blocks[index] = { ...this.blocks[index], ...updated };
                }
            } catch (error) {
                if (block) {
                    block.active = !active;
                }

                throw error;
            }
        },

        async deleteBlock(uuid, id) {
            this.saving = true;

            try {
                await api(`/pages/${uuid}/blocks/${id}`, { method: 'DELETE' });
                this.blocks = this.blocks.filter((block) => block.id !== id);
            } catch (error) {
                this.blocksError = error instanceof ApiError ? error.message : 'تعذر حذف البلوك.';
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async fetchBlock(uuid, id) {
            this.editingBlock = null;
            this.editingBlockLoading = true;
            this.editingBlockError = null;

            try {
                const payload = await api(`/pages/${uuid}/blocks/${id}`);
                this.editingBlock = payload?.data ?? null;

                return this.editingBlock;
            } catch (error) {
                this.editingBlockError = error instanceof ApiError ? error.message : 'تعذر تحميل إعدادات البلوك.';
                this.editingBlock = null;
                throw error;
            } finally {
                this.editingBlockLoading = false;
            }
        },

        async updateBlock(uuid, id, body) {
            this.saving = true;

            try {
                const options = body instanceof FormData
                    ? { method: 'POST', body }
                    : { method: 'PUT', body };

                const payload = await api(`/pages/${uuid}/blocks/${id}`, options);
                this.editingBlock = payload?.data ?? this.editingBlock;
                await this.fetchBlocks(uuid);

                return this.editingBlock;
            } catch (error) {
                this.editingBlockError = error instanceof ApiError ? error.message : 'تعذر حفظ إعدادات البلوك.';
                throw error;
            } finally {
                this.saving = false;
            }
        },

        clearEditingBlock() {
            this.editingBlock = null;
            this.editingBlockError = null;
        },
    },
});
