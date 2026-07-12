<script setup>
import { computed, reactive, ref, watch } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Button from '../../components/ui/Button.vue';
import Badge from '../../components/ui/Badge.vue';
import { api, ApiError } from '../../lib/api.js';
import { notifyApiSuccess, notifyApiError } from '../../lib/notify.js';
import { useSession, updateTenant } from '../../stores/session.js';
import { appDomain as fallbackDomain } from '../../data/settings.js';

// Port of resources/views/admin/settings/info/domain.blade.php
const { tenant } = useSession();

const form = reactive({
    handle: '',
    customDomain: '',
    customDomainStatus: null,
});

const errors = reactive({
    handle: null,
    custom_domain: null,
});

const saving = reactive({
    handle: false,
    custom: false,
});

const message = ref(null);

const appDomain = computed(() => tenant.value?.app_domain || fallbackDomain);

watch(
    tenant,
    (value) => {
        if (!value) {
            return;
        }

        form.handle = value.handle ?? '';
        form.customDomain = value.custom_domain ?? '';
        form.customDomainStatus = value.custom_domain_status ?? null;
    },
    { immediate: true },
);

const statusLabel = computed(() => {
    return {
        pending: 'قيد التحقق',
        active: 'مُفعّل',
        failed: 'فشل التحقق',
    }[form.customDomainStatus] ?? 'غير مُضاف';
});

const statusColor = computed(() => {
    return {
        pending: 'yellow',
        active: 'green',
        failed: 'red',
    }[form.customDomainStatus] ?? 'gray';
});

const customDomainHost = computed(() => {
    if (!form.customDomain) {
        return 'www';
    }

    const parts = form.customDomain.split('.');

    return parts.length <= 2 ? '@' : parts.slice(0, -2).join('.');
});

function applyTenant(payload) {
    const data = payload?.data ?? payload;
    updateTenant(data);
    form.handle = data.handle ?? form.handle;
    form.customDomain = data.custom_domain ?? '';
    form.customDomainStatus = data.custom_domain_status ?? null;
    message.value = payload?.message ?? null;
}

async function submitHandle() {
    saving.handle = true;
    errors.handle = null;
    message.value = null;

    try {
        const payload = await api('/settings/domain/handle', {
            method: 'PUT',
            body: { handle: form.handle.trim() },
        });
        applyTenant(payload);
        notifyApiSuccess(payload, 'تم الحفظ.');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.handle = error.errors?.handle?.[0] ?? null;
        }
        notifyApiError(error, 'تعذر حفظ الرابط.');
    } finally {
        saving.handle = false;
    }
}

async function submitCustomDomain() {
    saving.custom = true;
    errors.custom_domain = null;
    message.value = null;

    const value = form.customDomain.trim().toLowerCase().replace(/^https?:\/\//, '').replace(/\/$/, '');
    form.customDomain = value;

    try {
        const payload = await api('/settings/domain/custom', {
            method: 'PUT',
            body: { custom_domain: value || null },
        });
        applyTenant(payload);
        notifyApiSuccess(payload, 'تم الحفظ.');
    } catch (error) {
        if (error instanceof ApiError) {
            errors.custom_domain = error.errors?.custom_domain?.[0] ?? null;
        }

        notifyApiError(error, 'تعذر حفظ الدومين المخصص.');
    } finally {
        saving.custom = false;
    }
}
</script>

<template>
    <SettingsShell title="الدومين">
        <MainBox title="الدومين المجاني" subtitle="تعديل الدومين الفرعي المجاني من إقليم في حال كنت لا تملك دومين مخصص بعد.">
            <template #icon>
                <img :src="`/assets/icons/business/015-cloud-network.svg`" class="h-7 w-7" alt="">
            </template>

            <Form @submit="submitHandle">
                <Input
                    v-model="form.handle"
                    name="handle"
                    label="رابط الصفحة"
                    placeholder="admin"
                    prefix="https://"
                    dir="ltr"
                    :suffix="`.${appDomain}`"
                    :error="errors.handle"
                />

                <template #footer>
                    <div class="flex items-center gap-3">
                        <Button type="submit" label="حفظ" :disabled="saving.handle" />
                    </div>
                </template>
            </Form>
        </MainBox>

        <MainBox title="الدومين المخصص" subtitle="اربط دومينك الخاص بصفحتك على إقليم.">
            <template #icon>
                <svg class="h-7 w-7 text-stone-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18" /></svg>
            </template>
            <template #actions>
                <Badge :color="statusColor">{{ statusLabel }}</Badge>
            </template>

            <Form @submit="submitCustomDomain">
                <Input
                    v-model="form.customDomain"
                    name="customDomain"
                    label="الدومين المخصص"
                    placeholder="shop.example.com"
                    dir="ltr"
                    info="أدخل الدومين بدون https:// — مثل shop.example.com أو www.example.com"
                    :error="errors.custom_domain"
                />

                <div v-if="form.customDomain" class="mx-4 mb-2 rounded-xl border border-dashed border-blue-200 bg-blue-50/60 p-4">
                    <p class="text-sm font-semibold text-blue-800">إعدادات DNS — سجل CNAME</p>
                    <p class="mt-1 text-xs text-blue-700/80">أضف السجل التالي من لوحة تحكم مزوّد الدومين. قد يستغرق الانتشار من 5 دقائق إلى 48 ساعة.</p>
                    <dl class="mt-3 divide-y divide-blue-100 rounded-lg border border-blue-100 bg-white text-sm">
                        <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                            <dt class="text-xs font-medium text-stone-500">النوع</dt>
                            <dd class="font-mono text-xs text-stone-800" dir="ltr">CNAME</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                            <dt class="text-xs font-medium text-stone-500">الاسم (Host)</dt>
                            <dd class="font-mono text-xs text-stone-800" dir="ltr">{{ customDomainHost }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 px-3 py-2.5">
                            <dt class="text-xs font-medium text-stone-500">القيمة (يشير إلى)</dt>
                            <dd class="break-all text-left font-mono text-xs text-stone-800" dir="ltr">host.{{ appDomain }}</dd>
                        </div>
                    </dl>
                </div>
                <div v-else class="mx-4 mb-2 rounded-xl border border-dashed border-stone-200 bg-stone-50/60 p-4">
                    <p class="text-xs text-stone-500">بعد إدخال الدومين المخصص، ستظهر هنا تعليمات إعداد سجل CNAME في DNS.</p>
                </div>

                <template #footer>
                    <div class="flex items-center gap-3">
                        <Button type="submit" label="حفظ" :disabled="saving.custom" />
                    </div>
                </template>
            </Form>
        </MainBox>
    </SettingsShell>
</template>
