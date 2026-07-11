<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Empty from '../../components/ui/Empty.vue';
import Modal from '../../components/ui/Modal.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Toggle from '../../components/ui/Toggle.vue';
import Button from '../../components/ui/Button.vue';
import Switch from '../../components/settings/Switch.vue';
import { openModal, closeModal } from '../../lib/modal.js';
import { api, ApiError } from '../../lib/api.js';

defineProps({
    embedded: { type: Boolean, default: false },
});

const methods = ref([]);
const customOptions = ref([]);
const countryOptions = ref({
    '*': 'كل الدول',
    SA: 'المملكة العربية السعودية',
});

const editingCustomId = ref(null);
const methodForm = reactive({
    label: '',
    domestic_price: '',
    gulf_price: '',
    international_price: '',
});
const customForm = reactive({
    name: '',
    price: '',
    country: 'SA',
    active: true,
});

const loading = ref(true);
const saving = reactive({ method: false, custom: false });
const toggling = ref(null);
const message = ref(null);
const errors = reactive({});

const customModalTitle = computed(() => (editingCustomId.value ? 'تعديل خدمة شحن' : 'أضف خدمة شحن'));

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
}

function applyPayload(payload) {
    const data = payload?.data ?? payload;
    methods.value = (data.methods ?? []).map((item) => ({
        ...item,
        settings: item.settings ?? {},
    }));
    customOptions.value = (data.custom_options ?? []).map((item) => ({
        ...item,
        cities_summary: item.all_cities || item.country === '*' ? 'كل المدن' : (item.cities_summary ?? ''),
    }));
}

async function load() {
    loading.value = true;
    message.value = null;

    try {
        applyPayload(await api('/settings/shipping-options'));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحميل وسائل الشحن.';
    } finally {
        loading.value = false;
    }
}

function openMethod(slug) {
    const method = methods.value.find((item) => item.slug === slug);
    const settings = method?.settings ?? {};
    clearErrors();
    Object.assign(methodForm, {
        label: settings.label ?? '',
        domestic_price: settings.domestic_price != null ? String(settings.domestic_price) : '',
        gulf_price: settings.gulf_price != null ? String(settings.gulf_price) : '',
        international_price: settings.international_price != null ? String(settings.international_price) : '',
    });
    openModal(`shipping-method-${slug}`);
}

async function toggleMethod(method) {
    const next = !method.active;
    toggling.value = method.slug;
    message.value = null;

    try {
        const payload = await api(`/settings/shipping-options/methods/${method.slug}/active`, {
            method: 'PUT',
            body: { active: next },
        });
        const data = payload?.data ?? payload;
        const index = methods.value.findIndex((item) => item.slug === method.slug);

        if (index !== -1) {
            methods.value[index] = { ...methods.value[index], ...data };
        }
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحديث حالة الشحن.';
    } finally {
        toggling.value = null;
    }
}

async function toggleCustom(option) {
    const next = !option.active;
    toggling.value = option.id;
    message.value = null;

    try {
        await api(`/settings/shipping-options/custom/${option.id}/active`, {
            method: 'PUT',
            body: { active: next },
        });
        option.active = next;
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحديث حالة الخيار.';
    } finally {
        toggling.value = null;
    }
}

function openCustomForm(option = null) {
    editingCustomId.value = option?.id ?? null;
    clearErrors();
    Object.assign(customForm, {
        name: option?.name ?? '',
        price: option?.price != null ? String(option.price) : '',
        country: option?.country ?? 'SA',
        active: option?.active ?? true,
    });
    openModal('custom-shipping-form');
}

async function saveMethod(slug) {
    saving.method = true;
    message.value = null;
    clearErrors();

    try {
        const payload = await api(`/settings/shipping-options/methods/${slug}`, {
            method: 'PUT',
            body: {
                label: methodForm.label || null,
                domestic_price: methodForm.domestic_price !== '' ? Number(methodForm.domestic_price) : null,
                gulf_price: methodForm.gulf_price !== '' ? Number(methodForm.gulf_price) : null,
                international_price: methodForm.international_price !== '' ? Number(methodForm.international_price) : null,
            },
        });
        const data = payload?.data ?? payload;
        const index = methods.value.findIndex((item) => item.slug === slug);

        if (index !== -1) {
            methods.value[index] = { ...methods.value[index], ...data };
        }

        closeModal(`shipping-method-${slug}`);
    } catch (error) {
        if (error instanceof ApiError) {
            message.value = error.message;
            for (const [key, messages] of Object.entries(error.errors ?? {})) {
                errors[key] = messages?.[0] ?? null;
            }
        } else {
            message.value = 'تعذر حفظ إعدادات الشحن.';
        }
    } finally {
        saving.method = false;
    }
}

async function saveCustom() {
    saving.custom = true;
    message.value = null;
    clearErrors();

    const body = {
        name: customForm.name.trim(),
        price: Number(customForm.price) || 0,
        country: customForm.country,
        all_cities: true,
        city_ids: ['*'],
        active: Boolean(customForm.active),
    };

    try {
        if (editingCustomId.value) {
            await api(`/settings/shipping-options/custom/${editingCustomId.value}`, {
                method: 'PUT',
                body,
            });
        } else {
            await api('/settings/shipping-options/custom', {
                method: 'POST',
                body,
            });
        }

        closeModal('custom-shipping-form');
        await load();
    } catch (error) {
        if (error instanceof ApiError) {
            message.value = error.message;
            for (const [key, messages] of Object.entries(error.errors ?? {})) {
                errors[key] = messages?.[0] ?? null;
            }
        } else {
            message.value = 'تعذر حفظ خدمة الشحن.';
        }
    } finally {
        saving.custom = false;
    }
}

