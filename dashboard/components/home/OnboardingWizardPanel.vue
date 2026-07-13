<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Input from '../ui/Input.vue';
import Textarea from '../ui/Textarea.vue';
import Select from '../ui/Select.vue';
import CountrySelect from '../ui/CountrySelect.vue';
import FileCrop from '../ui/FileCrop.vue';
import PickerColor from '../ui/PickerColor.vue';
import Radio from '../ui/Radio.vue';
import Button from '../ui/Button.vue';
import { useOnboardingStore } from '../../stores/onboarding.js';
import { useSession, updateTenant } from '../../stores/session.js';
import { openModal } from '../../lib/modal.js';
import { notifySuccess, notifyError } from '../../lib/notify.js';
import { defaultCountryCode } from '../../data/countries.js';

const props = defineProps({
    /** When true, every step is navigable (completed account editing). */
    allowAllSteps: { type: Boolean, default: false },
    /** Hide the page header chrome (used inside a modal). */
    embedded: { type: Boolean, default: false },
    initialStep: { type: String, default: null },
});

const emit = defineEmits(['finished']);

const store = useOnboardingStore();
const {
    percentage,
    completedSteps,
    totalSteps,
    steps,
    forms,
    industries,
    socialNetworks,
    fonts,
    colorOptions,
    radiusOptions,
    catalogOptions,
    saving,
    completed,
} = storeToRefs(store);

const { tenant } = useSession();

const activeKey = ref(props.initialStep || store.currentStep || 'business');
const logoFile = ref(null);
const logoPreview = ref(null);

const business = reactive({ industry: '', name: '', bio: '' });
const contact = reactive({
    phone: '',
    email: '',
    whatsapp: '',
    country: defaultCountryCode,
    city: '',
});
const socialDraft = reactive({ network: 'twitter', url: '' });
const socialLinks = ref([]);
const identity = reactive({
    primary_color: 'blue',
    logo_radius: 'rounded-full',
    font_family: 'sarmady',
});
const enabledCatalog = ref([]);
const errors = reactive({});
const whatsappLinked = ref(true);

const stepIndex = computed(() => steps.value.findIndex((step) => step.key === activeKey.value));
const activeStep = computed(() => steps.value.find((step) => step.key === activeKey.value) ?? null);
const isLastStep = computed(() => stepIndex.value === steps.value.length - 1);
const canGoBack = computed(() => stepIndex.value > 0);

const nextLabel = computed(() => {
    if (isLastStep.value) {
        return props.allowAllSteps || completed.value ? 'حفظ' : 'إنهاء الإعداد';
    }

    return props.allowAllSteps || completed.value ? 'حفظ ومتابعة' : 'التالي';
});

const canContinue = computed(() => {
    switch (activeKey.value) {
        case 'business':
            return Boolean(
                business.industry
                && business.name.trim().length >= 2
                && business.bio.trim()
                && (logoFile.value || logoPreview.value),
            );
        case 'contact':
            return Boolean(
                contact.phone.trim()
                && contact.email.trim()
                && contact.whatsapp.trim()
                && contact.country.trim()
                && contact.city.trim()
                && socialLinks.value.length > 0,
            );
        case 'identity':
            return Boolean(identity.primary_color && identity.logo_radius && identity.font_family);
        case 'catalog':
            return enabledCatalog.value.length > 0;
        case 'orders':
            return Boolean(forms.value.orders.payment_active && forms.value.orders.verification_done);
        default:
            return false;
    }
});

watch(
    () => props.initialStep,
    (key) => {
        if (key) {
            activeKey.value = key;
        }
    },
);

