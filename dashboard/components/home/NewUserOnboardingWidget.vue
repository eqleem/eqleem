<script setup>
import {
    computed, defineAsyncComponent, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch,
} from 'vue';
import { storeToRefs } from 'pinia';
import Modal from '../ui/Modal.vue';
import Input from '../ui/Input.vue';
import Textarea from '../ui/Textarea.vue';
import Select from '../ui/Select.vue';
import SearchableSelect from '../ui/SearchableSelect.vue';
import PickerColor from '../ui/PickerColor.vue';
import Button from '../ui/Button.vue';
import OnboardingPagePreview from './OnboardingPagePreview.vue';
import CompletionVerification from './CompletionVerification.vue';
import { BrandMarkField, CountrySelect, UploadCover } from '../ui/asyncHeavy.js';
import { useOnboardingStore } from '../../stores/onboarding.js';
import { useWelcomeStore } from '../../stores/welcome.js';
import { useSession, updateTenant, loadDashboardContext } from '../../stores/session.js';
import { openModal, closeModal } from '../../lib/modal.js';
import { notifySuccess, notifyError } from '../../lib/notify.js';
import { defaultCountryCode } from '../../data/countries.js';
import { COVER_CLEAR, COVER_COLORS, encodeCssCover } from '../../data/coverPresets.js';
import { appDomain } from '../../data/settings.js';

const SettingsPaymentOptions = defineAsyncComponent(() => import('../../pages/settings/PaymentOptions.vue'));
const SettingsShippingOptions = defineAsyncComponent(() => import('../../pages/settings/ShippingOptions.vue'));

