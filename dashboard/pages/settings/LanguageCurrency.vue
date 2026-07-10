<script setup>
import { computed, reactive, ref, watch } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Select from '../../components/ui/Select.vue';
import Button from '../../components/ui/Button.vue';
import { languages, currencies } from '../../data/settings.js';

// Port of resources/views/admin/settings/info/language-currency.blade.php (dummy data).
const form = reactive({
    defaultLanguage: 'ar',
    defaultCurrency: 'SAR',
    availableLanguages: ['ar', 'en'],
    availableCurrencies: ['SAR', 'USD'],
});

const saved = ref(null);

const defaultLanguageOptions = computed(() => {
    return Object.fromEntries(
        Object.entries(languages).filter(([code]) => form.availableLanguages.includes(code)),
    );
});

const defaultCurrencyOptions = computed(() => {
    return Object.fromEntries(
        Object.entries(currencies).filter(([code]) => form.availableCurrencies.includes(code)),
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

function flash(key) {
    saved.value = key;
    setTimeout(() => {
        if (saved.value === key) {
            saved.value = null;
        }
    }, 2000);
}

function submitLanguage() {
    flash('language');
}

function submitCurrency() {
    flash('currency');
}
</script>

<template>
    <SettingsShell title="اللغة والعملة">
        <MainBox title="اللغة" subtitle="حدد اللغة الافتراضية واللغات المتاحة لزوار صفحتك.">
            <Form @submit="submitLanguage">
                <Select
                    v-model="form.defaultLanguage"
                    name="defaultLanguage"
                    label="اللغة الافتراضية"
                    :options="defaultLanguageOptions"
                />

                <div class="relative items-start gap-x-2 rounded-md bg-gray-100/75 p-1 lg:flex">
                    <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-gray-500">اللغات المتاحة</span>
                    <div class="flex flex-wrap gap-2 p-2">
                        <label
                            v-for="(label, code) in languages"
                            :key="code"
                            class="flex items-center gap-2 rounded bg-white px-3 py-2 text-sm"
                        >
                            <input v-model="form.availableLanguages" type="checkbox" :value="code" class="rounded border-gray-300">
                            {{ label }}
                        </label>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <span v-if="saved === 'language'" class="text-sm text-emerald-600">تم الحفظ.</span>
                        <Button type="submit" label="حفظ" />
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
                />

                <div class="relative items-start gap-x-2 rounded-md bg-gray-100/75 p-1 lg:flex">
                    <span class="inline-block w-36 shrink-0 p-2 text-sm font-semibold text-gray-500">العملات المتاحة</span>
                    <div class="flex flex-wrap gap-2 p-2">
                        <label
                            v-for="(label, code) in currencies"
                            :key="code"
                            class="flex items-center gap-2 rounded bg-white px-3 py-2 text-sm"
                        >
                            <input v-model="form.availableCurrencies" type="checkbox" :value="code" class="rounded border-gray-300">
                            {{ label }}
                        </label>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <span v-if="saved === 'currency'" class="text-sm text-emerald-600">تم الحفظ.</span>
                        <Button type="submit" label="حفظ" />
                    </div>
                </template>
            </Form>
        </MainBox>
    </SettingsShell>
</template>
