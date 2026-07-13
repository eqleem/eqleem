import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';

const emptyForms = () => ({
    business: { industry: '', name: '', bio: '', logo: '', brand_mark: null },
    contact: {
        phone: '',
        email: '',
        whatsapp: '',
        country: '',
        city: '',
        social_links: [],
    },
    identity: {
        theme_id: null,
        primary_color: 'blue',
        logo_radius: 'rounded-full',
        font_family: 'sarmady',
    },
    catalog: { enabled: [] },
    orders: {
        payment_active: false,
        shipping_active: false,
        verification_done: false,
    },
});

export const useOnboardingStore = defineStore('onboarding', {
    state: () => ({
        percentage: 0,
        completedSteps: 0,
        totalSteps: 5,
        currentStep: 'business',
        completed: false,
        steps: [],
        forms: emptyForms(),
        industries: {},
        socialNetworks: {},
        fonts: {},
        colorOptions: [],
        radiusOptions: {},
        catalogOptions: [],
        loading: false,
        loaded: false,
        saving: false,
        error: null,
        message: null,
        dismissed: false,
    }),

    getters: {
        shouldShow: (state) => state.loaded,
        showCompletedBadge: (state) => state.loaded && state.completed,
    },

    actions: {
        applyPayload(payload) {
            const data = payload?.data ?? payload;

            this.percentage = Number(data.percentage ?? 0);
            this.completedSteps = Number(data.completed_steps ?? 0);
            this.totalSteps = Number(data.total_steps ?? 5);
            this.currentStep = data.current_step ?? 'business';
            this.completed = Boolean(data.completed);
            this.steps = Array.isArray(data.steps) ? data.steps : [];
            this.forms = {
                business: {
                    industry: data.forms?.business?.industry ?? '',
                    name: data.forms?.business?.name ?? '',
                    bio: data.forms?.business?.bio ?? '',
                    logo: data.forms?.business?.logo ?? '',
                    brand_mark: data.forms?.business?.brand_mark ?? null,
                },
                contact: {
                    phone: data.forms?.contact?.phone ?? '',
                    email: data.forms?.contact?.email ?? '',
                    whatsapp: data.forms?.contact?.whatsapp ?? '',
                    country: data.forms?.contact?.country || 'SA',
                    city: data.forms?.contact?.city ?? '',
                    social_links: Array.isArray(data.forms?.contact?.social_links)
                        ? data.forms.contact.social_links
                        : [],
                },
                identity: {
                    theme_id: data.forms?.identity?.theme_id ?? null,
                    primary_color: data.forms?.identity?.primary_color ?? 'blue',
                    logo_radius: data.forms?.identity?.logo_radius ?? 'rounded-full',
                    font_family: data.forms?.identity?.font_family ?? 'sarmady',
                },
                catalog: {
                    enabled: Array.isArray(data.forms?.catalog?.enabled)
                        ? data.forms.catalog.enabled
                        : [],
                },
                orders: {
                    payment_active: Boolean(data.forms?.orders?.payment_active),
                    shipping_active: Boolean(data.forms?.orders?.shipping_active),
                    verification_done: Boolean(data.forms?.orders?.verification_done),
                },
            };
            this.industries = data.industries ?? {};
            this.socialNetworks = data.social_networks ?? {};
            this.fonts = data.fonts ?? {};
            this.colorOptions = Array.isArray(data.color_options) ? data.color_options : [];
            this.radiusOptions = data.radius_options ?? {};
            this.catalogOptions = Array.isArray(data.catalog_options) ? data.catalog_options : [];
            this.message = payload?.message ?? null;
            this.loaded = true;
        },

        async fetchOnboarding({ force = false, quiet = false } = {}) {
            if (!force && this.loaded) {
                return this;
            }

            if (!quiet) {
                this.loading = true;
            }

            this.error = null;

            try {
                this.applyPayload(await api('/dashboard/onboarding'));
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل إعداد الحساب';
            } finally {
                if (!quiet) {
                    this.loading = false;
                }
            }

            return this;
        },

        async saveBusiness(body) {
            return this.save('/dashboard/onboarding/business', 'POST', body);
        },

        async saveContact(body) {
            return this.save('/dashboard/onboarding/contact', 'PUT', body);
        },

        async saveIdentity(body) {
            return this.save('/dashboard/onboarding/identity', 'PUT', body);
        },

        async saveCatalog(body) {
            return this.save('/dashboard/onboarding/catalog', 'PUT', body);
        },

        async refreshQuiet() {
            return this.fetchOnboarding({ force: true, quiet: true });
        },

        dismiss() {
            this.dismissed = true;
        },

        async save(path, method, body) {
            this.saving = true;
            this.error = null;
            this.message = null;

            try {
                const payload = await api(path, { method, body });
                this.applyPayload(payload);

                return { ok: true, errors: {}, currentStep: this.currentStep };
            } catch (error) {
                if (error instanceof ApiError) {
                    this.error = error.message;

                    return { ok: false, errors: error.errors ?? {}, message: error.message };
                }

                this.error = 'تعذر الحفظ';

                return { ok: false, errors: {}, message: this.error };
            } finally {
                this.saving = false;
            }
        },
    },
});
