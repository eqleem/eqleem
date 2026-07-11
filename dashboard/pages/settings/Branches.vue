<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Empty from '../../components/ui/Empty.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Toggle from '../../components/ui/Toggle.vue';
import Button from '../../components/ui/Button.vue';
import Modal from '../../components/ui/Modal.vue';
import { weekdayLabels as fallbackWeekdays } from '../../data/settings.js';
import { openModal, closeModal } from '../../lib/modal.js';
import { api, ApiError } from '../../lib/api.js';

const search = ref('');
const branches = ref([]);
const countries = ref({ SA: 'المملكة العربية السعودية' });
const cities = ref({});
const weekdayLabels = ref({ ...fallbackWeekdays });

const editingId = ref(null);
const form = reactive(emptyForm());
const loading = ref(true);
const saving = ref(false);
const message = ref(null);
const errors = reactive({});

const results = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return branches.value;
    }

    return branches.value.filter((branch) => {
        return [branch.name, branch.city, branch.country_label, branch.address]
            .join(' ')
            .toLowerCase()
            .includes(query);
    });
});

const modalTitle = computed(() => (editingId.value ? 'تعديل' : 'أضف فرع'));

const cityOptions = computed(() => {
    if (Object.keys(cities.value).length) {
        return cities.value;
    }

    return {};
});

function defaultHours() {
    return Object.fromEntries(
        Object.keys(weekdayLabels.value).map((day) => [
            day,
            { enabled: !['friday', 'saturday'].includes(day), start: '09:00', end: '17:00' },
        ]),
    );
}

function emptyForm() {
    return {
        active: true,
        name: '',
        country: 'SA',
        city: '',
        address: '',
        postal_code: '',
        email: '',
        phonecode: '+966',
        phone: '',
        is_warehouse: false,
        is_pickup: false,
        working_hours: defaultHours(),
    };
}

function clearErrors() {
    Object.keys(errors).forEach((key) => {
        delete errors[key];
    });
}

function applyList(payload) {
    branches.value = payload?.data ?? [];

    if (payload?.meta?.countries) {
        countries.value = payload.meta.countries;
    }

    if (payload?.meta?.cities) {
        cities.value = payload.meta.cities;
    }

    if (payload?.meta?.weekday_labels) {
        weekdayLabels.value = payload.meta.weekday_labels;
    }
}

async function load() {
    loading.value = true;
    message.value = null;

    try {
        applyList(await api('/settings/branches'));
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر تحميل الفروع.';
    } finally {
        loading.value = false;
    }
}

function openAdd() {
    editingId.value = null;
    Object.assign(form, emptyForm());
    clearErrors();
    openModal('branch-form');
}

function openEdit(branch) {
    editingId.value = branch.id;
    Object.assign(form, {
        ...emptyForm(),
        active: Boolean(branch.active),
        name: branch.name ?? '',
        country: branch.country ?? 'SA',
        city: branch.city ?? '',
        address: branch.address ?? '',
        postal_code: branch.postal_code ?? '',
        email: branch.email ?? '',
        phonecode: branch.phonecode ?? '+966',
        phone: branch.phone ?? '',
        is_warehouse: Boolean(branch.is_warehouse),
        is_pickup: Boolean(branch.is_pickup),
        working_hours: { ...defaultHours(), ...(branch.working_hours ?? {}) },
    });
    clearErrors();
    openModal('branch-form');
}

function payloadBody() {
    return {
        name: form.name.trim(),
        country: form.country,
        city: form.city,
        address: form.address || null,
        postal_code: form.postal_code || null,
        email: form.email || null,
        phonecode: form.phonecode || null,
        phone: form.phone || null,
        active: Boolean(form.active),
        is_warehouse: Boolean(form.is_warehouse),
        is_pickup: Boolean(form.is_pickup),
        working_hours: form.working_hours,
    };
}

async function submit() {
    saving.value = true;
    message.value = null;
    clearErrors();

    try {
        if (editingId.value) {
            await api(`/settings/branches/${editingId.value}`, {
                method: 'PUT',
                body: payloadBody(),
            });
        } else {
            await api('/settings/branches', {
                method: 'POST',
                body: payloadBody(),
            });
        }

        closeModal('branch-form');
        await load();
    } catch (error) {
        if (error instanceof ApiError) {
            message.value = error.message;
            for (const [key, messages] of Object.entries(error.errors ?? {})) {
                errors[key] = messages?.[0] ?? null;
            }
        } else {
            message.value = 'تعذر حفظ الفرع.';
        }
    } finally {
        saving.value = false;
    }
}

