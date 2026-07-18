<script setup>
import {
    computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch,
} from 'vue';
import { storeToRefs } from 'pinia';
import Modal from '../ui/Modal.vue';
import Input from '../ui/Input.vue';
import Textarea from '../ui/Textarea.vue';
import Select from '../ui/Select.vue';
import CountrySelect from '../ui/CountrySelect.vue';
import SearchableSelect from '../ui/SearchableSelect.vue';
import BrandMarkField from '../ui/BrandMarkField.vue';
import PickerColor from '../ui/PickerColor.vue';
import UploadCover from '../ui/UploadCover.vue';
import Button from '../ui/Button.vue';
import OnboardingPagePreview from './OnboardingPagePreview.vue';
import CompletionVerification from './CompletionVerification.vue';
import SettingsPaymentOptions from '../../pages/settings/PaymentOptions.vue';
import SettingsShippingOptions from '../../pages/settings/ShippingOptions.vue';
import { useOnboardingStore } from '../../stores/onboarding.js';
import { useSession, updateTenant } from '../../stores/session.js';
import { openModal, closeModal } from '../../lib/modal.js';
import { notifySuccess, notifyError } from '../../lib/notify.js';
import { defaultCountryCode } from '../../data/countries.js';
import { COVER_CLEAR } from '../../data/coverPresets.js';
import { appDomain } from '../../data/settings.js';

const store = useOnboardingStore();
const {
    percentage,
    completedSteps,
    totalSteps,
    steps,
    forms,
    industryOptions,
    actionOptions,
    socialNetworks,
    colorOptions,
    catalogOptions,
    saving,
    completed,
    shouldShow,
    pageUrl,
    loading,
    loaded,
} = storeToRefs(store);

const { tenant, user } = useSession();

const activeKey = ref('business');
const showSuccess = ref(false);
const errors = reactive({});
const confettiPieces = ref([]);
const autosaving = ref(false);
const wizardRoot = ref(null);
const footerSentinel = ref(null);
const footerFixed = ref(false);
const footerStyle = ref({});
let footerObserver = null;

const brandMark = ref({
    type: null,
    value: '',
    color: '',
    url: null,
    file: null,
});

const business = reactive({ industry: '', name: '', bio: '' });
const contact = reactive({
    phone: '',
    email: '',
    whatsapp: '',
    country: defaultCountryCode,
    city: '',
});
const whatsappSameAsPhone = ref(true);
const social = reactive({ network: 'twitter', username: '' });
const identity = reactive({
    handle: '',
    primary_color: 'blue',
    header_image: '',
    header_image_url: '',
    header_image_position: 50,
});
const headerFile = ref(null);
const goal = reactive({
    primary_action_type: '',
    secondary_action_type: '',
});
const enabledCatalog = ref([]);

let autosaveTimer = null;
let syncingFromStore = false;
let hydrated = false;

const stepIndex = computed(() => steps.value.findIndex((step) => step.key === activeKey.value));
const activeStep = computed(() => steps.value.find((step) => step.key === activeKey.value) ?? null);
const isLastStep = computed(() => stepIndex.value === steps.value.length - 1);
const canGoBack = computed(() => stepIndex.value > 0);

const primaryActionLabel = computed(() => (
    actionOptions.value.find((item) => item.type === goal.primary_action_type)?.label || ''
));

const secondaryActionLabel = computed(() => (
    actionOptions.value.find((item) => item.type === goal.secondary_action_type)?.label || ''
));

const primaryActionSelectOptions = computed(() => (
    actionOptions.value.map((item) => ({
        id: item.type,
        label: item.label,
        description: item.description,
        icon: item.icon,
    }))
));

const secondaryActionSelectOptions = computed(() => (
    actionOptions.value
        .filter((item) => item.type !== goal.primary_action_type)
        .map((item) => ({
            id: item.type,
            label: item.label,
            description: item.description,
            icon: item.icon,
        }))
));

const previewSocialLinks = computed(() => {
    if (social.username.trim()) {
        return [{ network: social.network || 'twitter', username: social.username.trim() }];
    }

    return forms.value.contact?.social_links ?? [];
});

const handlePrefix = computed(() => `https://${appDomain}/`);

const circularProgressStyle = computed(() => ({
    background: `conic-gradient(#fbbf24 ${percentage.value * 3.6}deg, rgba(255,255,255,0.22) 0deg)`,
}));

const progressGradient = computed(() => (
    'linear-gradient(90deg, #6366f1 0%, #8b5cf6 45%, #ec4899 100%)'
));

function hasBrandMark(mark) {
    if (!mark || typeof mark !== 'object') {
        return false;
    }

    if (mark.type === 'image') {
        return Boolean(mark.file || mark.url);
    }

    if (mark.type === 'emoji' || mark.type === 'icon') {
        return Boolean(mark.value);
    }

    return false;
}

const canContinue = computed(() => {
    switch (activeKey.value) {
        case 'business':
            return Boolean(
                business.industry
                && business.name.trim().length >= 2
                && business.bio.trim()
                && hasBrandMark(brandMark.value),
            );
        case 'contact':
            return Boolean(contact.phone.trim() && contact.email.trim());
        case 'identity':
            return Boolean(identity.handle.trim().length >= 2 && identity.primary_color);
        case 'goal':
            return Boolean(goal.primary_action_type);
        case 'catalog':
            return enabledCatalog.value.length > 0;
        case 'orders':
            return Boolean(forms.value.orders.payment_active && forms.value.orders.verification_done);
        default:
            return false;
    }
});

