<script setup>
import { onMounted, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Modal from '../../components/ui/Modal.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Button from '../../components/ui/Button.vue';
import Switch from '../../components/settings/Switch.vue';
import { openModal, closeModal } from '../../lib/modal.js';
import { api, ApiError } from '../../lib/api.js';

const methods = ref([]);
const activeSlug = ref(null);
const loading = ref(true);
const saving = ref(false);
const toggling = ref(null);
const message = ref(null);
const saved = ref(false);

const modalForm = reactive({
    label: '',
    description: '',
    instructions: '',
    min_limit: '',
    max_limit: '',
    public_key: '',
    secret_key: '',
    api_token: '',
    notification_token: '',
    accounts: [],
});

const errors = reactive({});

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
}

function applyList(payload) {
    methods.value = (payload?.data ?? []).map((item) => ({
        ...item,
        settings: item.settings ?? {},
    }));
}

async function load() {
    loading.value = true;
    message.value = null;

    try {
        applyList(await api('/settings/payment-options'));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحميل وسائل الدفع.';
    } finally {
        loading.value = false;
    }
}

function openMethod(slug) {
    const method = methods.value.find((item) => item.slug === slug);
    activeSlug.value = slug;
    clearErrors();

    const settings = method?.settings ?? {};
    Object.assign(modalForm, {
        label: settings.label ?? '',
        description: settings.description ?? '',
        instructions: settings.instructions ?? '',
        min_limit: settings.min_limit != null ? String(settings.min_limit) : '',
        max_limit: settings.max_limit != null ? String(settings.max_limit) : '',
        public_key: settings.public_key ?? '',
        secret_key: settings.secret_key ?? '',
        api_token: settings.api_token ?? '',
        notification_token: settings.notification_token ?? '',
        accounts: Array.isArray(settings.accounts)
            ? settings.accounts.map((account) => ({ ...account }))
            : [],
    });

    if (slug === 'bank-transfer' && modalForm.accounts.length === 0) {
        modalForm.accounts.push({
            id: '',
            bank_name: '',
            account_name: '',
            iban: '',
            account_number: '',
        });
    }

    openModal(`payment-method-${slug}`);
}

function addBankAccount() {
    modalForm.accounts.push({
        id: '',
        bank_name: '',
        account_name: '',
        iban: '',
        account_number: '',
    });
}

function removeBankAccount(index) {
    modalForm.accounts.splice(index, 1);
}

function settingsBody(slug) {
    if (slug === 'bank-transfer') {
        return {
            accounts: modalForm.accounts.map((account) => ({
                id: account.id || undefined,
                bank_name: account.bank_name,
                account_name: account.account_name,
                iban: account.iban || null,
                account_number: account.account_number || null,
            })),
        };
    }

    if (slug === 'cash-on-delivery') {
        return {
            label: modalForm.label || null,
            description: modalForm.description || null,
            min_limit: modalForm.min_limit !== '' ? Number(modalForm.min_limit) : null,
        };
    }

    if (slug === 'tabby') {
        return {
            label: modalForm.label || null,
            description: modalForm.description || null,
            public_key: modalForm.public_key || null,
            secret_key: modalForm.secret_key || null,
            min_limit: modalForm.min_limit !== '' ? Number(modalForm.min_limit) : null,
            max_limit: modalForm.max_limit !== '' ? Number(modalForm.max_limit) : null,
        };
    }

    if (slug === 'tamara') {
        return {
            label: modalForm.label || null,
            description: modalForm.description || null,
            api_token: modalForm.api_token || null,
            notification_token: modalForm.notification_token || null,
            min_limit: modalForm.min_limit !== '' ? Number(modalForm.min_limit) : null,
        };
    }

    if (slug === 'custom') {
        return {
            label: modalForm.label,
            description: modalForm.description || null,
            instructions: modalForm.instructions || null,
        };
    }

    return {
        label: modalForm.label || null,
        description: modalForm.description || null,
    };
}

async function toggleActive(method) {
    const next = !method.active;
    toggling.value = method.slug;
    message.value = null;

    try {
        const payload = await api(`/settings/payment-options/${method.slug}/active`, {
            method: 'PUT',
            body: { active: next },
        });
        const data = payload?.data ?? payload;
        const index = methods.value.findIndex((item) => item.slug === method.slug);

        if (index !== -1) {
            methods.value[index] = { ...methods.value[index], ...data };
        }
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحديث حالة الوسيلة.';
    } finally {
        toggling.value = null;
    }
}

