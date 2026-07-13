import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

function mapTab(tab) {
    const sellable = Boolean(tab.sellable ?? tab.content_type?.sellable);

    return {
        id: tab.id ?? `content-${tab.slug}`,
        slug: tab.slug,
        label: tab.label ?? tab.name,
        icon: tab.icon,
        icon_url: tab.icon_url,
        description: tab.description ?? '',
        type: 'content',
        color: tab.color ?? tab.content_type?.color,
        sellable,
        content_type: tab.content_type ?? {
            slug: tab.slug,
            name: tab.label ?? tab.name,
            description: tab.description ?? '',
            icon: tab.icon,
            color: tab.color,
            sellable,
        },
    };
}

function mapCatalogOption(option) {
    return {
        slug: option.slug,
        name: option.name,
        description: option.description ?? '',
        icon: option.icon,
        icon_url: option.icon_url,
        color: option.color,
        enabled: Boolean(option.enabled),
    };
}

export const useContentTypesStore = defineStore('contentTypes', {
    state: () => ({
        tabs: [],
        loading: false,
        loaded: false,
        error: null,
        catalogOptions: [],
        catalogEnabled: [],
        catalogLoading: false,
        catalogSaving: false,
        catalogLoaded: false,
        catalogError: null,
    }),

    getters: {
        contentTabs: (state) => state.tabs.filter((tab) => !tab.sellable),
        sellableTabs: (state) => state.tabs.filter((tab) => tab.sellable),
        contentTypes: (state) => state.tabs.map((tab) => tab.content_type ?? {
            slug: tab.slug,
            name: tab.label,
            description: tab.description,
            icon: tab.icon,
            color: tab.color,
            sellable: tab.sellable,
        }),
        bySlug: (state) => (slug) => state.tabs.find((tab) => tab.slug === slug) ?? null,
    },

    actions: {
        async fetchContentTypes({ force = false } = {}) {
            if (this.loading) {
                return;
            }

            if (this.loaded && !force) {
                return;
            }

            this.loading = true;
            this.error = null;

            try {
                const payload = await api('/page/content-types');
                const rows = Array.isArray(payload?.data) ? payload.data : [];

                this.tabs = rows.map(mapTab);
                this.loaded = true;
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل أنواع المحتوى.';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.loading = false;
            }
        },

        async fetchCatalogSections({ force = false } = {}) {
            if (this.catalogLoading) {
                return;
            }

            if (this.catalogLoaded && !force) {
                return;
            }

            this.catalogLoading = true;
            this.catalogError = null;

            try {
                const payload = await api('/page/catalog-sections');
                const rows = Array.isArray(payload?.data) ? payload.data : [];

                this.catalogOptions = rows.map(mapCatalogOption);
                this.catalogEnabled = Array.isArray(payload?.enabled) ? payload.enabled : [];
                this.catalogLoaded = true;
            } catch (error) {
                this.catalogError = error instanceof ApiError
                    ? error.message
                    : 'تعذر تحميل أقسام الكتالوج.';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.catalogLoading = false;
            }
        },

        async saveCatalogSections(enabled) {
            this.catalogSaving = true;
            this.catalogError = null;

            try {
                const payload = await api('/page/catalog-sections', {
                    method: 'PUT',
                    body: { enabled },
                });

                const rows = Array.isArray(payload?.data) ? payload.data : [];

                this.catalogOptions = rows.map(mapCatalogOption);
                this.catalogEnabled = Array.isArray(payload?.enabled) ? payload.enabled : enabled;
                this.catalogLoaded = true;

                await this.fetchContentTypes({ force: true });

                return payload;
            } catch (error) {
                this.catalogError = error instanceof ApiError
                    ? error.message
                    : 'تعذر حفظ أقسام الكتالوج.';

                if (error instanceof ApiError && error.status === 401) {
                    window.location.href = '/login';
                }

                throw error;
            } finally {
                this.catalogSaving = false;
            }
        },
    },
});