function brandMarkFromPayload(data) {
    const mark = data?.brand_mark;

    if (mark && typeof mark === 'object' && mark.type) {
        return {
            type: mark.type,
            value: mark.value ?? '',
            color: mark.color ?? '',
            url: mark.type === 'image' ? (mark.url || data?.logo || null) : null,
            file: null,
        };
    }

    if (data?.logo) {
        return {
            type: 'image',
            value: '',
            color: '',
            url: data.logo,
            file: null,
        };
    }

    return {
        type: null,
        value: '',
        color: '',
        url: null,
        file: null,
    };
}

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
}

function flattenErrors(serverErrors = {}) {
    const mapped = {};

    Object.entries(serverErrors).forEach(([key, value]) => {
        mapped[key] = Array.isArray(value) ? value[0] : value;
    });

    return mapped;
}

function isUnlocked(step) {
    return completed.value || step.unlocked || step.done;
}

function selectStep(step) {
    if (!isUnlocked(step) && step.key !== activeKey.value) {
        return;
    }

    activeKey.value = step.key;
}

function goBack() {
    if (!canGoBack.value) {
        return;
    }

    const prev = steps.value[stepIndex.value - 1];

    if (prev) {
        activeKey.value = prev.key;
    }
}

function advanceToNext() {
    const next = steps.value[stepIndex.value + 1];

    if (next) {
        activeKey.value = next.key;
    }
}

function toggleCatalog(slug) {
    if (enabledCatalog.value.includes(slug)) {
        enabledCatalog.value = enabledCatalog.value.filter((item) => item !== slug);
        return;
    }

    enabledCatalog.value = [...enabledCatalog.value, slug];
    scheduleAutosave();
}

function onPhoneInput(value) {
    contact.phone = value;

    if (whatsappSameAsPhone.value) {
        contact.whatsapp = value;
    }

    scheduleAutosave();
}

function fireConfetti() {
    confettiPieces.value = Array.from({ length: 48 }, (_, index) => ({
        id: index,
        left: Math.random() * 100,
        delay: Math.random() * 0.6,
        duration: 1.8 + Math.random() * 1.4,
        color: ['#f43f5e', '#3b82f6', '#f59e0b', '#10b981', '#8b5cf6', '#ec4899'][index % 6],
        rotate: Math.random() * 360,
        size: 6 + Math.random() * 8,
    }));

    setTimeout(() => {
        confettiPieces.value = [];
    }, 3200);
}

function syncFromStore() {
    syncingFromStore = true;

    const value = forms.value;

    business.industry = value.business?.industry ?? '';
    business.name = value.business?.name ?? '';
    business.bio = value.business?.bio ?? '';

    if (!brandMark.value?.file) {
        brandMark.value = brandMarkFromPayload(value.business);
    }

    contact.phone = value.contact?.phone || user.value?.phone || '';
    contact.email = value.contact?.email || user.value?.email || '';
    contact.whatsapp = value.contact?.whatsapp || contact.phone;
    const country = value.contact?.country ?? '';
    contact.country = /^[A-Za-z]{2}$/.test(country) ? country.toUpperCase() : defaultCountryCode;
    contact.city = value.contact?.city ?? '';

    const phone = contact.phone.trim();
    const whatsapp = contact.whatsapp.trim();
    whatsappSameAsPhone.value = !whatsapp || whatsapp === phone;

    const firstSocial = value.contact?.social_links?.[0];
    social.network = firstSocial?.network || 'twitter';
    social.username = firstSocial?.username || '';

    identity.handle = value.identity?.handle || tenant.value?.handle || '';
    identity.primary_color = value.identity?.primary_color ?? 'blue';
    identity.header_image = value.identity?.header_image ?? '';
    identity.header_image_url = value.identity?.header_image_url ?? '';
    identity.header_image_position = value.identity?.header_image_position ?? 50;

    goal.primary_action_type = value.goal?.primary_action_type ?? '';
    goal.secondary_action_type = value.goal?.secondary_action_type ?? '';

    const enabled = value.catalog?.enabled ?? [];
    enabledCatalog.value = enabled.length
        ? [...enabled]
        : catalogOptions.value.filter((item) => item.enabled).map((item) => item.slug);

    if (!activeKey.value || !steps.value.some((step) => step.key === activeKey.value)) {
        activeKey.value = store.currentStep || steps.value[0]?.key || 'business';
    }

    showSuccess.value = completed.value;

    nextTick(() => {
        syncingFromStore = false;
        hydrated = true;
    });
}

function appendBrandMark(body) {
    const mark = brandMark.value ?? {};

    if (mark.type === 'image' && mark.file) {
        body.append('logo', mark.file);
        body.append('brand_mark_type', 'image');
    } else if (mark.type === 'emoji' || mark.type === 'icon') {
        body.append('brand_mark_type', mark.type);
        body.append('brand_mark_value', mark.value ?? '');
        if (mark.type === 'icon') {
            body.append('brand_mark_color', mark.color ?? '');
        }
    } else if (mark.type === 'none') {
        body.append('brand_mark_type', 'none');
        body.append('remove_logo', '1');
    }
}

async function autosaveBusiness() {
    if (!business.name.trim() && !business.bio.trim() && !business.industry && !hasBrandMark(brandMark.value)) {
        return { ok: true };
    }

    const body = new FormData();
    body.append('partial', '1');

    if (business.industry) {
        body.append('industry', business.industry);
    }

    if (business.name.trim()) {
        body.append('name', business.name.trim());
    }

    if (business.bio.trim()) {
        body.append('bio', business.bio.trim());
    }

    appendBrandMark(body);

    return store.saveBusiness(body);
}

async function autosaveContact() {
    if (!contact.phone.trim() && !contact.email.trim() && !contact.city.trim() && !social.username.trim()) {
        return { ok: true };
    }

    const payload = {
        partial: true,
        phone: contact.phone.trim() || undefined,
        email: contact.email.trim() || undefined,
        whatsapp_same_as_phone: whatsappSameAsPhone.value,
        whatsapp: whatsappSameAsPhone.value ? contact.phone.trim() : contact.whatsapp.trim(),
        country: contact.country || undefined,
        city: contact.city.trim(),
        social_links: social.username.trim()
            ? [{ network: social.network || 'twitter', username: social.username.trim() }]
            : [],
    };

    return store.saveContact(payload);
}

