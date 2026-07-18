import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

function emptyStructure() {
    return {
        top_blocks: [],
        cta_block: null,
        user_blocks: [],
        bottom_blocks: [],
        float_links_block: null,
        block_types: [],
        block_link_editor: null,
    };
}

export const usePageStructureStore = defineStore('pageStructure', {
    state: () => ({
        topBlocks: [],
        ctaBlock: null,
        userBlocks: [],
        bottomBlocks: [],
        floatLinksBlock: null,
        blockTypes: [],
        blockLinkEditor: null,
        loading: false,
        loaded: false,
        error: null,
        saving: false,
        editing: null,
        editingLoading: false,
        editingError: null,
        creating: false,
    }),

    getters: {
        userBlocksEmpty: (state) => state.loaded && !state.loading && state.userBlocks.length === 0,
    },

    actions: {
        applyStructure(payload) {
            const data = payload?.data ?? payload ?? emptyStructure();
            this.topBlocks = Array.isArray(data.top_blocks) ? data.top_blocks : [];
            this.ctaBlock = data.cta_block ?? null;
            this.userBlocks = Array.isArray(data.user_blocks) ? data.user_blocks : [];
            this.bottomBlocks = Array.isArray(data.bottom_blocks) ? data.bottom_blocks : [];
            this.floatLinksBlock = data.float_links_block ?? null;
            this.blockTypes = Array.isArray(data.block_types) ? data.block_types : [];
            this.blockLinkEditor = data.block_link_editor ?? null;
            this.loaded = true;
        },

        setCtaEditor(editor) {
            if (!this.ctaBlock) {
                return;
            }

            this.ctaBlock = {
                ...this.ctaBlock,
                editor: editor ?? this.ctaBlock.editor,
            };
        },

        setFloatLinksEditor(editor) {
            if (!this.floatLinksBlock) {
                return;
            }

            this.floatLinksBlock = {
                ...this.floatLinksBlock,
                editor: editor ?? this.floatLinksBlock.editor,
            };
        },

        upsertUserBlock(block) {
            if (!block?.id) {
                return;
            }

            const index = this.userBlocks.findIndex((item) => item.id === block.id);

            if (index === -1) {
                this.userBlocks = [...this.userBlocks, block];

                return;
            }

            this.userBlocks[index] = { ...this.userBlocks[index], ...block };
        },

        beginCreateBlockLink() {
            const editor = this.blockLinkEditor ?? {
                type: 'block-link',
                title: '',
                description: '',
                url: '',
                link_type: '',
                content_id: null,
                selected_content_title: '',
                link_type_options: [],
                link_type_picker_options: [],
            };

            this.creating = true;
            this.editingLoading = false;
            this.editingError = null;
            this.editing = {
                block: {
                    id: null,
                    type: 'block-link',
                    title: 'رابط جديد',
                },
                editor: {
                    ...editor,
                    title: '',
                    description: '',
                    url: '',
                    link_type: '',
                    content_id: null,
                    selected_content_title: '',
                },
            };
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
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل أقسام الصفحة.';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        async createBlock(type, fields = {}) {
            this.saving = true;
            this.error = null;

            try {
                let body;

                if (fields instanceof FormData) {
                    body = fields;
                    if (!body.has('type')) {
                        body.append('type', type);
                    }
                } else {
                    body = { type, ...fields };
                }

                const payload = await api('/page/blocks', {
                    method: 'POST',
                    body,
                });

                const data = payload?.data ?? null;
                const block = data?.block ?? null;

                if (block) {
                    this.upsertUserBlock(block);
                }

                this.editing = data;
                this.creating = false;

                return data;
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
            this.creating = false;
            this.editing = null;
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

                const block = this.editing?.block;

                if (block) {
                    this.upsertUserBlock(block);
                }

                return this.editing;
            } catch (error) {
                throw error;
            } finally {
                this.saving = false;
            }
        },

        clearEditing() {
            this.editing = null;
            this.editingError = null;
            this.creating = false;
        },
    },
});