const store = useOnboardingStore();
const welcomeStore = useWelcomeStore();
const {
    percentage,
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
const forceOpen = ref(false);
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
const defaultPrimaryActionType = 'contact-form';
const goal = reactive({
    primary_action_type: defaultPrimaryActionType,
    secondary_action_type: '',
});
const enabledCatalog = ref([]);
const defaultCatalogSlugs = ['store', 'digital-services'];
const headerFollowsPrimaryColor = ref(true);

const primaryColorHexes = Object.fromEntries(
    COVER_COLORS.map((color) => [color.id, color.value]),
);
primaryColorHexes.gray = '#78716c';

let autosaveTimer = null;
let syncingFromStore = false;
let hydrated = false;
const onboardingModalName = 'new-user-onboarding';

const stepIndex = computed(() => steps.value.findIndex((step) => step.key === activeKey.value));
const activeStep = computed(() => steps.value.find((step) => step.key === activeKey.value) ?? null);
const isLastStep = computed(() => stepIndex.value === steps.value.length - 1);
const canGoBack = computed(() => showSuccess.value || stepIndex.value > 0);
const isWidgetVisible = computed(() => shouldShow.value || forceOpen.value);
const activeStepNumber = computed(() => Math.max(stepIndex.value + 1, 1));
const displayPercentage = computed(() => Math.max(percentage.value, 5));

const stepTitles = {
    business: 'النشاط',
    contact: 'معلومات الاتصال',
    identity: 'الهوية',
    goal: 'الأهداف',
    catalog: 'الكتالوج',
    orders: 'استقبال الطلبات',
};

const timelineSteps = computed(() => [
    ...steps.value.map((step) => ({
        ...step,
        title: stepTitles[step.key] ?? step.title,
    })),
    {
        key: 'publish',
        title: 'النشر',
        icon: 'hugeicons:rocket-01',
        done: completed.value,
        unlocked: false,
        milestone: true,
    },
]);

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

const industrySelectOptions = computed(() => (
    industryOptions.value.map((option) => ({
        id: option.slug,
        label: option.label,
        description: option.description,
        emoji: option.emoji,
    }))
));

const previewSocialLinks = computed(() => {
    if (social.username.trim()) {
        return [{ network: social.network || 'twitter', username: social.username.trim() }];
    }

    return forms.value.contact?.social_links ?? [];
});

const handlePrefix = computed(() => `https://${appDomain}/`);

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

function primaryColorCover(color) {
    const value = String(color ?? '').trim();
    const hex = /^#[0-9a-fA-F]{3,8}$/.test(value)
        ? value
        : primaryColorHexes[value] ?? primaryColorHexes.blue;

    return encodeCssCover('color', hex);
}

function onHeaderImageChange(value) {
    identity.header_image = value;
    headerFollowsPrimaryColor.value = false;
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
            return Boolean(forms.value.orders.payment_active);
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

/**
 * Keep the dashboard header (logo / preview URL) in sync with onboarding saves.
 */
function syncSessionTenantFromOnboarding() {
    if (!tenant.value) {
        return;
    }

    const savedMark = brandMarkFromPayload(store.forms.business);
    const nextHandle = store.forms.identity.handle
        || identity.handle.trim()
        || tenant.value.handle;
    const nextUrl = store.pageUrl || tenant.value.url;

    updateTenant({
        ...tenant.value,
        name: store.forms.business.name || business.name.trim() || tenant.value.name,
        logo: store.forms.business.logo || savedMark.url || tenant.value.logo,
        brand_mark: savedMark.type
            ? {
                type: savedMark.type,
                value: savedMark.value,
                color: savedMark.color,
                url: savedMark.url,
            }
            : tenant.value.brand_mark,
        handle: nextHandle,
        url: nextUrl,
    });

    if (nextUrl) {
        welcomeStore.pageUrl = nextUrl;
    }
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

    showSuccess.value = false;
    activeKey.value = step.key;
}

function onIndustryChange(value) {
    business.industry = value;
    scheduleAutosave();
}

function onPrimaryActionChange(value) {
    goal.primary_action_type = value == null ? '' : String(value);

    if (goal.secondary_action_type && goal.secondary_action_type === goal.primary_action_type) {
        goal.secondary_action_type = '';
    }

    scheduleAutosave();
}

function onSecondaryActionChange(value) {
    goal.secondary_action_type = value == null ? '' : String(value);
    scheduleAutosave();
}

function goBack() {
    if (!canGoBack.value) {
        return;
    }

    if (showSuccess.value) {
        showSuccess.value = false;
        activeKey.value = steps.value[steps.value.length - 1]?.key || 'orders';
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
    headerFollowsPrimaryColor.value = !identity.header_image
        || identity.header_image === primaryColorCover(identity.primary_color);

    goal.primary_action_type = value.goal?.primary_action_type || defaultPrimaryActionType;
    goal.secondary_action_type = value.goal?.secondary_action_type ?? '';

    const enabled = value.catalog?.enabled ?? [];
    enabledCatalog.value = enabled.length
        ? [...enabled]
        : catalogOptions.value
            .filter((item) => defaultCatalogSlugs.includes(item.slug))
            .map((item) => item.slug);

    if (!activeKey.value || !steps.value.some((step) => step.key === activeKey.value)) {
        activeKey.value = store.currentStep || steps.value[0]?.key || 'business';
    }

    // Only auto-enter success on first hydrate. Later refreshes must not yank the
    // user back if they navigated to a previous step after completing.
    if (!hydrated && completed.value) {
        showSuccess.value = true;
    }

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

    const payload = {
        partial: true,
    };

    if (goal.primary_action_type) {
        payload.primary_action_type = goal.primary_action_type;
    }

    // Always send secondary on goal autosave so a chosen value is persisted, and an
    // intentional clear (empty string) can be distinguished later on full save.
    if (goal.secondary_action_type) {
        payload.secondary_action_type = goal.secondary_action_type;
    }

    const result = await store.saveGoal(payload);

    if (result.ok) {
        if (store.forms.goal.primary_action_type) {
            goal.primary_action_type = store.forms.goal.primary_action_type;
        }

        if (store.forms.goal.secondary_action_type) {
            goal.secondary_action_type = store.forms.goal.secondary_action_type;
        }
    }

    return result;
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
                if (!result.ok) {
                    Object.assign(errors, flattenErrors(result.errors));
                }
                break;
            case 'catalog':
                result = await autosaveCatalog();
                break;
            default:
                break;
        }

        if (result.ok && (activeKey.value === 'business' || activeKey.value === 'identity')) {
            syncSessionTenantFromOnboarding();
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
        syncSessionTenantFromOnboarding();

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
        syncSessionTenantFromOnboarding();

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

        if (result.ok) {
            goal.primary_action_type = store.forms.goal.primary_action_type || goal.primary_action_type;
            // Prefer server value when present; keep local selection if the response
            // unexpectedly omits a secondary that we just submitted.
            goal.secondary_action_type = store.forms.goal.secondary_action_type
                || (goal.secondary_action_type || '');
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

        if (!forms.value.orders.payment_active) {
            notifyError('فعّل وسيلة دفع واحدة على الأقل للمتابعة');
            return;
        }

        syncSessionTenantFromOnboarding();
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

    // Refresh dashboard session so header logo + preview URL match saved tenant.
    syncSessionTenantFromOnboarding();
    await loadDashboardContext();
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

function onOnboardingModalClosed(event) {
    if (event.detail?.modal !== onboardingModalName) {
        return;
    }

    if (shouldShow.value) {
        nextTick(() => openModal(onboardingModalName));
    } else {
        forceOpen.value = false;
    }
}

function onOnboardingModalOpened(event) {
    if (event.detail?.modal !== onboardingModalName || shouldShow.value) {
        return;
    }

    forceOpen.value = true;
    showSuccess.value = false;
    activeKey.value = steps.value[0]?.key || 'business';

    nextTick(() => setupFooterObserver());
}

watch(completed, (value, previous) => {
    if (value && !previous) {
        showSuccess.value = true;
        fireConfetti();
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
    () => identity.header_image,
    () => identity.header_image_position,
    headerFile,
], () => scheduleAutosave());

watch(() => identity.primary_color, (value) => {
    if (hydrated && !syncingFromStore && headerFollowsPrimaryColor.value) {
        identity.header_image = primaryColorCover(value);
        identity.header_image_url = '';
        headerFile.value = null;
    }

    scheduleAutosave();
});

function updateFooterPin() {
    if (typeof window === 'undefined' || window.matchMedia('(min-width: 1024px)').matches) {
        footerFixed.value = false;
        footerStyle.value = {};
        return;
    }

    const root = wizardRoot.value;
    const sentinel = footerSentinel.value;

    if (!root || !sentinel) {
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
    window.addEventListener('openmodal', onOnboardingModalOpened);
    window.addEventListener('closemodal', onOrdersModalClosed);
    window.addEventListener('closemodal', onOnboardingModalClosed);

    if (completed.value) {
        showSuccess.value = true;
    }

    await nextTick();
    if (shouldShow.value) {
        openModal(onboardingModalName);
    }
    setupFooterObserver();
});

onBeforeUnmount(() => {
    clearTimeout(autosaveTimer);
    window.removeEventListener('openmodal', onOnboardingModalOpened);
    window.removeEventListener('closemodal', onOrdersModalClosed);
    window.removeEventListener('closemodal', onOnboardingModalClosed);
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

watch(shouldShow, async (value) => {
    await nextTick();

    if (value) {
        openModal(onboardingModalName);
    } else {
        closeModal(onboardingModalName);
    }
});
</script>

<template>
    <Modal
        title="إعداد صفحتك"
        size="5xl"
        :name="onboardingModalName"
        :close="!shouldShow"
        :escape="!shouldShow"
    >
        <div
            v-if="isWidgetVisible"
            ref="wizardRoot"
            class="relative rounded-3xl border border-stone-200/80 bg-white shadow-xl shadow-indigo-100/40"
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

        <div class="rounded-t-3xl border-b border-stone-200 bg-stone-50 px-5 py-4 sm:px-6">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-normal text-stone-600">
                    أكمل صفحتك خلال دقائق وإبدأ باستقبال الطلبات فورا
                </p>
                <p class="shrink-0 text-xs font-medium tabular-nums text-stone-500" dir="ltr">
                    {{ activeStepNumber }}/{{ totalSteps }} ({{ displayPercentage }}%)
                </p>
            </div>

            <div class="mt-3">
                <div class="h-1.5 overflow-hidden rounded-full bg-stone-200">
                    <div
                        class="h-full rounded-full bg-indigo-400 transition-all duration-500"
                        :style="{ width: `${displayPercentage}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <div class="border-b border-stone-100 py-3 sm:px-5">
            <div class="overflow-x-auto pb-1">
                <div class="flex w-full items-center justify-between px-2 sm:px-0">
                    <template v-for="(step, index) in timelineSteps" :key="step.key">
                        <button
                            type="button"
                            class="group flex shrink-0 items-center gap-1 rounded-lg px-1 py-1 text-xs transition sm:gap-1.5 sm:px-1.5"
                            :class="[
                                (showSuccess && step.key === 'publish') || (!showSuccess && activeKey === step.key)
                                    ? 'font-bold text-primary-600'
                                    : step.done || (showSuccess && step.key !== 'publish')
                                        ? 'text-emerald-600'
                                        : isUnlocked(step)
                                            ? 'text-stone-500 hover:bg-stone-50'
                                            : 'cursor-not-allowed text-stone-300',
                            ]"
                            :disabled="step.milestone || !isUnlocked(step)"
                            @click="!step.milestone && selectStep(step)"
                        >
                            <span class="hidden w-3 items-center justify-center text-sm leading-none sm:inline-flex">
                                {{ (showSuccess && step.key === 'publish') || (!showSuccess && activeKey === step.key)
                                    ? '●'
                                    : step.done || (showSuccess && step.key !== 'publish')
                                        ? '✓'
                                        : '○' }}
                            </span>
                            <iconify-icon
                                :icon="step.icon || 'hugeicons:circle'"
                                class="shrink-0 text-base"
                            ></iconify-icon>
                            <span class="text-[10px] tabular-nums sm:hidden">{{ index + 1 }}</span>
                            <span
                                v-if="(showSuccess && step.key === 'publish') || (!showSuccess && activeKey === step.key)"
                                class="whitespace-nowrap text-[11px] sm:hidden"
                            >
                                {{ step.title }}
                            </span>
                            <span class="hidden whitespace-nowrap sm:inline">{{ step.title }}</span>
                        </button>
                        <span
                            v-if="index < timelineSteps.length - 1"
                            class="mx-0.5 h-px min-w-1 flex-1 bg-stone-200 sm:mx-1"
                        ></span>
                    </template>
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
        </div>

        <template v-else>
            <div class="grid gap-0 lg:grid-cols-8">
                <div class="min-w-0 px-4 pb-4 pt-4 sm:px-6 lg:col-span-5 lg:pb-6">
                    <div class="mb-4">
                        <p class="text-sm text-stone-500">{{ activeStep?.description }}</p>
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
                            
                            :error="errors.bio"
                        />
                        <BrandMarkField
                            v-model="brandMark"
                            name="logo"
                            label="اختر شعار *"
                            :error="errors.logo || errors.brand_mark_value || errors.brand_mark_type"
                            @change="scheduleAutosave"
                        />

                        <SearchableSelect
                            :model-value="business.industry"
                            name="industry"
                            label="إيش مجالك؟ *"
                            placeholder="ابحث عن مجالك…"
                            empty-label="لم نجد مجالاً مطابقاً"
                            :options="industrySelectOptions"
                            :error="errors.industry"
                            show-selected-description
                            @update:model-value="onIndustryChange"
                        />
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
                            info-dir="rtl"
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
                            :model-value="identity.header_image"
                            v-model:file="headerFile"
                            v-model:preview="identity.header_image_url"
                            v-model:position="identity.header_image_position"
                            name="header_image"
                            label="صورة الهيدر"
                            info="صورة أبلغ من ألف كلمة."
                            :error="errors.header_image || errors.header_image_file"
                            @update:model-value="onHeaderImageChange"
                        />
                    </div>

                    <div v-else-if="activeKey === 'goal'" class="space-y-4">
                        <SearchableSelect
                            :model-value="goal.primary_action_type"
                            name="primary_action_type"
                            label="الزر الرئيسي *"
                            placeholder="ابحث عن إجراء…"
                            info="اختر أهم زر في صفحتك. يمكنك إضافة المزيد لاحقاً من إعدادات الصفحة."
                            :options="primaryActionSelectOptions"
                            :error="errors.primary_action_type"
                            @update:model-value="onPrimaryActionChange"
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
                                    {{ forms.orders.verification_done ? 'تم إرسال التوثيق' : 'اختياري — يمكنك توثيق متجرك لاحقاً بعد اكتمال الإعداد' }}
                                </span>
                            </span>
                            <iconify-icon icon="hugeicons:arrow-left-01" class="text-xl text-stone-300"></iconify-icon>
                        </button>

                        <p class="pt-1 text-xs text-stone-400">
                            للمتابعة: فعّل وسيلة دفع واحدة على الأقل. الشحن والتوثيق اختياريان.
                        </p>
                    </div>
                </div>

                <div class="hidden w-full border-t border-stone-100 bg-stone-50/80 px-4 py-5 lg:col-span-3 lg:block lg:border-s lg:border-t-0 lg:px-5">
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
        </template>

            <div
                class="lg:hidden"
                :class="showSuccess
                    ? 'h-[5rem]'
                    : activeKey === 'identity' ? 'h-[calc(5rem+58vh)]' : 'h-[19rem]'"
                aria-hidden="true"
            ></div>
            <div
                class="fixed inset-x-4 bottom-0 z-30 overflow-hidden rounded-b-xl border border-stone-200 bg-stone-50/95 shadow-[0_-8px_24px_rgba(0,0,0,0.12)] backdrop-blur lg:hidden"
            >
                <div class="flex items-center gap-2 px-4 py-3">
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
                        :disabled="showSuccess ? false : !canContinue"
                        :loading="showSuccess ? saving : (saving && !autosaving)"
                        :label="showSuccess ? 'إنهاء الإعداد' : (isLastStep ? 'إنهاء إعداد صفحتك' : 'حفظ وإكمال')"
                        icon-position="end"
                        @click="showSuccess ? dismissWizard() : continueStep()"
                    >
                        <template #icon>
                            <iconify-icon
                                :icon="showSuccess || isLastStep ? 'solar:check-circle-bold-duotone' : 'solar:arrow-left-bold-duotone'"
                                class="text-2xl"
                            ></iconify-icon>
                        </template>
                    </Button>
                </div>

                <div
                    v-if="!showSuccess"
                    class="overflow-y-auto border-t border-stone-200 bg-stone-50/80 p-3"
                    :class="activeKey === 'identity' ? 'max-h-[58vh]' : 'max-h-56'"
                >
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
                        :compact-cover="activeKey !== 'identity'"
                    />
                </div>
            </div>

            <div ref="footerSentinel" class="h-px w-full" aria-hidden="true"></div>
            <div
                v-if="footerFixed"
                class="hidden h-[4.25rem] lg:block"
                aria-hidden="true"
            ></div>
            <div
                class="hidden rounded-b-3xl border-t border-stone-200 bg-stone-50/95 px-4 py-3 backdrop-blur sm:px-6 lg:block"
                :class="footerFixed
                    ? 'fixed bottom-0 z-30 shadow-[0_-8px_24px_rgba(0,0,0,0.08)]'
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
                        :disabled="showSuccess ? false : !canContinue"
                        :loading="showSuccess ? saving : (saving && !autosaving)"
                        :label="showSuccess ? 'إنهاء الإعداد' : (isLastStep ? 'إنهاء إعداد صفحتك' : 'حفظ وإكمال')"
                        icon-position="end"
                        @click="showSuccess ? dismissWizard() : continueStep()"
                    >
                        <template #icon>
                            <iconify-icon
                                :icon="showSuccess || isLastStep ? 'solar:check-circle-bold-duotone' : 'solar:arrow-left-bold-duotone'"
                                class="text-2xl"
                            ></iconify-icon>
                        </template>
                    </Button>
                </div>
            </div>

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
    </Modal>
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