async function removeBranch() {
    if (!editingId.value || !confirm('هل أنت متأكد من حذف هذا الفرع؟')) {
        return;
    }

    saving.value = true;
    message.value = null;

    try {
        await api(`/settings/branches/${editingId.value}`, { method: 'DELETE' });
        closeModal('branch-form');
        await load();
    } catch (error) {
        message.value = error instanceof ApiError ? error.message : 'تعذر حذف الفرع.';
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <SettingsShell title="الفروع">
        <p v-if="message" class="mb-4 text-sm text-red-500">{{ message }}</p>

        <MainBox title="الفروع" subtitle="قائمة الفروع والمستودعات.">
            <template #icon>
                <img :src="`/assets/icons/business/010-location.svg`" alt="" class="h-6 w-6">
            </template>
            <template #actions>
                <Button label="أضف فرع" @click="openAdd" />
            </template>

            <div class="divide-y divide-dotted divide-gray-200 border-t border-dotted border-gray-200">
                <div class="bg-gray-100 p-3">
                    <div class="relative text-sm text-gray-800">
                        <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="ابحث .."
                            class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm"
                        >
                    </div>
                </div>

                <p v-if="loading" class="px-4 py-6 text-sm text-gray-400">جاري التحميل...</p>

                <Empty v-else-if="results.length === 0" subtitle="سيتم عرض الفروع هنا بعد إضافتها.">
                    لا توجد فروع.
                </Empty>

                <ul v-else class="divide-y divide-gray-100">
                    <li v-for="branch in results" :key="branch.id">
                        <button
                            type="button"
                            class="group flex w-full items-center gap-3 px-4 py-3 text-start transition hover:bg-gray-50"
                            @click="openEdit(branch)"
                        >
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50">
                                <svg class="h-5 w-5 text-red-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11z" /><circle cx="12" cy="10" r="2.5" /></svg>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-gray-800">{{ branch.name }}</p>
                                <p class="mt-0.5 truncate text-xs text-gray-500">{{ branch.city }} · {{ branch.address }}</p>
                            </div>
                        </button>
                    </li>
                </ul>
            </div>
        </MainBox>

        <Modal :title="modalTitle" size="3xl" name="branch-form">
            <Form class="max-h-[75vh] overflow-y-auto !rounded-none" @submit="submit">
                <Toggle v-model="form.active" name="active" label="الحالة" />
                <Input v-model="form.name" name="name" label="الاسم" placeholder="الفرع الرئيسي" :error="errors.name" />
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <Select
                        v-model="form.country"
                        name="country"
                        label="الدولة"
                        :options="countries"
                        :error="errors.country"
                    />
                    <Select
                        v-if="Object.keys(cityOptions).length"
                        v-model="form.city"
                        name="city"
                        label="المدينة"
                        :options="cityOptions"
                        :error="errors.city"
                    />
                    <Input
                        v-else
                        v-model="form.city"
                        name="city"
                        label="المدينة"
                        placeholder="الرياض"
                        :error="errors.city"
                    />
                </div>
                <Input v-model="form.address" name="address" label="العنوان" placeholder="الحي واسم الشارع" :error="errors.address" />
                <Input v-model="form.postal_code" name="postal_code" label="الرمز البريدي" placeholder="12345" dir="ltr" :error="errors.postal_code" />
                <Input v-model="form.email" name="email" label="البريد الإلكتروني" placeholder="a@aa.aaa" type="email" dir="ltr" :error="errors.email" />
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <Select
                        v-model="form.phonecode"
                        name="phonecode"
                        label="رمز الدولة"
                        :options="{ '+966': '+966', '+971': '+971', '+20': '+20' }"
                    />
                    <div class="sm:col-span-2">
                        <Input v-model="form.phone" name="phone" label="رقم الجوال" placeholder="512345678" dir="ltr" :error="errors.phone" />
                    </div>
                </div>
                <Toggle v-model="form.is_warehouse" name="is_warehouse" label="مستودع تخزين؟" />
                <Toggle v-model="form.is_pickup" name="is_pickup" label="يمكن الاستلام منه؟" />

                <div class="mt-2 overflow-hidden rounded-xl border border-gray-200">
                    <div class="bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">ساعات العمل</div>
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="(label, day) in weekdayLabels"
                            :key="day"
                            class="flex flex-wrap items-center gap-3 px-4 py-3"
                        >
                            <label class="flex w-28 shrink-0 items-center gap-2">
                                <input v-model="form.working_hours[day].enabled" type="checkbox" class="rounded border-gray-300">
                                <span class="text-sm text-gray-700">{{ label }}</span>
                            </label>
                            <div class="flex min-w-[220px] flex-1 items-center gap-2">
                                <input
                                    v-model="form.working_hours[day].start"
                                    type="time"
                                    :disabled="!form.working_hours[day].enabled"
                                    class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                                >
                                <span class="text-sm text-gray-400">إلى</span>
                                <input
                                    v-model="form.working_hours[day].end"
                                    type="time"
                                    :disabled="!form.working_hours[day].enabled"
                                    class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                <template #footer>
                    <div class="flex w-full items-center justify-between gap-3">
                        <button
                            v-if="editingId"
                            type="button"
                            class="text-sm text-red-500 hover:text-red-600"
                            :disabled="saving"
                            @click="removeBranch"
                        >
                            حذف؟
                        </button>
                        <span v-else></span>
                        <div class="flex items-center gap-2">
                            <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('branch-form')" />
                            <Button type="submit" :label="editingId ? 'تعديل' : 'إضافة'" :disabled="saving" />
                        </div>
                    </div>
                </template>
            </Form>
        </Modal>
    </SettingsShell>
</template>
