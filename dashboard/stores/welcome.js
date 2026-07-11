import { defineStore } from 'pinia';
import { api, ApiError } from '../lib/api.js';
import { closeModal, openModal } from '../lib/modal.js';

const emptyForms = () => ({
    basic_info: { name: '', bio: '', logo: '' },
    contact: { phone: '', email: '', country: '', city: '' },
    social_networks: {},
});

export const useWelcomeStore = defineStore('welcome', {
    state: () => ({
        greeting: '',
        userName: '',
        pageUrl: '',
        shareText: '',
        percentage: 0,
        completedSteps: 0,
        totalSteps: 0,
        steps: [],
        nextStep: null,
        forms: emptyForms(),
        loading: false,
        loaded: false,
        saving: false,
        error: null,
        message: null,
    }),

    actions: {
        applyPayload(payload) {
            const data = payload?.data ?? payload;

            this.greeting = data.greeting ?? '';
            this.userName = data.user_name ?? '';
            this.pageUrl = data.page_url ?? '';
            this.shareText = data.share_text ?? '';
            this.percentage = Number(data.percentage ?? 0);
            this.completedSteps = Number(data.completed_steps ?? 0);
            this.totalSteps = Number(data.total_steps ?? 0);
            this.steps = Array.isArray(data.steps) ? data.steps : [];
            this.nextStep = data.next_step ?? null;
            this.forms = {
                basic_info: {
                    name: data.forms?.basic_info?.name ?? '',
                    bio: data.forms?.basic_info?.bio ?? '',
                    logo: data.forms?.basic_info?.logo ?? '',
                },
                contact: {
                    phone: data.forms?.contact?.phone ?? '',
                    email: data.forms?.contact?.email ?? '',
                    country: data.forms?.contact?.country ?? '',
                    city: data.forms?.contact?.city ?? '',
                },
                social_networks: data.forms?.social_networks ?? {},
            };
            this.message = payload?.message ?? null;
            this.loaded = true;
        },

        async fetchWelcome({ force = false, quiet = false } = {}) {
            if (!force && this.loaded) {
                return this;
            }

            if (!quiet) {
                this.loading = true;
            }

            this.error = null;

            try {
                const payload = await api('/dashboard/welcome');
                this.applyPayload(payload);
            } catch (error) {
                this.error = error instanceof ApiError ? error.message : 'تعذر تحميل الترحيب';
            } finally {
                if (!quiet) {
                    this.loading = false;
                }
            }

            return this;
        },

        /**
         * Mirrors Livewire `page-completion-updated` + `closemodal`, then opens the next incomplete step.
         */
        async afterStepSaved(closedModal) {
            closeModal(closedModal);
            await this.fetchWelcome({ force: true, quiet: true });

            const nextModal = this.nextStep?.modal;

            if (!nextModal || nextModal === closedModal) {
                return;
            }

            openModal(nextModal);
        },

        async saveBasicInfo(body) {
            return this.save('/dashboard/welcome/basic-info', 'POST', body);
        },

        async saveContact(body) {
            return this.save('/dashboard/welcome/contact', 'PUT', body);
        },

        async addSocialLink(body) {
            return this.save('/dashboard/welcome/social', 'POST', body);
        },

        async save(path, method, body) {
            this.saving = true;
            this.error = null;
            this.message = null;

            try {
                const payload = await api(path, { method, body });
                this.applyPayload(payload);

                return { ok: true, errors: {}, nextStep: this.nextStep };
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