async function autosaveIdentity() {
    const body = new FormData();
    body.append('partial', '1');

    if (identity.handle.trim()) {
        body.append('handle', identity.handle.trim());
    }

    if (identity.primary_color) {
        body.append('primary_color', identity.primary_color);
    }

    body.append('header_image_position', String(identity.header_image_position ?? 50));

    if (headerFile.value) {
        body.append('header_image_file', headerFile.value);
    } else if (identity.header_image === COVER_CLEAR) {
        body.append('clear_header_image', '1');
    } else if (identity.header_image) {
        body.append('header_image', identity.header_image);
    }

    return store.saveIdentity(body);
}

async function autosaveGoal() {
    if (!goal.primary_action_type && !goal.secondary_action_type) {
        return { ok: true };
    }

    return store.saveGoal({
        partial: true,
        primary_action_type: goal.primary_action_type || undefined,
        secondary_action_type: goal.secondary_action_type || null,
    });
}

async function autosaveCatalog() {
    return store.saveCatalog({
        partial: true,
        enabled: enabledCatalog.value,
    });
}

async function runAutosave() {
    if (!hydrated || syncingFromStore || showSuccess.value) {
        return;
    }

    autosaving.value = true;

    try {
        let result = { ok: true };

        switch (activeKey.value) {
            case 'business':
                result = await autosaveBusiness();
                break;
            case 'contact':
                result = await autosaveContact();
                break;
            case 'identity':
                result = await autosaveIdentity();
                if (result.ok) {
                    headerFile.value = null;
                }
                break;
            case 'goal':
                result = await autosaveGoal();
                break;
            case 'catalog':
                result = await autosaveCatalog();
                break;
            default:
                break;
        }

        if (result.ok && activeKey.value === 'business' && tenant.value) {
            updateTenant({
                ...tenant.value,
                name: business.name.trim() || tenant.value.name,
                logo: store.forms.business.logo || tenant.value.logo,
            });
        }

        if (result.ok && activeKey.value === 'identity' && tenant.value) {
            updateTenant({
                ...tenant.value,
                handle: identity.handle.trim() || tenant.value.handle,
                url: store.pageUrl || tenant.value.url,
            });
        }
    } finally {
        autosaving.value = false;
    }
}

function scheduleAutosave() {
    if (!hydrated || syncingFromStore) {
        return;
    }

    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(() => {
        runAutosave();
    }, 700);
}

async function continueStep() {
    if (!canContinue.value || saving.value) {
        return;
    }

    clearErrors();
    clearTimeout(autosaveTimer);

    if (activeKey.value === 'business') {
        const body = new FormData();
        body.append('industry', business.industry);
        body.append('name', business.name.trim());
        body.append('bio', business.bio.trim());
        appendBrandMark(body);

        const result = await store.saveBusiness(body);

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        brandMark.value = brandMarkFromPayload(store.forms.business);

        if (tenant.value) {
            updateTenant({
                ...tenant.value,
                name: business.name.trim(),
                logo: store.forms.business.logo || tenant.value.logo,
            });
        }

        advanceToNext();
        return;
    }

    if (activeKey.value === 'contact') {
        const result = await store.saveContact({
            phone: contact.phone.trim(),
            email: contact.email.trim(),
            whatsapp_same_as_phone: whatsappSameAsPhone.value,
            whatsapp: whatsappSameAsPhone.value ? contact.phone.trim() : contact.whatsapp.trim(),
            country: contact.country,
            city: contact.city.trim(),
            social_links: social.username.trim()
                ? [{ network: social.network || 'twitter', username: social.username.trim() }]
                : [],
        });

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        advanceToNext();
        return;
    }

    if (activeKey.value === 'identity') {
        const body = new FormData();
        body.append('handle', identity.handle.trim());
        body.append('primary_color', identity.primary_color);
        body.append('header_image_position', String(identity.header_image_position ?? 50));

        if (headerFile.value) {
            body.append('header_image_file', headerFile.value);
        } else if (identity.header_image === COVER_CLEAR) {
            body.append('clear_header_image', '1');
        } else if (identity.header_image) {
            body.append('header_image', identity.header_image);
        }

        const result = await store.saveIdentity(body);

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        headerFile.value = null;
        identity.header_image = store.forms.identity.header_image;
        identity.header_image_url = store.forms.identity.header_image_url;

        if (tenant.value) {
            updateTenant({
                ...tenant.value,
                handle: identity.handle.trim(),
                url: store.pageUrl || tenant.value.url,
            });
        }

        advanceToNext();
        return;
    }

    if (activeKey.value === 'goal') {
        const result = await store.saveGoal({
            primary_action_type: goal.primary_action_type,
            secondary_action_type: goal.secondary_action_type || null,
        });

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        advanceToNext();
        return;
    }

    if (activeKey.value === 'catalog') {
        const result = await store.saveCatalog({ enabled: enabledCatalog.value });

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        advanceToNext();
        return;
    }

    if (activeKey.value === 'orders') {
        await store.refreshQuiet();

        if (!forms.value.orders.payment_active || !forms.value.orders.verification_done) {
            notifyError('فعّل وسيلة دفع وأكمل التوثيق للمتابعة');
            return;
        }

        showSuccess.value = true;
        fireConfetti();
        notifySuccess('صفحتك جاهزة لاستقبال العملاء 🎉');
    }
}

async function dismissWizard() {
    const result = await store.dismissWizard();

    if (!result.ok) {
        notifyError(result.message ?? 'تعذر إنهاء الإعداد');
        return;
    }

    notifySuccess('تم إنهاء الإعداد');
}