watch(
    () => forms.value,
    (value) => {
        business.industry = value.business?.industry ?? '';
        business.name = value.business?.name ?? '';
        business.bio = value.business?.bio ?? '';

        if (!logoFile.value) {
            logoPreview.value = value.business?.logo || null;
        }

        contact.phone = value.contact?.phone ?? '';
        contact.email = value.contact?.email ?? '';
        contact.whatsapp = value.contact?.whatsapp ?? '';
        const country = value.contact?.country ?? '';
        contact.country = /^[A-Za-z]{2}$/.test(country) ? country.toUpperCase() : defaultCountryCode;
        contact.city = value.contact?.city ?? '';
        socialLinks.value = [...(value.contact?.social_links ?? [])];

        const phone = contact.phone.trim();
        const whatsapp = contact.whatsapp.trim();
        whatsappLinked.value = !whatsapp || whatsapp === phone;

        if (whatsappLinked.value && phone && !whatsapp) {
            contact.whatsapp = contact.phone;
        }

        const firstNetwork = Object.keys(socialNetworks.value)[0] ?? 'twitter';
        if (!Object.keys(socialNetworks.value).includes(socialDraft.network)) {
            socialDraft.network = firstNetwork;
        }

        identity.primary_color = value.identity?.primary_color ?? 'blue';
        identity.logo_radius = value.identity?.logo_radius ?? 'rounded-full';
        identity.font_family = value.identity?.font_family ?? 'sarmady';

        const enabled = value.catalog?.enabled ?? [];
        enabledCatalog.value = enabled.length
            ? [...enabled]
            : catalogOptions.value.filter((item) => item.enabled).map((item) => item.slug);
    },
    { immediate: true, deep: true },
);

function isUnlocked(step) {
    return props.allowAllSteps || completed.value || step.unlocked;
}

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
}

function selectStep(step) {
    if (!isUnlocked(step)) {
        return;
    }

    activeKey.value = step.key;
}

function goBack() {
    if (!canGoBack.value) {
        return;
    }

    const prev = steps.value[stepIndex.value - 1];

    if (prev && isUnlocked(prev)) {
        activeKey.value = prev.key;
    }
}

function addSocialLink() {
    const url = socialDraft.url.trim();

    if (!socialDraft.network || !url) {
        return;
    }

    socialLinks.value.push({
        id: `local-${Date.now()}`,
        network: socialDraft.network,
        url,
    });
    socialDraft.url = '';
}

function removeSocialLink(id) {
    socialLinks.value = socialLinks.value.filter((link) => link.id !== id);
}

function toggleCatalog(slug) {
    if (enabledCatalog.value.includes(slug)) {
        enabledCatalog.value = enabledCatalog.value.filter((item) => item !== slug);
        return;
    }

    enabledCatalog.value = [...enabledCatalog.value, slug];
}

function advanceToNext() {
    const next = steps.value[stepIndex.value + 1];

    if (next && isUnlocked(next)) {
        activeKey.value = next.key;
    }
}

function flattenErrors(serverErrors = {}) {
    const mapped = {};

    Object.entries(serverErrors).forEach(([key, value]) => {
        mapped[key] = Array.isArray(value) ? value[0] : value;
    });

    return mapped;
}

function onPhoneInput(value) {
    contact.phone = value;

    if (whatsappLinked.value) {
        contact.whatsapp = value;
    }
}

function onWhatsappInput(value) {
    contact.whatsapp = value;
    whatsappLinked.value = false;
}

function openOrdersModal(name) {
    openModal(name);
}

