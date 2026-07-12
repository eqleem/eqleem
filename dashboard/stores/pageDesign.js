import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

export const usePageDesignStore = defineStore('pageDesign', {
    state: () => ({
        themes: [],
        selectedThemeId: null,
        tenantThemeId: null,
        selectedTheme: null,
        optionsSchema: {},
        options: {},
        optionPreviews: {},
        loading: false,
        loaded: false,
        error: null,
        saving: false,
        activating: false,
        message: null,
    }),

    getters: {
        schemaEntries: (state) => Object.entries(state.optionsSchema ?? {}),
        hasOptions: (state) => Object.keys(state.optionsSchema ?? {}).length > 0,
        themesEmpty: (state) => state.loaded && !state.loading && state.themes.length === 0,
    },

    actions: {
        applyPayload(payload) {
            const data = payload?.data ?? payload ?? {};

            this.themes = Array.isArray(data.themes) ? data.themes : [];
            this.selectedThemeId = data.selected_theme_id ?? null;
            this.tenantThemeId = data.tenant_theme_id ?? null;
            this.selectedTheme = data.selected_theme ?? null;
            this.optionsSchema = data.options_schema && typeof data.options_schema === 'object'
                ? data.options_schema
                : {};
            this.options = data.options && typeof data.options === 'object' ? { ...data.options } : {};
            this.optionPreviews = data.option_previews && typeof data.option_previews === 'object'
                ? { ...data.option_previews }
                : {};
            this.loaded = true;
        },

        async fetchDesign({ themeId = null, force = false } = {}) {
            if (this.loading) {
                return;
            }

            if (this.loaded && !force && themeId === null) {
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const query = themeId ? `?theme_id=${themeId}` : '';
                this.applyPayload(await api(`/page/design${query}`));
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل تصميم الصفحة.';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        async selectTheme(themeId) {
            if (themeId === this.selectedThemeId && this.loaded) {
                return;
            }

            await this.fetchDesign({ themeId, force: true });
        },

        async setDefaultTheme() {
            if (!this.selectedThemeId) {
                return;
            }

            this.activating = true;
            this.error = null;
            this.message = null;

            try {
                const payload = await api('/page/design/theme', {
                    method: 'PUT',
                    body: { theme_id: this.selectedThemeId },
                });

                this.applyPayload(payload);
                this.message = payload?.message ?? 'تم تعيين القالب الافتراضي بنجاح.';
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تعيين القالب.';
                throw error;
            } finally {
                this.activating = false;
            }
        },

        async saveOptions(uploads = {}) {
            if (!this.selectedThemeId) {
                return { ok: false };
            }

            this.saving = true;
            this.error = null;
            this.message = null;

            try {
                const body = new FormData();
                body.append('theme_id', String(this.selectedThemeId));

                const uploadKeys = new Set(
                    Object.entries(uploads)
                        .filter(([, file]) => file instanceof Blob)
                        .map(([key]) => key),
                );

                Object.entries(this.options).forEach(([key, value]) => {
                    if (value === undefined || value === null) {
                        return;
                    }

                    // Don't send the old path for fields that have a fresh file upload.
                    if (uploadKeys.has(key)) {
                        return;
                    }

                    // Don't wipe existing images with an empty string.
                    if (value === '' && (this.optionsSchema?.[key]?.type === 'upload-single-image')) {
                        return;
                    }

                    body.append(`options[${key}]`, String(value));
                });

                Object.entries(uploads).forEach(([key, file]) => {
                    if (file instanceof File) {
                        body.append(`uploads[${key}]`, file);
                    } else if (file instanceof Blob) {
                        body.append(`uploads[${key}]`, file, `${key}.jpg`);
                    }
                });

                const payload = await api('/page/design/options', {
                    method: 'POST',
                    body,
                });

                this.applyPayload(payload);
                this.message = payload?.message ?? 'تم حفظ الإعدادات بنجاح.';

                return { ok: true };
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر حفظ خيارات القالب.';

                return {
                    ok: false,
                    errors: error instanceof ApiError ? error.errors : {},
                    message: this.error,
                };
            } finally {
                this.saving = false;
            }
        },
    },
});
