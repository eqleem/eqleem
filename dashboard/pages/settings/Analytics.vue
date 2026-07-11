<script setup>
import { onMounted, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Toggle from '../../components/ui/Toggle.vue';
import Button from '../../components/ui/Button.vue';
import { analyticsProviders as fallbackProviders } from '../../data/settings.js';
import { api, ApiError } from '../../lib/api.js';

const providers = ref(fallbackProviders.map((provider) => ({ ...provider })));
const integrations = reactive({});
const loading = ref(true);
const saving = ref(false);
const saved = ref(false);
const message = ref(null);
const errors = reactive({});

function ensureIntegrations(list) {
    for (const provider of list) {
        if (!integrations[provider.key]) {
            integrations[provider.key] = { identifier: '', active: false };
        }
    }
}

ensureIntegrations(providers.value);

function applyPayload(payload) {
    const data = payload?.data ?? payload;
    const providerMap = data.providers ?? {};

    providers.value = Object.entries(providerMap).map(([key, meta]) => ({
        key,
        name: meta.name,
        label: meta.label,
        placeholder: meta.placeholder ?? '',
    }));

    if (providers.value.length === 0) {
        providers.value = fallbackProviders.map((provider) => ({ ...provider }));
    }

    ensureIntegrations(providers.value);

    for (const provider of providers.value) {
        const row = data.integrations?.[provider.key] ?? {};
        integrations[provider.key] = {
            identifier: row.identifier ?? '',
            active: Boolean(row.active),
        };
        errors[provider.key] = null;
    }
}
   
async function load() {
    loading.value = true;
    message.value = null; 

    try {
        applyPayload(await api('/settings/analytics'));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحميل إعدادات الإحصائيات.';
    } finally {
        loading.value = false;
    }
}

async function submit() {
    saving.value = true;
    saved.value = false;
    message.value = null;
    Object.keys(errors).forEach((key) => {
        errors[key] = null;
    });

    try {
        const body = {
            integrations: Object.fromEntries(
                providers.value.map((provider) => [
                    provider.key,
                    {
                        identifier: integrations[provider.key]?.identifier ?? '',
                        active: Boolean(integrations[provider.key]?.active),
                    },
                ]),
            ),
        };

        applyPayload(await api('/settings/analytics', { method: 'PUT', body }));
        saved.value = true;
        setTimeout(() => {
            saved.value = false;
        }, 2000);
    } catch (error) {
        if (error instanceof ApiError) {
            message.value = error.message;
            for (const provider of providers.value) {
                const field = error.errors?.[`integrations.${provider.key}.identifier`]?.[0]
                    ?? error.errors?.[`integrations.${provider.key}.active`]?.[0]
                    ?? null;
                errors[provider.key] = field;
            }
        } else {
            message.value = 'تعذر حفظ إعدادات الإحصائيات.';
        }
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <SettingsShell title="ربط الإحصائيات">
        <MainBox title="ربط الإحصائيات" subtitle="ألحق أكواد التتبع لبدء قياس زيارات وإحصائيات صفحتك.">
            <template #icon>
                <img :src="`/assets/icons/business/030-growth-chart.svg`" class="h-7 w-7" alt="">
            </template>

            <p v-if="loading" class="px-4 py-6 text-sm text-gray-400">جاري التحميل...</p>
            <p v-else-if="message && !saved" class="px-4 pt-3 text-sm text-red-500">{{ message }}</p>

            <Form v-if="!loading" @submit="submit">
                <div class="flex flex-col gap-3">
                    <div
                        v-for="provider in providers"
                        :key="provider.key"
                        class="rounded-xl border border-gray-100 bg-gray-50/50 p-4"
                    >
                        <div class="mb-3 flex items-center justify-between gap-4">
                            <h3 class="text-sm font-semibold text-gray-700">{{ provider.name }}</h3>
                            <Toggle
                                v-model="integrations[provider.key].active"
                                :name="`${provider.key}-active`"
                                label="تفعيل"
                                label-width="w-auto"
                            />
                        </div>
                        <Input
                            v-model="integrations[provider.key].identifier"
                            :name="`${provider.key}-identifier`"
                            :label="provider.label"
                            :placeholder="provider.placeholder"
                            :error="errors[provider.key]"
                            dir="ltr"
                            block
                        />
                    </div>

                    <div class="flex min-h-[80px] flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50/30 p-4 text-center">
                        <p class="text-sm font-semibold text-gray-500">قريباً</p>
                        <p class="mt-1 text-xs text-gray-400">سيتم إضافة المزيد من التكاملات لاحقاً.</p>
                    </div>
                </div>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <span v-if="saved" class="text-sm text-emerald-600">تم الحفظ.</span>
                        <Button type="submit" label="حفظ" :disabled="saving" />
                    </div>
                </template>
            </Form>
        </MainBox>
    </SettingsShell>
</template>
