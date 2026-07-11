import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

function emptyStructure() {
    return {
        top_blocks: [],
        user_blocks: [],
        bottom_blocks: [],
        block_types: [],
    };
}

export const usePageStructureStore = defineStore('pageStructure', {
    state: () => ({
        topBlocks: [],
        userBlocks: [],
        bottomBlocks: [],
        blockTypes: [],
        loading: false,
        loaded: false,
        error: null,
        saving: false,
        editing: null,
        editingLoading: false,
        editingError: null,
    }),

    getters: {
        userBlocksEmpty: (state) => state.loaded && !state.loading && state.userBlocks.length === 0,
    },

    actions: {
        applyStructure(payload) {
            const data = payload?.data ?? payload ?? emptyStructure();
            this.topBlocks = Array.isArray(data.top_blocks) ? data.top_blocks : [];
            this.userBlocks = Array.isArray(data.user_blocks) ? data.user_blocks : [];
            this.bottomBlocks = Array.isArray(data.bottom_blocks) ? data.bottom_blocks : [];
            this.blockTypes = Array.isArray(data.block_types) ? data.block_types : [];
            this.loaded = true;
        },

        async fetchStructure({ force = false } = {}) {
            if (this.loading) {
                return;
            }

            if (this.loaded && !force) {
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                this.applyStructure(await api('/page/structure'));
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل هيكل الصفحة.';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createBlock(type) {
            this.saving = true;
            this.error = null;

            try {
                const payload = await api('/page/blocks', {
                    method: 'POST',
                    body: { type },
                });

                await this.fetchStructure({ force: true });

                return payload?.data ?? null;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر إضافة البلوك.';
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async reorderBlocks(order) {
            const previous = this.userBlocks.map((block) => ({ ...block }));
            const ordered = order
                .map((id) => this.userBlocks.find((block) => block.id === id))
                .filter(Boolean);

            this.userBlocks = ordered;

            try {
                this.applyStructure(await api('/page/blocks/reorder', {
                    method: 'PUT',
                    body: { order },
                }));
            } catch (error) {
                this.userBlocks = previous;
                this.error = error instanceof ApiError ? error.message : 'تعذر إعادة ترتيب البلوكات.';
                throw error;
            }
        },

        async toggleActive(id, active) {
            const block = this.userBlocks.find((item) => item.id === id);

            if (block) {
                block.active = active;
            }

            try {
                const payload = await api(`/page/blocks/${id}/active`, {
                    method: 'PUT',
                    body: { active },
                });

                const updated = payload?.data;
                const index = this.userBlocks.findIndex((item) => item.id === id);

                if (index !== -1 && updated) {
                    this.userBlocks[index] = { ...this.userBlocks[index], ...updated };
                }
            } catch (error) {
                if (block) {
                    block.active = !active;
                }

                this.error = error instanceof ApiError ? error.message : 'تعذر تحديث حالة البلوك.';
                throw error;
            }
        },

        async deleteBlock(id) {
            this.saving = true;

            try {
                await api(`/page/blocks/${id}`, { method: 'DELETE' });
                this.userBlocks = this.userBlocks.filter((block) => block.id !== id);
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر حذف البلوك.';
                throw error;
            } finally {
                this.saving = false;
            }
        },

        async fetchBlock(id) {
            this.editingLoading = true;
            this.editingError = null;

            try {
                const payload = await api(`/page/blocks/${id}`);
                this.editing = payload?.data ?? null;

                return this.editing;
            } catch (error) {
                this.editingError = error instanceof ApiError ? error.message : 'تعذر تحميل إعدادات البلوك.';
                this.editing = null;
                throw error;
            } finally {
                this.editingLoading = false;
            }
        },

        async updateBlock(id, body) {
            this.saving = true;

            try {
                const options = body instanceof FormData
                    ? { method: 'POST', body }
                    : { method: 'PUT', body };

                const payload = await api(`/page/blocks/${id}`, options);
                this.editing = payload?.data ?? this.editing;
                await this.fetchStructure({ force: true });

                return this.editing;
            } catch (error) {
                this.editingError = error instanceof ApiError ? error.message : 'تعذر حفظ إعدادات البلوك.';
                throw error;
            } finally {
                this.saving = false;
            }
        },

        clearEditing() {
            this.editing = null;
            this.editingError = null;
        },
    },
});
