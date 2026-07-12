<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Select from '../../components/ui/Select.vue';
import Button from '../../components/ui/Button.vue';
import { languages as fallbackLanguages, currencies as fallbackCurrencies } from '../../data/settings.js';
import { api, ApiError } from '../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../lib/notify.js';

const languages = ref({ ...fallbackLanguages });
const currencies = ref({ ...fallbackCurrencies });

const form = reactive({
    defaultLanguage: 'ar',
    defaultCurrency: 'SAR',
    availableLanguages: ['ar'],
    availableCurrencies: ['SAR'],
});

const loading = ref(true);
const saving = reactive({ language: false, currency: false });
const message = ref(null);
const errors = reactive({
    default_language: null,
    default_currency: null,
    available_languages: null,
    available_currencies: null,
});

const defaultLanguageOptions = computed(() => {
    return Object.fromEntries(
        Object.entries(languages.value).filter(([code]) => form.availableLanguages.includes(code)),
    );
});

const defaultCurrencyOptions = computed(() => {
    return Object.fromEntries(
        Object.entries(currencies.value).filter(([code]) => form.availableCurrencies.includes(code)),
    );
});

watch(
    () => [...form.availableLanguages],
    (codes) => {
        if (!codes.includes(form.defaultLanguage) && codes.length) {
            form.defaultLanguage = codes[0];
        }
    },
);

watch(
    () => [...form.availableCurrencies],
    (codes) => {
        if (!codes.includes(form.defaultCurrency) && codes.length) {
            form.defaultCurrency = codes[0];
        }
    },
);

function applyPayload(payload) {
    const data = payload?.data ?? payload;

    if (data.languages) {
        languages.value = data.languages;
    }

    if (data.currencies) {
        currencies.value = data.currencies;
    }

    form.defaultLanguage = data.default_language ?? form.defaultLanguage;
    form.defaultCurrency = data.default_currency ?? form.defaultCurrency;
    form.availableLanguages = [...(data.available_languages ?? form.availableLanguages)];
    form.availableCurrencies = [...(data.available_currencies ?? form.availableCurrencies)];
}

function clearErrors() {
    errors.default_language = null;
    errors.default_currency = null;
    errors.available_languages = null;
    errors.available_currencies = null;
}

async function load() {
    loading.value = true;
    message.value = null;

    try {
        applyPayload(await api('/settings/language-currency'));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحميل إعدادات اللغة والعملة.';
    } finally {
        loading.value = false;
    }
}

async function save(section) {
    saving[section] = true;
    message.value = null;
    clearErrors();

    try {
        const payload = await api('/settings/language-currency', {
            method: 'PUT',
            body: {
                default_language: form.defaultLanguage,
                default_currency: form.defaultCurrency,
                available_languages: form.availableLanguages,
                available_currencies: form.availableCurrencies,
            },
        });
        applyPayload(payload);
        notifySuccess('تم الحفظ.');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.default_language = error.errors?.default_language?.[0] ?? null;
            errors.default_currency = error.errors?.default_currency?.[0] ?? null;
            errors.available_languages = error.errors?.available_languages?.[0] ?? null;
            errors.available_currencies = error.errors?.available_currencies?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر حفظ الإعدادات.');
    } finally {
        saving[section] = false;
    }
}

function submitLanguage() {
    return save('language');
}

function submitCurrency() {
    return save('currency');
}

onMounted(load);
</script>

<template>
    <SettingsShell title="اللغة والعملة">
        <p v-if="message" class="mb-4 text-sm text-red-500">{{ message }}</p>
        <div v-if="loading" class="mb-4 flex items-center justify-center"><LoadingSpinner size="sm" /></div>

        <MainBox title="اللغة" subtitle="حدد اللغة الافتراضية واللغات المتاحة لزوار صفحتك.">
            <Form @submit="submitLanguage">
                <Select
                    v-model="form.defaultLanguage"
                    name="defaultLanguage"
                    label="اللغة الافتراضية"
                    :options="defaultLanguageOptions"
                    :error="errors.default_language"
                />

                <div class="relative items-start gap-x-2 rounded-md bg-stone-100/75 p-1 lg:flex">
                    <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-stone-500">اللغات المتاحة</span>
                    <div class="flex flex-wrap gap-2 p-2">
                        <label
                            v-for="(label, code) in languages"
                            :key="code"
                            class="flex items-center gap-2 rounded bg-white px-3 py-2 text-sm"
                        >
                            <input v-model="form.availableLanguages" type="checkbox" :value="code" class="rounded border-stone-300">
                            {{ label }}
                        </label>
                    </div>
                </div>
                <p v-if="errors.available_languages" class="px-2 text-xs text-red-500">{{ errors.available_languages }}</p>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <Button type="submit" label="حفظ" :disabled="loading || saving.language" />
                    </div>
                </template>
            </Form>
        </MainBox>

        <MainBox title="العملة" subtitle="حدد العملة الافتراضية والعملات المتاحة للاستخدام في صفحتك.">
            <Form @submit="submitCurrency">
                <Select
                    v-model="form.defaultCurrency"
                    name="defaultCurrency"
                    label="العملة الافتراضية"
                    :options="defaultCurrencyOptions"
                    :error="errors.default_currency"
                />

                <div class="relative items-start gap-x-2 rounded-md bg-stone-100/75 p-1 lg:flex">
                    <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-stone-500">العملات المتاحة</span>
                    <div class="flex flex-wrap gap-2 p-2">
                        <label
                            v-for="(label, code) in currencies"
                            :key="code"
                            class="flex items-center gap-2 rounded bg-white px-3 py-2 text-sm"
                        >
                            <input v-model="form.availableCurrencies" type="checkbox" :value="code" class="rounded border-stone-300">
                            {{ label }}
                        </label>
                    </div>
                </div>
                <p v-if="errors.available_currencies" class="px-2 text-xs text-red-500">{{ errors.available_currencies }}</p>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <Button type="submit" label="حفظ" :disabled="loading || saving.currency" />
                    </div>
                </template>
            </Form>
        </MainBox>
    </SettingsShell>
</template>