async function continueStep() {
    if (!canContinue.value || saving.value) {
        return;
    }

    clearErrors();

    if (activeKey.value === 'business') {
        const body = new FormData();
        body.append('industry', business.industry);
        body.append('name', business.name.trim());
        body.append('bio', business.bio.trim());

        if (logoFile.value) {
            body.append('logo', logoFile.value);
        }

        const result = await store.saveBusiness(body);

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        notifySuccess('تم حفظ بيانات النشاط');
        logoFile.value = null;

        if (tenant.value) {
            updateTenant({
                ...tenant.value,
                name: business.name.trim(),
                logo: store.forms.business.logo || tenant.value.logo,
            });
        }

        if (!isLastStep.value) {
            advanceToNext();
        }

        return;
    }

    if (activeKey.value === 'contact') {
        const result = await store.saveContact({
            phone: contact.phone.trim(),
            email: contact.email.trim(),
            whatsapp: contact.whatsapp.trim(),
            country: contact.country.trim(),
            city: contact.city.trim(),
            social_links: socialLinks.value.map((link) => ({
                network: link.network,
                url: link.url,
            })),
        });

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        notifySuccess('تم حفظ بيانات الاتصال');

        if (!isLastStep.value) {
            advanceToNext();
        }

        return;
    }

    if (activeKey.value === 'identity') {
        const result = await store.saveIdentity({
            primary_color: identity.primary_color,
            logo_radius: identity.logo_radius,
            font_family: identity.font_family,
        });

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        notifySuccess('تم حفظ الهوية');

        if (!isLastStep.value) {
            advanceToNext();
        }

        return;
    }

    if (activeKey.value === 'catalog') {
        const result = await store.saveCatalog({ enabled: enabledCatalog.value });

        if (!result.ok) {
            Object.assign(errors, flattenErrors(result.errors));
            notifyError(result.message ?? 'تعذر الحفظ');
            return;
        }

        notifySuccess('تم حفظ الكتالوج');

        if (!isLastStep.value) {
            advanceToNext();
        }

        return;
    }

    if (activeKey.value === 'orders') {
        await store.refreshQuiet();

        if (!forms.value.orders.payment_active || !forms.value.orders.verification_done) {
            notifyError('فعّل وسيلة دفع وأكمل التوثيق للمتابعة');
            return;
        }

        notifySuccess(completed.value || props.allowAllSteps ? 'تم حفظ الإعدادات' : 'تم إكمال إعداد الحساب');
        emit('finished');
    }
}
</script>