async function copyLink() {
    try {
        await navigator.clipboard.writeText(pageUrl.value || `https://${identity.handle}.${'eqleem.test'}`);
        notifySuccess('تم نسخ الرابط');
    } catch {
        notifyError('تعذر نسخ الرابط');
    }
}

function shareNative() {
    const url = pageUrl.value;

    if (navigator.share && url) {
        navigator.share({
            title: business.name || 'صفحتي',
            text: business.bio || '',
            url,
        }).catch(() => {});
        return;
    }

    copyLink();
}

function openOrdersModal(name) {
    openModal(name);
}

function closeOrdersSetupModal(name) {
    closeModal(name);
    store.refreshQuiet();
}

function onVerificationSaved() {
    closeModal('new-onboarding-verification');
    store.refreshQuiet();
}

function onOrdersModalClosed(event) {
    const modal = event.detail?.modal;

    if (!modal || ['new-onboarding-payment', 'new-onboarding-shipping', 'new-onboarding-verification'].includes(modal)) {
        store.refreshQuiet();
    }
}

watch(() => [
    forms.value.orders.payment_active,
    forms.value.orders.shipping_active,
    forms.value.orders.verification_done,
    completed.value,
], () => {
    if (!hydrated) {
        return;
    }

    if (completed.value) {
        showSuccess.value = true;
    }
});

watch([
    () => business.name,
    () => business.bio,
    () => business.industry,
    brandMark,
], () => scheduleAutosave(), { deep: true });

watch([
    () => contact.email,
    () => contact.whatsapp,
    () => contact.country,
    () => contact.city,
    () => social.network,
    () => social.username,
    whatsappSameAsPhone,
], () => scheduleAutosave());

watch([
    () => identity.handle,
    () => identity.primary_color,
    () => identity.header_image,
    () => identity.header_image_position,
    headerFile,
], () => scheduleAutosave());

watch([
    () => goal.primary_action_type,
    () => goal.secondary_action_type,
], () => scheduleAutosave());

watch(completed, (value) => {
    if (value && !showSuccess.value) {
        showSuccess.value = true;
        fireConfetti();
    }
});

function updateFooterPin() {
    if (typeof window === 'undefined' || window.matchMedia('(min-width: 1024px)').matches) {
        footerFixed.value = false;
        footerStyle.value = {};
        return;
    }

    const root = wizardRoot.value;
    const sentinel = footerSentinel.value;

    if (!root || !sentinel || showSuccess.value) {
        footerFixed.value = false;
        footerStyle.value = {};
        return;
    }

    const rootRect = root.getBoundingClientRect();
    const sentinelRect = sentinel.getBoundingClientRect();
    const viewportBottom = window.innerHeight;
    const rootInView = rootRect.bottom > 80 && rootRect.top < viewportBottom;
    const sentinelReached = sentinelRect.top <= viewportBottom - 8;

    footerFixed.value = rootInView && !sentinelReached;

    if (footerFixed.value) {
        footerStyle.value = {
            left: `${rootRect.left}px`,
            width: `${rootRect.width}px`,
        };
    } else {
        footerStyle.value = {};
    }
}

function setupFooterObserver() {
    footerObserver?.disconnect();

    if (typeof window === 'undefined' || !footerSentinel.value) {
        return;
    }

    footerObserver = new IntersectionObserver(() => {
        updateFooterPin();
    }, {
        threshold: [0, 1],
        rootMargin: '0px',
    });

    footerObserver.observe(footerSentinel.value);
    window.addEventListener('scroll', updateFooterPin, { passive: true });
    window.addEventListener('resize', updateFooterPin);
    updateFooterPin();
}

onMounted(async () => {
    await store.fetchOnboarding();
    syncFromStore();
    window.addEventListener('closemodal', onOrdersModalClosed);

    if (completed.value) {
        showSuccess.value = true;
    }

    await nextTick();
    setupFooterObserver();
});

onBeforeUnmount(() => {
    clearTimeout(autosaveTimer);
    window.removeEventListener('closemodal', onOrdersModalClosed);
    window.removeEventListener('scroll', updateFooterPin);
    window.removeEventListener('resize', updateFooterPin);
    footerObserver?.disconnect();
});

watch([showSuccess, shouldShow], async () => {
    await nextTick();

    if (!showSuccess.value && shouldShow.value) {
        setupFooterObserver();
    } else {
        footerFixed.value = false;
        footerStyle.value = {};
    }
});
</script>

