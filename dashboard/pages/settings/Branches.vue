<script setup>
import { computed, reactive, ref } from 'vue';
import SettingsShell from '../../components/settings/SettingsShell.vue';
import MainBox from '../../components/ui/MainBox.vue';
import Empty from '../../components/ui/Empty.vue';
import Form from '../../components/ui/Form.vue';
import Input from '../../components/ui/Input.vue';
import Select from '../../components/ui/Select.vue';
import Toggle from '../../components/ui/Toggle.vue';
import Button from '../../components/ui/Button.vue';
import Modal from '../../components/ui/Modal.vue';
import { weekdayLabels } from '../../data/settings.js';
import { openModal, closeModal } from '../../lib/modal.js';

// Port of resources/views/admin/settings/branches/branches.blade.php (dummy data).
const search = ref('');
const branches = ref([
    { id: 1, name: 'الفرع الرئيسي', city: 'الرياض', country: 'السعودية', address: 'حي المروج', active: true, isWarehouse: true, isPickup: true, phone: '512345678', phonecode: '+966', email: 'branch@example.com', postalCode: '12345', workingHours: defaultHours() },
    { id: 2, name: 'مستودع جدة', city: 'جدة', country: 'السعودية', address: 'حي الصفا', active: true, isWarehouse: true, isPickup: false, phone: '555555555', phonecode: '+966', email: '', postalCode: '', workingHours: defaultHours() },
]);

const editingId = ref(null);
const form = reactive(emptyForm());

const results = computed(() => {
    const query = search.value.trim().toLowerCase();

    if (!query) {
        return branches.value;
    }

    return branches.value.filter((branch) => {
        return [branch.name, branch.city, branch.country, branch.address]
            .join(' ')
            .toLowerCase()
            .includes(query);
    });
});

const modalTitle = computed(() => (editingId.value ? 'تعديل' : 'أضف فرع'));

function defaultHours() {
    return Object.fromEntries(
        Object.keys(weekdayLabels).map((day) => [
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
        postalCode: '',
        email: '',
        phonecode: '+966',
        phone: '',
        isWarehouse: false,
        isPickup: false,
        workingHours: defaultHours(),
    };
}

function openAdd() {
    editingId.value = null;
    Object.assign(form, emptyForm());
    openModal('branch-form');
}

function openEdit(branch) {
    editingId.value = branch.id;
    Object.assign(form, {
        ...emptyForm(),
        ...branch,
        workingHours: { ...defaultHours(), ...(branch.workingHours ?? {}) },
    });
    openModal('branch-form');
}

function submit() {
    if (!form.name.trim()) {
        return;
    }

    if (editingId.value) {
        const index = branches.value.findIndex((item) => item.id === editingId.value);

        if (index !== -1) {
            branches.value[index] = {
                ...branches.value[index],
                ...JSON.parse(JSON.stringify(form)),
                id: editingId.value,
            };
        }
    } else {
        branches.value.push({
            id: Date.now(),
            ...JSON.parse(JSON.stringify(form)),
        });
    }

    closeModal('branch-form');
}

function removeBranch() {
    if (!editingId.value || !confirm('هل أنت متأكد من حذف هذا الفرع؟')) {
        return;
    }

    branches.value = branches.value.filter((item) => item.id !== editingId.value);
    closeModal('branch-form');
}
</script>

<template>
    <SettingsShell title="الفروع">
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

                <Empty v-if="results.length === 0" subtitle="سيتم عرض الفروع هنا بعد إضافتها.">
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
                <Input v-model="form.name" name="name" label="الاسم" placeholder="الفرع الرئيسي" />
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    <Select
                        v-model="form.country"
                        name="country"
                        label="الدولة"
                        :options="{ SA: 'السعودية', AE: 'الإمارات', EG: 'مصر' }"
                    />
                    <Input v-model="form.city" name="city" label="المدينة" placeholder="الرياض" />
                </div>
                <Input v-model="form.address" name="address" label="العنوان" placeholder="الحي واسم الشارع" />
                <Input v-model="form.postalCode" name="postalCode" label="الرمز البريدي" placeholder="12345" dir="ltr" />
                <Input v-model="form.email" name="email" label="البريد الإلكتروني" placeholder="a@aa.aaa" type="email" dir="ltr" />
                <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                    <Select
                        v-model="form.phonecode"
                        name="phonecode"
                        label="رمز الدولة"
                        :options="{ '+966': '+966', '+971': '+971', '+20': '+20' }"
                    />
                    <div class="sm:col-span-2">
                        <Input v-model="form.phone" name="phone" label="رقم الجوال" placeholder="512345678" dir="ltr" />
                    </div>
                </div>
                <Toggle v-model="form.isWarehouse" name="isWarehouse" label="مستودع تخزين؟" />
                <Toggle v-model="form.isPickup" name="isPickup" label="يمكن الاستلام منه؟" />

                <div class="mt-2 overflow-hidden rounded-xl border border-gray-200">
                    <div class="bg-gray-50 px-4 py-2 text-sm font-medium text-gray-700">ساعات العمل</div>
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="(label, day) in weekdayLabels"
                            :key="day"
                            class="flex flex-wrap items-center gap-3 px-4 py-3"
                        >
                            <label class="flex w-28 shrink-0 items-center gap-2">
                                <input v-model="form.workingHours[day].enabled" type="checkbox" class="rounded border-gray-300">
                                <span class="text-sm text-gray-700">{{ label }}</span>
                            </label>
                            <div class="flex min-w-[220px] flex-1 items-center gap-2">
                                <input
                                    v-model="form.workingHours[day].start"
                                    type="time"
                                    :disabled="!form.workingHours[day].enabled"
                                    class="rounded-lg border border-gray-200 px-2 py-1.5 text-sm text-gray-700 disabled:bg-gray-100 disabled:text-gray-400"
                                >
                                <span class="text-sm text-gray-400">إلى</span>
                                <input
                                    v-model="form.workingHours[day].end"
                                    type="time"
                                    :disabled="!form.workingHours[day].enabled"
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
                            @click="removeBranch"
                        >
                            حذف؟
                        </button>
                        <span v-else></span>
                        <div class="flex items-center gap-2">
                            <Button type="button" variant="ghost" label="إلغاء" @click="closeModal('branch-form')" />
                            <Button type="submit" :label="editingId ? 'تعديل' : 'إضافة'" />
                        </div>
                    </div>
                </template>
            </Form>
        </Modal>
    </SettingsShell>
</template>