<template>
    <div dir="rtl">
        <div
            v-if="!embedded"
            class="border-b border-stone-100 bg-gradient-to-l from-stone-50 to-white px-4 py-4 sm:px-5"
        >
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs font-medium text-stone-500">إعداد الحساب</p>
                    <h2 class="mt-0.5 text-lg font-bold text-stone-900 sm:text-xl">أكمل إنشاء حسابك</h2>
                    <p class="mt-1 text-sm text-stone-500">خطوات سريعة عشان صفحتك تكون جاهزة تستقبل العملاء.</p>
                </div>
                <slot name="header-actions" />
            </div>

            <div class="mt-4">
                <div class="mb-2 flex items-center justify-between text-xs text-stone-500">
                    <span>{{ completedSteps }}/{{ totalSteps }} خطوات</span>
                    <span class="font-semibold text-stone-700">{{ percentage }}%</span>
                </div>
                <div class="h-1.5 overflow-hidden rounded-full bg-stone-100">
                    <div
                        class="h-full rounded-full bg-primary-500 transition-all duration-500"
                        :style="{ width: `${percentage}%` }"
                    ></div>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto border-b border-stone-100 px-3 py-3 sm:px-4">
            <div class="flex min-w-max items-center gap-1.5 sm:gap-2">
                <button
                    v-for="(step, index) in steps"
                    :key="step.key"
                    type="button"
                    class="group flex max-w-[9.5rem] flex-col items-start gap-1 rounded-xl px-2.5 py-2 text-start transition sm:max-w-none sm:px-3"
                    :class="[
                        activeKey === step.key
                            ? 'bg-primary-50 text-primary-800'
                            : isUnlocked(step)
                                ? 'text-stone-600 hover:bg-stone-50'
                                : 'cursor-not-allowed text-stone-300',
                    ]"
                    :disabled="!isUnlocked(step)"
                    @click="selectStep(step)"
                >
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold sm:text-xs">
                        <span
                            class="inline-flex size-5 items-center justify-center rounded-full text-[10px] font-bold"
                            :class="[
                                step.done
                                    ? 'bg-emerald-500 text-white'
                                    : activeKey === step.key
                                        ? 'bg-primary-600 text-white'
                                        : isUnlocked(step)
                                            ? 'bg-stone-200 text-stone-600'
                                            : 'bg-stone-100 text-stone-300',
                            ]"
                        >
                            <iconify-icon v-if="step.done" icon="hugeicons:tick-02" class="text-sm"></iconify-icon>
                            <template v-else>{{ index + 1 }}</template>
                        </span>
                        {{ step.title }}
                    </span>
                    <span class="hidden text-[11px] leading-tight text-stone-400 sm:block">{{ step.description }}</span>
                </button>
            </div>
        </div>

        <div class="px-4 pb-24 pt-4 sm:px-5 sm:pb-28">
            <div class="mb-4">
                <h3 class="text-base font-bold text-stone-900">{{ activeStep?.title }}</h3>
                <p class="mt-0.5 text-sm text-stone-500">{{ activeStep?.description }}</p>
            </div>

            <div v-if="activeKey === 'business'" class="space-y-3">
                <Select
                    v-model="business.industry"
                    name="industry"
                    label="المجال"
                    :options="industries"
                    :error="errors.industry"
                />
                <Input
                    v-model="business.name"
                    name="name"
                    label="اسم النشاط"
                    placeholder="اسم صفحتك أو نشاطك"
                    :error="errors.name"
                />
                <Textarea
                    v-model="business.bio"
                    name="bio"
                    label="البيو"
                    placeholder="نبذة قصيرة عن نشاطك"
                    :rows="3"
                    :error="errors.bio"
                />
                <FileCrop
                    v-model="logoFile"
                    v-model:preview="logoPreview"
                    name="logo"
                    label="الشعار"
                    upload-label="رفع شعار"
                    crop-title="قص الشعار"
                    shape="square"
                    :error="errors.logo"
                />
            </div>

            <div v-else-if="activeKey === 'contact'" class="space-y-3">
                <Input
                    :model-value="contact.phone"
                    name="phone"
                    label="الجوال"
                    placeholder="05xxxxxxxx"
                    dir="ltr"
                    :error="errors.phone"
                    @update:model-value="onPhoneInput"
                />
                <Input v-model="contact.email" name="email" type="email" label="البريد الإلكتروني" placeholder="hello@example.com" dir="ltr" :error="errors.email" />
                <Input
                    :model-value="contact.whatsapp"
                    name="whatsapp"
                    label="رقم الواتساب"
                    placeholder="9665xxxxxxxx"
                    dir="ltr"
                    :error="errors.whatsapp"
                    @update:model-value="onWhatsappInput"
                />
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <CountrySelect v-model="contact.country" name="country" :error="errors.country" />
                    <Input v-model="contact.city" name="city" label="المدينة" placeholder="الرياض" :error="errors.city" />
                </div>

                <div class="rounded-xl border border-stone-100 bg-stone-50/60 p-3">
                    <p class="mb-2 text-sm font-semibold text-stone-700">روابط السوشال ميديا</p>
                    <div class="space-y-2">
                        <Select
                            v-model="socialDraft.network"
                            name="social_network"
                            class="w-full"
                            width="w-full"
                            :options="socialNetworks"
                        />
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-[1fr_auto]">
                            <Input v-model="socialDraft.url" name="social_url" label="المعرف أو الرابط" placeholder="https://... أو @username" dir="ltr" />
                            <div class="flex items-end">
                                <Button type="button" variant="secondary" class="w-full sm:w-auto" label="إضافة" @click="addSocialLink" />
                            </div>
                        </div>
                    </div>
                    <p v-if="errors['social_links'] || errors['social_links.0.url']" class="mt-2 text-xs text-red-500">
                        {{ errors['social_links'] || errors['social_links.0.url'] }}
                    </p>

                    <ul v-if="socialLinks.length" class="mt-3 space-y-1.5">
                        <li
                            v-for="link in socialLinks"
                            :key="link.id"
                            class="flex items-center gap-2 rounded-lg border border-stone-100 bg-white px-2.5 py-2"
                        >
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium text-stone-800">{{ socialNetworks[link.network] ?? link.network }}</p>
                                <p class="truncate text-xs text-stone-400" dir="ltr">{{ link.url }}</p>
                            </div>
                            <button type="button" class="rounded-md p-1 text-red-400 hover:bg-red-50" @click="removeSocialLink(link.id)">
                                <iconify-icon icon="hugeicons:delete-02" class="text-lg"></iconify-icon>
                            </button>
                        </li>
                    </ul>
                    <p v-else class="mt-3 text-xs text-stone-400">أضف رابطاً واحداً على الأقل للمتابعة.</p>
                </div>
            </div>

            <div v-else-if="activeKey === 'identity'" class="space-y-4">
                <PickerColor
                    v-model="identity.primary_color"
                    name="primary_color"
                    label="اللون الأساسي"
                    :options="colorOptions"
                    allow-custom
                />
                <Radio
                    v-model="identity.logo_radius"
                    name="logo_radius"
                    label="انحناء الحدود"
                    :options="radiusOptions"
                    :error="errors.logo_radius"
                />
                <Radio
                    v-model="identity.font_family"
                    name="font_family"
                    label="نوع الخط"
                    :options="fonts"
                    :error="errors.font_family"
                />
            </div>

            <div v-else-if="activeKey === 'catalog'" class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <button
                    v-for="option in catalogOptions"
                    :key="option.slug"
                    type="button"
                    class="flex items-start gap-3 rounded-xl border px-3 py-3 text-start transition"
                    :class="enabledCatalog.includes(option.slug)
                        ? 'border-primary-300 bg-primary-50/70 ring-1 ring-primary-200'
                        : 'border-stone-100 bg-white hover:border-stone-200 hover:bg-stone-50'"
                    @click="toggleCatalog(option.slug)"
                >
                    <img :src="`/${option.icon}`" alt="" class="size-10 shrink-0 rounded-lg bg-white p-1.5 shadow-sm">
                    <span class="min-w-0 flex-1">
                        <span class="flex items-center justify-between gap-2">
                            <span class="block text-sm font-semibold text-stone-800">{{ option.name }}</span>
                            <span
                                class="inline-flex size-5 shrink-0 items-center justify-center rounded-full border"
                                :class="enabledCatalog.includes(option.slug)
                                    ? 'border-primary-500 bg-primary-500 text-white'
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
                    class="flex w-full items-center gap-3 rounded-xl border px-3 py-3.5 text-start transition"
                    :class="forms.orders.payment_active
                        ? 'border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50'
                        : 'border-stone-100 bg-white hover:border-stone-200 hover:bg-stone-50'"
                    @click="openOrdersModal('onboarding-payment')"
                >
                    <span
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl"
                        :class="forms.orders.payment_active ? 'bg-emerald-100 text-emerald-600' : 'bg-stone-100 text-stone-500'"
                    >
                        <iconify-icon :icon="forms.orders.payment_active ? 'hugeicons:checkmark-circle-02' : 'hugeicons:credit-card'" class="text-2xl"></iconify-icon>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-stone-800">تفعيل طرق الدفع</span>
                        <span class="block text-xs" :class="forms.orders.payment_active ? 'text-emerald-600' : 'text-stone-400'">
                            {{ forms.orders.payment_active ? 'تم التفعيل' : 'اختر بوابات الدفع اللي تناسبك' }}
                        </span>
                    </span>
                    <iconify-icon
                        v-if="forms.orders.payment_active"
                        icon="hugeicons:tick-02"
                        class="text-xl text-emerald-500"
                    ></iconify-icon>
                    <iconify-icon v-else icon="hugeicons:arrow-left-01" class="text-xl text-stone-300"></iconify-icon>
                </button>

                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl border px-3 py-3.5 text-start transition"
                    :class="forms.orders.shipping_active
                        ? 'border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50'
                        : 'border-stone-100 bg-white hover:border-stone-200 hover:bg-stone-50'"
                    @click="openOrdersModal('onboarding-shipping')"
                >
                    <span
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl"
                        :class="forms.orders.shipping_active ? 'bg-emerald-100 text-emerald-600' : 'bg-stone-100 text-stone-500'"
                    >
                        <iconify-icon :icon="forms.orders.shipping_active ? 'hugeicons:checkmark-circle-02' : 'hugeicons:delivery-truck-02'" class="text-2xl"></iconify-icon>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-stone-800">تفعيل طرق الشحن</span>
                        <span class="block text-xs" :class="forms.orders.shipping_active ? 'text-emerald-600' : 'text-stone-400'">
                            {{ forms.orders.shipping_active ? 'تم التفعيل' : 'اختياري حسب نوع منتجاتك' }}
                        </span>
                    </span>
                    <iconify-icon
                        v-if="forms.orders.shipping_active"
                        icon="hugeicons:tick-02"
                        class="text-xl text-emerald-500"
                    ></iconify-icon>
                    <iconify-icon v-else icon="hugeicons:arrow-left-01" class="text-xl text-stone-300"></iconify-icon>
                </button>

                <button
                    type="button"
                    class="flex w-full items-center gap-3 rounded-xl border px-3 py-3.5 text-start transition"
                    :class="forms.orders.verification_done
                        ? 'border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50'
                        : 'border-stone-100 bg-white hover:border-stone-200 hover:bg-stone-50'"
                    @click="openOrdersModal('onboarding-verification')"
                >
                    <span
                        class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl"
                        :class="forms.orders.verification_done ? 'bg-emerald-100 text-emerald-600' : 'bg-stone-100 text-stone-500'"
                    >
                        <iconify-icon :icon="forms.orders.verification_done ? 'hugeicons:checkmark-circle-02' : 'hugeicons:security-check'" class="text-2xl"></iconify-icon>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-stone-800">توثيق النشاط</span>
                        <span class="block text-xs" :class="forms.orders.verification_done ? 'text-emerald-600' : 'text-stone-400'">
                            {{ forms.orders.verification_done ? 'تم إرسال التوثيق' : 'مطلوب لاستقبال المدفوعات بأمان' }}
                        </span>
                    </span>
                    <iconify-icon
                        v-if="forms.orders.verification_done"
                        icon="hugeicons:tick-02"
                        class="text-xl text-emerald-500"
                    ></iconify-icon>
                    <iconify-icon v-else icon="hugeicons:arrow-left-01" class="text-xl text-stone-300"></iconify-icon>
                </button>

                <p class="pt-1 text-xs text-stone-400">
                    للمتابعة: فعّل وسيلة دفع واحدة على الأقل وأكمل طلب التوثيق. الشحن اختياري.
                </p>
                <p
                    v-if="!forms.orders.payment_active || !forms.orders.verification_done"
                    class="rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800"
                >
                    <template v-if="!forms.orders.payment_active && !forms.orders.verification_done">
                        فعّل طرق الدفع وأكمل التوثيق لتفعيل زر الحفظ.
                    </template>
                    <template v-else-if="!forms.orders.payment_active">
                        فعّل وسيلة دفع واحدة على الأقل، ثم اضغط «تم».
                    </template>
                    <template v-else>
                        أكمل توثيق النشاط لتفعيل زر الحفظ.
                    </template>
                </p>
            </div>
        </div>

        <div class="sticky bottom-0 border-t border-stone-100 bg-white/95 px-4 py-3 backdrop-blur sm:px-5">
            <div class="flex items-center gap-2">
                <Button
                    v-if="canGoBack"
                    type="button"
                    variant="ghost"
                    class="h-12 shrink-0 rounded-xl px-4"
                    label="رجوع"
                    @click="goBack"
                >
                    <template #icon>
                        <iconify-icon icon="hugeicons:arrow-right-01" class="text-xl"></iconify-icon>
                    </template>
                </Button>
                <Button
                    type="button"
                    class="h-12 flex-1 rounded-xl text-base font-semibold"
                    :disabled="!canContinue"
                    :loading="saving"
                    :label="nextLabel"
                    icon-position="end"
                    @click="continueStep"
                >
                    <template #icon>
                        <iconify-icon :icon="isLastStep ? 'hugeicons:checkmark-circle-02' : 'hugeicons:arrow-left-01'" class="text-xl"></iconify-icon>
                    </template>
                </Button>
            </div>
        </div>
    </div>
</template>