async function saveMethod() {
    if (!activeSlug.value) {
        return;
    }

    saving.value = true;
    saved.value = false;
    message.value = null;
    clearErrors();

    try {
        const payload = await api(`/settings/payment-options/${activeSlug.value}`, {
            method: 'PUT',
            body: settingsBody(activeSlug.value),
        });
        const data = payload?.data ?? payload;
        const index = methods.value.findIndex((item) => item.slug === activeSlug.value);

        if (index !== -1) {
            methods.value[index] = { ...methods.value[index], ...data };
        }

        closeModal(`payment-method-${activeSlug.value}`);
        saved.value = true;
        setTimeout(() => {
            saved.value = false;
        }, 2000);
    } catch (error) {
        if (error instanceof ApiError) {
            message.value = error.message;
            for (const [key, messages] of Object.entries(error.errors ?? {})) {
                errors[key] = messages?.[0] ?? null;
            }
        } else {
            message.value = 'تعذر حفظ إعدادات الوسيلة.';
        }
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <SettingsShell title="وسائل الدفع">
        <p v-if="message" class="mb-4 text-sm text-red-500">{{ message }}</p>
        <p v-if="saved" class="mb-4 text-sm text-emerald-600">تم الحفظ.</p>

        <MainBox title="وسائل الدفع" subtitle="قم بتفعيل وتخصيص وسائل الدفع المناسبة لجمهورك.">
            <template #icon>
                <img :src="`/assets/icons/business/017-atm-card.svg`" alt="" class="h-6 w-6">
            </template>

            <p v-if="loading" class="px-4 py-6 text-sm text-gray-400">جاري التحميل...</p>

            <div v-else class="divide-y divide-dotted divide-gray-200 border-t border-dotted border-gray-200">
                <div
                    v-for="method in methods"
                    :key="method.slug"
                    class="group flex items-center gap-4 px-4 py-4 transition hover:bg-gray-50/80"
                >
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-4 text-start"
                        @click="openMethod(method.slug)"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800">{{ method.name }}</p>
                            <p class="mt-0.5 line-clamp-2 text-xs text-gray-500">{{ method.description }}</p>
                        </div>
                        <div class="shrink-0 rounded-lg border border-gray-100 bg-white p-2">
                            <img :src="method.icon_url || `/${method.icon}`" :alt="method.name" class="h-8 w-auto max-w-[72px] object-contain">
                        </div>
                    </button>

                    <Switch
                        :model-value="method.active"
                        :label="method.active ? `تعطيل ${method.name}` : `تفعيل ${method.name}`"
                        :disabled="toggling === method.slug"
                        @update:model-value="toggleActive(method)"
                    />
                </div>
            </div>
        </MainBox>

        <Modal
            v-for="method in methods"
            :key="`modal-${method.slug}`"
            :title="method.name"
            size="3xl"
            :name="`payment-method-${method.slug}`"
        >
            <Form class="!rounded-none" @submit="saveMethod">
                <template v-if="method.slug === 'bank-transfer'">
                    <div
                        v-for="(account, index) in modalForm.accounts"
                        :key="index"
                        class="mb-3 space-y-2 rounded-xl border border-gray-100 bg-gray-50/50 p-3"
                    >
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-700">حساب بنكي {{ index + 1 }}</p>
                            <button
                                v-if="modalForm.accounts.length > 1"
                                type="button"
                                class="text-xs text-red-500"
                                @click="removeBankAccount(index)"
                            >
                                حذف
                            </button>
                        </div>
                        <Input v-model="account.bank_name" :name="`bank_name_${index}`" label="اسم البنك" />
                        <Input v-model="account.account_name" :name="`account_name_${index}`" label="اسم صاحب الحساب" />
                        <Input v-model="account.iban" :name="`iban_${index}`" label="IBAN" dir="ltr" />
                        <Input v-model="account.account_number" :name="`account_number_${index}`" label="رقم الحساب" dir="ltr" />
                    </div>
                    <Button type="button" variant="secondary" label="إضافة حساب" @click="addBankAccount" />
                </template>

                <template v-else>
                    <Input
                        v-model="modalForm.label"
                        name="label"
                        label="العنوان الظاهر"
                        :placeholder="method.name"
                        :error="errors.label"
                    />
                    <Input
                        v-model="modalForm.description"
                        name="description"
                        label="وصف مختصر"
                        placeholder="اختياري"
                        :error="errors.description"
                    />
                    <Input
                        v-if="method.slug === 'custom'"
                        v-model="modalForm.instructions"
                        name="instructions"
                        label="تعليمات الدفع"
                        placeholder="اختياري"
                        :error="errors.instructions"
                    />
                    <Input
                        v-if="['cash-on-delivery', 'tabby', 'tamara'].includes(method.slug)"
                        v-model="modalForm.min_limit"
                        name="min_limit"
                        label="الحد الأدنى"
                        placeholder="0"
                        dir="ltr"
                        :error="errors.min_limit"
                    />
                    <Input
                        v-if="method.slug === 'tabby'"
                        v-model="modalForm.max_limit"
                        name="max_limit"
                        label="الحد الأعلى"
                        placeholder="0"
                        dir="ltr"
                        :error="errors.max_limit"
                    />
                    <Input
                        v-if="method.slug === 'tabby'"
                        v-model="modalForm.public_key"
                        name="public_key"
                        label="Public Key"
                        dir="ltr"
                    />
                    <Input
                        v-if="method.slug === 'tabby'"
                        v-model="modalForm.secret_key"
                        name="secret_key"
                        label="Secret Key"
                        dir="ltr"
                    />
                    <Input
                        v-if="method.slug === 'tamara'"
                        v-model="modalForm.api_token"
                        name="api_token"
                        label="API Token"
                        dir="ltr"
                    />
                    <Input
                        v-if="method.slug === 'tamara'"
                        v-model="modalForm.notification_token"
                        name="notification_token"
                        label="Notification Token"
                        dir="ltr"
                    />
                </template>

                <template #footer>
                    <div class="flex gap-2">
                        <Button type="button" variant="ghost" label="إلغاء" @click="closeModal(`payment-method-${method.slug}`)" />
                        <Button type="submit" label="حفظ" :disabled="saving" />
                    </div>
                </template>
            </Form>
        </Modal>
    </SettingsShell>
</template>
