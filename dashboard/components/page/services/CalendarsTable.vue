<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import Button from '../../ui/Button.vue';
import Modal from '../../ui/Modal.vue';
import Empty from '../../ui/Empty.vue';
import Dropdown from '../../Dropdown.vue';
import CalendarForm from './CalendarForm.vue';
import { useServicesStore } from '../../../stores/services.js';
import { openModal } from '../../../lib/modal.js';

const store = useServicesStore();
const search = ref('');
const editingCalendarId = ref(null);
let searchTimer = null;

onMounted(() => {
    store.fetchCalendars();
});

watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        store.fetchCalendars({ search: value });
    }, 300);
});

const calendarsEmpty = computed(
    () => store.calendarsLoaded && !store.calendarsLoading && store.calendars.length === 0,
);

const modalTitle = computed(() => (editingCalendarId.value ? 'تعديل الأصل' : 'أضف أصل جديد'));

function openAdd() {
    editingCalendarId.value = null;
    openModal('service-calendar-form');
}

function openEdit(calendarId) {
    editingCalendarId.value = calendarId;
    openModal('service-calendar-form');
}

async function remove(calendarId) {
    if (!confirm('هل أنت متأكد من حذف هذا الأصل؟')) {
        return;
    }

    await store.deleteCalendar(calendarId);
}
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="border-b border-stone-100 px-4 py-4">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-base font-semibold text-gray-800">الأصول القابلة للحجز</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        مقدمي الخدمات بالساعة، يمكنك إدارة ساعات العمل المتاحة للحجز من هنا.
                    </p>
                </div>

                <Button label="أضف جديد" @click="openAdd">
                    <template #icon>
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="4" width="16" height="16" rx="2" /><path stroke-linecap="round" d="M12 8v8M8 12h8" /></svg>
                    </template>
                </Button>
            </div>
        </div>

        <div class="flex w-full items-center gap-x-4 bg-gray-100 p-3">
            <div class="flex-grow">
                <div class="relative text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="ابحث .."
                        class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                    >
                </div>
            </div>

            <Modal
                :title="modalTitle"
                size="3xl"
                name="service-calendar-form"
            >
                <CalendarForm
                    :key="`calendar-${editingCalendarId ?? 'new'}`"
                    :calendar-id="editingCalendarId"
                    modal-name="service-calendar-form"
                />
            </Modal>
        </div>

        <div class="relative overflow-x-auto">
            <div v-if="store.calendarsLoading && !store.calendarsLoaded" class="flex items-center justify-center p-10 text-sm text-gray-500">
                جاري التحميل…
            </div>

            <div v-else-if="store.calendarsError" class="p-6 text-center text-sm text-red-600">
                {{ store.calendarsError }}
            </div>

            <Empty v-else-if="calendarsEmpty" subtitle="سيتم عرض مقدمي الخدمات هنا بعد إضافتهم.">
                لا توجد أصول قابلة للحجز.
                <template #icon>
                    <svg class="h-12 w-12 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" /><path stroke-linecap="round" d="M16 2v4M8 2v4M3 10h18" /></svg>
                </template>
            </Empty>

            <table v-else class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-start font-medium">الاسم</th>
                        <th class="px-4 py-3 text-start font-medium">النوع</th>
                        <th class="px-4 py-3 text-start font-medium">تاريخ البداية</th>
                        <th class="px-4 py-3 text-start font-medium">تاريخ النهاية</th>
                        <th class="px-4 py-3 text-end font-medium" />
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="calendar in store.calendars"
                        :key="calendar.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="px-6 py-4">
                            <div class="flex min-w-0 items-center gap-2">
                                <span
                                    v-if="calendar.active"
                                    class="h-2 w-2 shrink-0 rounded-full bg-emerald-500"
                                />
                                <button
                                    type="button"
                                    class="truncate text-start font-medium text-gray-800 transition hover:text-primary-600"
                                    @click="openEdit(calendar.id)"
                                >
                                    {{ calendar.name }}
                                </button>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-gray-600">{{ calendar.type_label }}</td>
                        <td class="px-4 py-4 text-gray-600">{{ calendar.from_label ?? '—' }}</td>
                        <td class="px-4 py-4 text-gray-600">{{ calendar.to_label ?? '—' }}</td>
                        <td class="px-4 py-4 text-end">
                            <Dropdown width="w-40" class="inline-block">
                                <template #trigger>
                                    <button type="button" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" aria-label="menu">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                    </button>
                                </template>
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-x-2 rounded p-1.5 text-start hover:bg-stone-100"
                                    @click="openEdit(calendar.id)"
                                >
                                    تعديل
                                </button>
                                <button
                                    type="button"
                                    class="flex w-full items-center gap-x-2 rounded p-1.5 text-start text-red-600 hover:bg-stone-100"
                                    @click="remove(calendar.id)"
                                >
                                    حذف
                                </button>
                            </Dropdown>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="store.calendarsLoading && store.calendarsLoaded"
                class="absolute inset-0 bg-white opacity-50"
            />

            <div
                v-if="store.calendarsLoading && store.calendarsLoaded"
                class="absolute inset-0 flex items-center justify-center"
            >
                <svg class="h-10 w-10 animate-spin text-gray-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 3v3M12 18v3M4.2 4.2l2.1 2.1M17.7 17.7l2.1 2.1M3 12h3M18 12h3M4.2 19.8l2.1-2.1M17.7 6.3l2.1-2.1" /></svg>
            </div>
        </div>
    </div>
</template>
