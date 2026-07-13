<script setup>
import { onMounted, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import Switch from '../../components/settings/Switch.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Button from '../../components/ui/Button.vue';
import { analyticsProviders as fallbackProviders } from '../../data/settings.js';
import { api, ApiError } from '../../lib/api.js';
import { notifySuccess, notifyApiError } from '../../lib/notify.js';

const fallbackByKey = Object.fromEntries(fallbackProviders.map((provider) => [provider.key, provider]));

const providers = ref(fallbackProviders.map((provider) => ({ ...provider })));
const integrations = reactive({});
const loading = ref(true);
const saving = ref(false);
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

function mapProvider(key, meta = {}) {
    const fallback = fallbackByKey[key] ?? {};

    return {
        key,
        name: meta.name ?? fallback.name ?? key,
        description: meta.description ?? fallback.description ?? '',
        label: meta.label ?? fallback.label ?? '',
        placeholder: meta.placeholder ?? fallback.placeholder ?? '',
        icon: meta.icon ?? fallback.icon ?? '',
    };
}

function applyPayload(payload) {
    const data = payload?.data ?? payload;
    const providerMap = data.providers ?? {};

    providers.value = Object.entries(providerMap).map(([key, meta]) => mapProvider(key, meta));

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
        notifySuccess('تم الحفظ.');
    } catch (error) {
        if (error instanceof ApiError) {
            for (const provider of providers.value) {
                const field = error.errors?.[`integrations.${provider.key}.identifier`]?.[0]
                    ?? error.errors?.[`integrations.${provider.key}.active`]?.[0]
                    ?? null;
                errors[provider.key] = field;
            }
        }
        notifyApiError(error, 'تعذر حفظ إعدادات الإحصائيات.');
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

            <div v-if="loading" class="flex items-center justify-center px-4 py-10">
                <LoadingSpinner size="sm" />
            </div>
            <p v-else-if="message" class="px-4 pt-3 text-sm text-red-500">{{ message }}</p>

            <Form v-if="!loading" @submit="submit">
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <article
                        v-for="provider in providers"
                        :key="provider.key"
                        class="flex flex-col gap-4 rounded-2xl border border-stone-100 bg-stone-50/40 p-4 transition hover:border-stone-200 hover:bg-white"
                        :class="integrations[provider.key].active ? 'ring-1 ring-primary-200/80' : ''"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3">
                                <div class="flex size-11 shrink-0 items-center justify-center rounded-xl border border-stone-100 bg-white shadow-sm">
                                    <img
                                        v-if="provider.icon"
                                        :src="provider.icon"
                                        :alt="provider.name"
                                        class="size-6 object-contain"
                                    >
                                </div>
                                <div class="min-w-0">
                                    <h3 class="text-sm font-semibold text-stone-800">{{ provider.name }}</h3>
                                    <p class="mt-1 text-xs leading-5 text-stone-500">{{ provider.description }}</p>
                                </div>
                            </div>

                            <div class="flex shrink-0 flex-col items-end gap-1">
                                <Switch
                                    v-model="integrations[provider.key].active"
                                    :label="integrations[provider.key].active ? `تعطيل ${provider.name}` : `تفعيل ${provider.name}`"
                                />
                                <span
                                    class="text-[10px] font-medium"
                                    :class="integrations[provider.key].active ? 'text-primary-600' : 'text-stone-400'"
                                >
                                    {{ integrations[provider.key].active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </div>
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
                    </article>

                    <div class="flex min-h-[160px] flex-col items-center justify-center rounded-2xl border border-dashed border-stone-200 bg-stone-50/30 p-6 text-center md:col-span-1">
                        <p class="text-sm font-semibold text-stone-500">قريباً</p>
                        <p class="mt-1 text-xs text-stone-400">سيتم إضافة المزيد من التكاملات لاحقاً.</p>
                    </div>
                </div>

                <template #footer>
                    <Button type="submit" label="حفظ" :disabled="saving" />
                </template>
            </Form>
        </MainBox>
    </SettingsShell>
</template>