onMounted(load);
</script>

<template>
    <SettingsShell title="وسائل الشحن" :embedded="embedded">
        <p v-if="message" class="mb-4 text-sm text-red-500">{{ message }}</p>
        <p v-if="loading" class="mb-4 text-sm text-gray-400">جاري التحميل...</p>

        <MainBox title="وسائل الشحن" subtitle="قم بتفعيل وتخصيص طرق الشحن المتاحة لعملائك.">
            <template #icon>
                <img :src="`/assets/icons/ecommerce/018-cart.svg`" alt="" class="h-6 w-6">
            </template>

            <div class="divide-y divide-dotted divide-gray-200 border-t border-dotted border-gray-200">
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
                        @update:model-value="toggleMethod(method)"
                    />
                </div>
            </div>
        </MainBox>

        <MainBox title="خيارات الشحن المخصصة" subtitle="مناديب وشركات الشحن الخاصة المتعاقد معهم خارج المنصة.">
            <template #actions>
                <Button label="أضف خدمة شحن" @click="openCustomForm()" />
            </template>

            <div class="divide-y divide-dotted divide-gray-200 border-t border-dotted border-gray-200">
                <Empty v-if="customOptions.length === 0" subtitle="سيتم عرض خيارات الشحن المخصصة هنا بعد إضافتها.">
                    لا توجد خيارات شحن مخصصة.
                </Empty>

                <div
                    v-for="option in customOptions"
                    :key="option.id"
                    class="group flex items-center gap-4 px-4 py-4 transition hover:bg-gray-50/80"
                >
                    <button
                        type="button"
                        class="flex min-w-0 flex-1 items-center gap-4 text-start"
                        @click="openCustomForm(option)"
                    >
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                            <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M7 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM17 17a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z" /><path d="M5 17H3V7h11v10h-1M14 9h4l3 4v4h-2" /></svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm font-semibold"
                                :class="option.active ? 'text-gray-800' : 'text-gray-400 line-through'"
                            >
                                {{ option.name }}
                            </p>
                            <p class="mt-0.5 truncate text-xs text-gray-500">
                                {{ option.price }} ر.س · {{ option.country_label }}
                                <template v-if="option.cities_summary"> · {{ option.cities_summary }}</template>
                            </p>
                        </div>
                    </button>
                    <Switch
                        :model-value="option.active"
                        :label="option.active ? `تعطيل ${option.name}` : `تفعيل ${option.name}`"
                        :disabled="toggling === option.id"
                        @update:model-value="toggleCustom(option)"
                    />
                </div>
            </div>
        </MainBox>

        <Modal
            v-for="method in methods"
            :key="`ship-modal-${method.slug}`"
            :title="method.name"
            size="3xl"
            :name="`shipping-method-${method.slug}`"
        >
            <Form class="!rounded-none" @submit="saveMethod(method.slug)">
                <Input v-model="methodForm.label" name="label" label="العنوان الظاهر" :placeholder="method.name" :error="errors.label" />
                <Input v-model="methodForm.domestic_price" name="domestic_price" label="سعر الشحن الداخلي" placeholder="0" dir="ltr" :error="errors.domestic_price" />
                <Input v-model="methodForm.gulf_price" name="gulf_price" label="سعر الشحن الخليجي" placeholder="0" dir="ltr" :error="errors.gulf_price" />
                <Input v-model="methodForm.international_price" name="international_price" label="سعر الشحن الدولي" placeholder="0" dir="ltr" :error="errors.international_price" />
                <template #footer>
                    <div class="flex gap-2">
                        <Button type="button" variant="ghost" label="إلغاء" @click="closeModal(`shipping-method-${method.slug}`)" />
                        <Button type="submit" label="حفظ" :disabled="saving.method" />
                    </div>
                </template>
            </Form>
        </Modal>

        <Modal :title="customModalTitle" size="3xl" name="custom-shipping-form">
            <Form class="!rounded-none" @submit="saveCustom">
                <Toggle v-model="customForm.active" name="active" label="الحالة" />
                <Input v-model="customForm.name" name="name" label="اسم الخدمة" placeholder="مندوب الرياض" :error="errors.name" />
                <Input v-model="customForm.price" name="price" label="السعر" placeholder="25" dir="ltr" :error="errors.price" />
                <Select
                    v-model="customForm.country"
                    name="country"
                    label="الدولة"
                    :options="countryOptions"
                    :error="errors.country"
                />
                <template #footer>
                    <div class="flex gap-2">
                        <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('custom-shipping-form')" />
                        <Button type="submit" :label="editingCustomId ? 'تعديل' : 'إضافة'" :disabled="saving.custom" />
                    </div>
                </template>
            </Form>
        </Modal>
    </SettingsShell>
</template>