<template>
    <div
        v-if="shouldShow"
        ref="wizardRoot"
        class="relative mb-6 rounded-3xl border border-stone-200/80 bg-white shadow-xl shadow-indigo-100/40"
        dir="rtl"
        :class="{ 'animate-pulse opacity-80': loading && !loaded }"
    >
        <div
            v-if="confettiPieces.length"
            class="pointer-events-none absolute inset-0 z-20 overflow-hidden"
        >
            <span
                v-for="piece in confettiPieces"
                :key="piece.id"
                class="absolute top-0 block rounded-sm"
                :style="{
                    left: `${piece.left}%`,
                    width: `${piece.size}px`,
                    height: `${piece.size * 0.6}px`,
                    backgroundColor: piece.color,
                    transform: `rotate(${piece.rotate}deg)`,
                    animation: `onboarding-confetti ${piece.duration}s ease-out ${piece.delay}s forwards`,
                }"
            ></span>
        </div>

        <div class="relative overflow-hidden rounded-t-3xl bg-gradient-to-br from-blue-900 via-purple-800 to-blue-800 px-5 py-6 text-white sm:px-7 sm:py-7">
            <div class="pointer-events-none absolute -start-8 -top-10 size-40 rounded-full bg-white/10 blur-2xl"></div>
            <div class="pointer-events-none absolute -end-6 bottom-0 size-32 rounded-full bg-amber-300/20 blur-2xl"></div>
            <div class="pointer-events-none absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 20% 20%, rgba(255,255,255,.35) 0, transparent 40%), radial-gradient(circle at 80% 0%, rgba(255,255,255,.2) 0, transparent 35%);"></div>

            <div class="relative flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-white/75">إعداد صفحتك لأول مرة</p>
                    <h2 class="mt-1 text-xl font-bold sm:text-2xl">
                        {{ showSuccess ? 'صفحتك جاهزة 🎉' : 'كمّل صفحتك واستقبل طلبات عملائك' }}
                    </h2>
                    <p class="mt-1 max-w-md text-sm text-white/80">
                        {{ showSuccess ? 'شارك رابطك وابدأ استقبال عملائك.' : 'أكمل صفحتك خلال دقائق وإبدأ باستقبال الطلبات فورا' }}
                    </p>
                </div>
                <div
                    class="relative inline-flex size-14 shrink-0 items-center justify-center rounded-full p-[3px]"
                    :style="circularProgressStyle"
                >
                    <div class="flex size-full flex-col items-center justify-center rounded-full bg-indigo-950/70 text-center backdrop-blur-sm">
                        <p class="text-sm font-bold leading-none">{{ percentage }}%</p>
                        <p class="mt-0.5 text-[9px] text-white/70">{{ completedSteps }}/{{ totalSteps }}</p>
                    </div>
                </div>
            </div>

            <div class="relative mt-5">
                <div class="h-2 overflow-hidden rounded-full bg-black/20">
                    <div
                        class="h-full rounded-full transition-all duration-500"
                        :style="{ width: `${percentage}%`, background: progressGradient }"
                    ></div>
                </div>
            </div>
        </div>

        <div v-if="showSuccess" class="space-y-5 p-5 sm:p-7">
            <div class="mx-auto max-w-md text-center">
                <div class="mx-auto mb-4 flex size-16 items-center justify-center rounded-full bg-gradient-to-br from-amber-100 to-rose-100 text-3xl">
                    🎊
                </div>
                <h3 class="text-xl font-bold text-stone-900">أهلاً بك على إقليم!</h3>
                <p class="mt-1 text-sm text-stone-500">صفحتك جاهزة، شاركها وابدأ استقبال عملائك.</p>

                <div class="mt-4 rounded-2xl border border-stone-100 bg-stone-50 px-3 py-2.5 text-sm text-stone-600" dir="ltr">
                    {{ pageUrl }}
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2">
                    <Button type="button" variant="outline" class="h-11 rounded-xl" label="نسخ" @click="copyLink">
                        <template #icon>
                            <iconify-icon icon="hugeicons:copy-02" class="text-lg"></iconify-icon>
                        </template>
                    </Button>
                    <Button type="button" variant="outline" class="h-11 rounded-xl" label="مشاركة" @click="shareNative">
                        <template #icon>
                            <iconify-icon icon="hugeicons:share-03" class="text-lg"></iconify-icon>
                        </template>
                    </Button>
                    <Button
                        type="button"
                        variant="outline"
                        class="h-11 rounded-xl"
                        label="زيارة"
                        :href="pageUrl || '#'"
                        target="_blank"
                    >
                        <template #icon>
                            <iconify-icon icon="hugeicons:link-square-02" class="text-lg"></iconify-icon>
                        </template>
                    </Button>
                </div>
            </div>

            <OnboardingPagePreview
                :name="business.name"
                :bio="business.bio"
                :handle="identity.handle"
                :page-url="pageUrl"
                :brand-mark="brandMark"
                :logo-url="forms.business.logo"
                :header-image="identity.header_image"
                :header-image-url="identity.header_image_url"
                :header-image-position="identity.header_image_position"
                :primary-color="identity.primary_color"
                :primary-action-label="primaryActionLabel"
                :secondary-action-label="secondaryActionLabel"
                :social-links="previewSocialLinks"
            />

            <div class="space-y-2">
                <Button
                    type="button"
                    class="h-12 w-full rounded-2xl text-base font-bold"
                    label="ابدأ استقبال عملائك"
                    :href="pageUrl || '#'"
                    target="_blank"
                >
                    <template #icon>
                        <iconify-icon icon="hugeicons:rocket-01" class="text-xl"></iconify-icon>
                    </template>
                </Button>
                <Button
                    type="button"
                    variant="outline"
                    class="h-11 w-full rounded-2xl font-semibold"
                    label="زيارة الصفحة"
                    :href="pageUrl || '#'"
                    target="_blank"
                />
                <Button
                    type="button"
                    variant="secondary"
                    class="h-12 w-full rounded-2xl text-base font-bold"
                    label="إنهاء الإعداد"
                    :loading="saving"
                    @click="dismissWizard"
                >
                    <template #icon>
                        <iconify-icon icon="hugeicons:tick-02" class="text-xl"></iconify-icon>
                    </template>
                </Button>
            </div>
        </div>

        <template v-else>
            <div class="border-b border-stone-100 px-3 py-3 sm:px-5">
                <div class="flex items-center justify-between gap-2 overflow-x-auto pb-1">
                    <button
                        v-for="(step, index) in steps"
                        :key="step.key"
                        type="button"
                        class="group flex min-w-0 flex-1 flex-col items-center gap-1.5 rounded-xl px-1 py-1.5 transition sm:items-start sm:px-2"
                        :class="[
                            activeKey === step.key
                                ? 'bg-indigo-50 text-indigo-700'
                                : isUnlocked(step)
                                    ? 'text-stone-600 hover:bg-stone-50'
                                    : 'cursor-not-allowed text-stone-300',
                        ]"
                        :disabled="!isUnlocked(step)"
                        @click="selectStep(step)"
                    >
                        <span class="inline-flex items-center gap-1.5">
                            <span
                                class="inline-flex size-7 items-center justify-center rounded-full text-xs font-bold"
                                :class="[
                                    step.done
                                        ? 'bg-emerald-500 text-white'
                                        : activeKey === step.key
                                            ? 'bg-indigo-600 text-white'
                                            : isUnlocked(step)
                                                ? 'bg-stone-200 text-stone-600'
                                                : 'bg-stone-100 text-stone-300',
                                ]"
                            >
                                <iconify-icon v-if="step.done" icon="hugeicons:tick-02" class="text-sm"></iconify-icon>
                                <iconify-icon v-else :icon="step.icon || 'hugeicons:circle'" class="text-base sm:hidden"></iconify-icon>
                                <template v-if="!step.done">
                                    <span class="hidden sm:inline">{{ index + 1 }}</span>
                                </template>
                            </span>
                            <span class="hidden text-xs font-semibold sm:inline">{{ step.title }}</span>
                        </span>
                        <span class="hidden text-[11px] leading-tight text-stone-400 sm:block">{{ step.description }}</span>
                    </button>
                </div>

                <div class="mt-2 sm:hidden">
                    <p class="text-sm font-bold text-stone-900">{{ activeStep?.title }}</p>
                    <p class="text-xs text-stone-500">{{ activeStep?.description }}</p>
                </div>
            </div>

            <div class="grid gap-0 lg:grid-cols-[1.05fr_0.95fr]">
                <div class="min-w-0 px-4 pb-4 pt-4 sm:px-6 lg:pb-6">
                    <div class="mb-4 hidden sm:block">
                        <h3 class="text-lg font-bold text-stone-900">{{ activeStep?.title }}</h3>
                        <p class="mt-0.5 text-sm text-stone-500">{{ activeStep?.description }}</p>
                    </div>

                    <div v-if="activeKey === 'business'" class="space-y-4">
                        <Input
                            v-model="business.name"
                            name="name"
                            label="اسم نشاطك *"
                            placeholder="اسم صفحتك أو نشاطك"
                            :error="errors.name"
                        />
                        <Textarea
                            v-model="business.bio"
                            name="bio"
                            label="نبذة بسيطة *"
                            placeholder="اشرح نشاطك، إيش تبيع، وإيش خدماتك"
                            :rows="3"
                            :error="errors.bio"
                        />
                        <BrandMarkField
                            v-model="brandMark"
                            name="logo"
                            label="اختر شعار *"
                            :error="errors.logo || errors.brand_mark_value || errors.brand_mark_type"
                            @change="scheduleAutosave"
                        />

                        <div>
                            <p class="mb-2 text-sm font-medium text-stone-700">إيش مجالك؟ *</p>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    v-for="option in industryOptions"
                                    :key="option.slug"
                                    type="button"
                                    class="flex items-start gap-2 rounded-2xl border px-2.5 py-2.5 text-start transition sm:gap-3 sm:px-3 sm:py-3"
                                    :class="business.industry === option.slug
                                        ? 'border-indigo-400 bg-indigo-50 ring-1 ring-indigo-200'
                                        : 'border-stone-100 bg-white hover:border-stone-200'"
                                    @click="business.industry = option.slug; scheduleAutosave()"
                                >
                                    <span class="text-xl leading-none sm:text-2xl">{{ option.emoji }}</span>
                                    <span class="min-w-0">
                                        <span class="block text-xs font-semibold text-stone-800 sm:text-sm">{{ option.label }}</span>
                                        <span class="mt-0.5 block text-[10px] leading-snug text-stone-400 sm:text-xs">{{ option.description }}</span>
                                    </span>
                                </button>
                            </div>
                            <p v-if="errors.industry" class="mt-1 text-xs text-red-500">{{ errors.industry }}</p>
                        </div>
                    </div>

                    <div v-else-if="activeKey === 'contact'" class="space-y-3">
                        <Input
                            :model-value="contact.phone"
                            name="phone"
                            label="الجوال *"
                            placeholder="05xxxxxxxx"
                            dir="ltr"
                            :error="errors.phone"
                            @update:model-value="onPhoneInput"
                        />

                        <label class="flex cursor-pointer items-center gap-2 rounded-xl bg-stone-50 px-3 py-2.5 text-sm text-stone-700">
                            <input
                                v-model="whatsappSameAsPhone"
                                type="checkbox"
                                class="size-4 rounded border-stone-300 text-indigo-600 focus:ring-indigo-500"
                                @change="whatsappSameAsPhone && (contact.whatsapp = contact.phone); scheduleAutosave()"
                            >
                            يستخدم للواتساب أيضاً
                        </label>

                        <Input
                            v-if="!whatsappSameAsPhone"
                            v-model="contact.whatsapp"
                            name="whatsapp"
                            label="رقم الواتساب"
                            placeholder="9665xxxxxxxx"
                            dir="ltr"
                            :error="errors.whatsapp"
                        />

                        <Input
                            v-model="contact.email"
                            name="email"
                            type="email"
                            label="البريد الإلكتروني *"
                            placeholder="hello@example.com"
                            dir="ltr"
                            :error="errors.email"
                        />

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <CountrySelect v-model="contact.country" name="country" :error="errors.country" />
                            <Input v-model="contact.city" name="city" label="المدينة" placeholder="الرياض" :error="errors.city" />
                        </div>

                        <div class="rounded-2xl border border-stone-100 bg-stone-50/70 p-3">
                            <p class="mb-2 text-sm font-semibold text-stone-700">حساب سوشال ميديا <span class="font-normal text-stone-400">(اختياري)</span></p>
                            <div class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_1.2fr]">
                                <Select
                                    v-model="social.network"
                                    name="social_network"
                                    :options="socialNetworks"
                                />
                                <Input
                                    v-model="social.username"
                                    name="social_username"
                                    label="المعرف"
                                    placeholder="@username"
                                    dir="ltr"
                                />
                            </div>
                        </div>
                    </div>

                    <div v-else-if="activeKey === 'identity'" class="space-y-4">
                        <Input
                            v-model="identity.handle"
                            name="handle"
                            label="معرّف الصفحة *"
                            :prefix="handlePrefix"
                            placeholder="my-page"
                            dir="ltr"
                            info="يمكنك تغييره لاحقاً أو ربط دومينك المخصص."
                            :error="errors.handle"
                        />

                        <PickerColor
                            v-model="identity.primary_color"
                            name="primary_color"
                            label="اللون الرئيسي *"
                            :options="colorOptions"
                            allow-custom
                        />

                        <UploadCover
                            v-model="identity.header_image"
                            v-model:file="headerFile"
                            v-model:preview="identity.header_image_url"
                            v-model:position="identity.header_image_position"
                            name="header_image"
                            label="صورة الهيدر"
                            info="صورة أبلغ من ألف كلمة."
                            :error="errors.header_image || errors.header_image_file"
                        />
                    </div>

                    <div v-else-if="activeKey === 'goal'" class="space-y-4">
                        <SearchableSelect
                            v-model="goal.primary_action_type"
                            name="primary_action_type"
                            label="الزر الرئيسي *"
                            placeholder="ابحث عن إجراء…"
                            info="اختر أهم زر هنا، يمكنك إضافة باقي الأزرار لاحقاً كأزرار ثانوية أو أزرار للصفحة."
                            :options="primaryActionSelectOptions"
                            :error="errors.primary_action_type"
                            @update:model-value="scheduleAutosave"
                        />

                        <SearchableSelect
                            v-model="goal.secondary_action_type"
                            name="secondary_action_type"
                            label="الزر الثانوي"
                            placeholder="ابحث عن إجراء ثانوي…"
                            clearable
                            clear-label="بدون زر ثانوي"
                            :options="secondaryActionSelectOptions"
                            :error="errors.secondary_action_type"
                            @update:model-value="scheduleAutosave"
                        />
                    </div>

                    <div v-else-if="activeKey === 'catalog'" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <button
                            v-for="option in catalogOptions"
                            :key="option.slug"
                            type="button"
                            class="flex items-start gap-3 rounded-2xl border px-3 py-3 text-start transition"
                            :class="enabledCatalog.includes(option.slug)
                                ? 'border-indigo-300 bg-indigo-50/70 ring-1 ring-indigo-200'
                                : 'border-stone-100 bg-white hover:border-stone-200'"
                            @click="toggleCatalog(option.slug)"
                        >
                            <img :src="`/${option.icon}`" alt="" class="size-10 shrink-0 rounded-xl bg-white p-1.5 shadow-sm">
                            <span class="min-w-0 flex-1">
                                <span class="flex items-center justify-between gap-2">
                                    <span class="block text-sm font-semibold text-stone-800">{{ option.name }}</span>
                                    <span
                                        class="inline-flex size-5 shrink-0 items-center justify-center rounded-full border"
                                        :class="enabledCatalog.includes(option.slug)
                                            ? 'border-indigo-500 bg-indigo-500 text-white'
                                            : 'border-stone-200 bg-white text-transparent'"
                                    >
                                        <iconify-icon icon="hugeicons:tick-02" class="text-sm"></iconify-icon>
                                    </span>
                                </span>
                                <span class="mt-0.5 block text-xs leading-relaxed text-stone-400">{{ option.description }}</span>
                            </span>
                        </button>
                        <p v-if="errors.enabled" class="col-span-full text-xs text-red-500">{{ errors.enabled }}</p>
                    </div>

                    <div v-else-if="activeKey === 'orders'" class="space-y-2">
                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-2xl border px-3 py-3.5 text-start transition"
                            :class="forms.orders.payment_active
                                ? 'border-emerald-200 bg-emerald-50/50'
                                : 'border-stone-100 bg-white hover:bg-stone-50'"
                            @click="openOrdersModal('new-onboarding-payment')"
                        >
                            <span
                                class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl"
                                :class="forms.orders.payment_active ? 'bg-emerald-100 text-emerald-600' : 'bg-indigo-50 text-indigo-600'"
                            >
                                <iconify-icon :icon="forms.orders.payment_active ? 'hugeicons:checkmark-circle-02' : 'hugeicons:credit-card'" class="text-2xl"></iconify-icon>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-stone-800">تفعيل طرق الدفع</span>
                                <span class="block text-xs" :class="forms.orders.payment_active ? 'text-emerald-600' : 'text-stone-400'">
                                    {{ forms.orders.payment_active ? 'تم التفعيل' : 'اختر بوابات الدفع اللي تناسبك' }}
                                </span>
                            </span>
                            <iconify-icon icon="hugeicons:arrow-left-01" class="text-xl text-stone-300"></iconify-icon>
                        </button>

                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-2xl border px-3 py-3.5 text-start transition"
                            :class="forms.orders.shipping_active
                                ? 'border-emerald-200 bg-emerald-50/50'
                                : 'border-stone-100 bg-white hover:bg-stone-50'"
                            @click="openOrdersModal('new-onboarding-shipping')"
                        >
                            <span
                                class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl"
                                :class="forms.orders.shipping_active ? 'bg-emerald-100 text-emerald-600' : 'bg-sky-50 text-sky-600'"
                            >
                                <iconify-icon :icon="forms.orders.shipping_active ? 'hugeicons:checkmark-circle-02' : 'hugeicons:delivery-truck-02'" class="text-2xl"></iconify-icon>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-stone-800">تفعيل طرق الشحن</span>
                                <span class="block text-xs" :class="forms.orders.shipping_active ? 'text-emerald-600' : 'text-stone-400'">
                                    {{ forms.orders.shipping_active ? 'تم التفعيل' : 'اختياري حسب نوع منتجاتك' }}
                                </span>
                            </span>
                            <iconify-icon icon="hugeicons:arrow-left-01" class="text-xl text-stone-300"></iconify-icon>
                        </button>

                        <button
                            type="button"
                            class="flex w-full items-center gap-3 rounded-2xl border px-3 py-3.5 text-start transition"
                            :class="forms.orders.verification_done
                                ? 'border-emerald-200 bg-emerald-50/50'
                                : 'border-stone-100 bg-white hover:bg-stone-50'"
                            @click="openOrdersModal('new-onboarding-verification')"
                        >
                            <span
                                class="inline-flex size-11 shrink-0 items-center justify-center rounded-2xl"
                                :class="forms.orders.verification_done ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-50 text-amber-600'"
                            >
                                <iconify-icon :icon="forms.orders.verification_done ? 'hugeicons:checkmark-circle-02' : 'hugeicons:security-check'" class="text-2xl"></iconify-icon>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-stone-800">توثيق النشاط</span>
                                <span class="block text-xs" :class="forms.orders.verification_done ? 'text-emerald-600' : 'text-stone-400'">
                                    {{ forms.orders.verification_done ? 'تم إرسال التوثيق' : 'مطلوب لاستقبال المدفوعات بأمان' }}
                                </span>
                            </span>
                            <iconify-icon icon="hugeicons:arrow-left-01" class="text-xl text-stone-300"></iconify-icon>
                        </button>

                        <p class="pt-1 text-xs text-stone-400">
                            إعداد مرة واحدة: فعّل وسيلة دفع وأكمل التوثيق. الشحن اختياري.
                        </p>
                    </div>
                </div>

                <div class="w-full border-t border-stone-100 bg-stone-50/80 px-4 py-5 lg:border-s lg:border-t-0 lg:px-5">
                    <OnboardingPagePreview
                        :name="business.name"
                        :bio="business.bio"
                        :handle="identity.handle"
                        :page-url="pageUrl"
                        :brand-mark="brandMark"
                        :logo-url="forms.business.logo"
                        :header-image="identity.header_image"
                        :header-image-url="identity.header_image_url"
                        :header-image-position="identity.header_image_position"
                        :primary-color="identity.primary_color"
                        :primary-action-label="primaryActionLabel"
                        :secondary-action-label="secondaryActionLabel"
                        :social-links="previewSocialLinks"
                    />
                </div>
            </div>

            <div ref="footerSentinel" class="h-px w-full" aria-hidden="true"></div>
            <div
                v-if="footerFixed"
                class="h-[4.25rem]"
                aria-hidden="true"
            ></div>
            <div
                class="z-30 border-t border-stone-100 bg-white/95 px-4 py-3 backdrop-blur sm:px-6"
                :class="footerFixed
                    ? 'fixed bottom-0 rounded-b-3xl shadow-[0_-8px_24px_rgba(0,0,0,0.08)]'
                    : 'relative'"
                :style="footerStyle"
            >
                <div class="flex items-center gap-2">
                    <Button
                        v-if="canGoBack"
                        type="button"
                        variant="outline"
                        class="h-12 shrink-0 rounded-2xl px-4"
                        label="رجوع"
                        @click="goBack"
                    >
                        <template #icon>
                            <iconify-icon icon="solar:alt-arrow-right-bold-duotone" class="text-xl"></iconify-icon>
                        </template>
                    </Button>
                    <Button
                        type="button"
                        class="h-12 flex-1 rounded-2xl text-base font-bold"
                        :disabled="!canContinue"
                        :loading="saving && !autosaving"
                        :label="isLastStep ? 'إنهاء إعداد صفحتك' : 'حفظ وإكمال'"
                        icon-position="end"
                        @click="continueStep"
                    >
                        <template #icon>
                            <iconify-icon
                                :icon="isLastStep ? 'solar:check-circle-bold-duotone' : 'solar:arrow-left-bold-duotone'"
                                class="text-2xl"
                            ></iconify-icon>
                        </template>
                    </Button>
                </div>
            </div>
        </template>

        <Modal title="طرق الدفع" size="2xl" name="new-onboarding-payment">
            <div class="flex max-h-[75vh] flex-col">
                <div class="min-h-0 flex-1 overflow-y-auto p-2 sm:p-3">
                    <SettingsPaymentOptions embedded />
                </div>
                <div class="shrink-0 border-t border-stone-100 bg-white p-3 px-4">
                    <Button
                        type="button"
                        class="h-11 w-full rounded-xl font-semibold"
                        label="تم"
                        @click="closeOrdersSetupModal('new-onboarding-payment')"
                    />
                </div>
            </div>
        </Modal>

        <Modal title="طرق الشحن" size="2xl" name="new-onboarding-shipping">
            <div class="flex max-h-[75vh] flex-col">
                <div class="min-h-0 flex-1 overflow-y-auto p-2 sm:p-3">
                    <SettingsShippingOptions embedded />
                </div>
                <div class="shrink-0 border-t border-stone-100 bg-white p-3 px-4">
                    <Button
                        type="button"
                        class="h-11 w-full rounded-xl font-semibold"
                        label="تم"
                        @click="closeOrdersSetupModal('new-onboarding-shipping')"
                    />
                </div>
            </div>
        </Modal>

        <Modal title="توثيق النشاط" size="lg" name="new-onboarding-verification">
            <CompletionVerification skip-welcome-flow @saved="onVerificationSaved" />
        </Modal>
    </div>
</template>

<style scoped>
@keyframes onboarding-confetti {
    0% {
        transform: translate3d(0, -10px, 0) rotate(0deg);
        opacity: 1;
    }

    100% {
        transform: translate3d(20px, 420px, 0) rotate(720deg);
        opacity: 0;
    }
}
</style>
