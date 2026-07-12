<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import Button from '../../ui/Button.vue';
import Modal from '../../ui/Modal.vue';
import Dropdown from '../../Dropdown.vue';
import AddNewsletter from './AddNewsletter.vue';
import { useNewsletterStore } from '../../../stores/newsletter.js';
import { openModal } from '../../../lib/modal.js';

const store = useNewsletterStore();
const search = ref('');
const selectedIds = ref([]);
let searchTimer = null;

onMounted(() => {
    store.fetchIssues({ page: 1 });
});

watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        store.setSearch(value);
        selectedIds.value = [];
    }, 300);
});

const allSelected = computed({
    get: () => store.items.length > 0 && store.items.every((item) => selectedIds.value.includes(String(item.id))),
    set: (value) => {
        selectedIds.value = value ? store.items.map((item) => String(item.id)) : [];
    },
});

function toggleOne(id, checked) {
    const key = String(id);

    if (checked) {
        if (!selectedIds.value.includes(key)) {
            selectedIds.value = [...selectedIds.value, key];
        }
        return;
    }

    selectedIds.value = selectedIds.value.filter((item) => item !== key);
}

function mailStatusClass(status) {
    if (status === 'sent') {
        return 'bg-emerald-100 text-emerald-700';
    }

    if (status === 'scheduled') {
        return 'bg-amber-100 text-amber-700';
    }

    return 'bg-gray-100 text-gray-600';
}

function formatRecipients(count) {
    const value = Number(count);

    return value > 0 ? new Intl.NumberFormat('ar').format(value) : '—';
}

async function removeSelected() {
    if (selectedIds.value.length === 0) {
        return;
    }

    if (!confirm('هل أنت متأكد من حذف العناصر المحددة؟')) {
        return;
    }

    await store.deleteIssues(selectedIds.value);
    selectedIds.value = [];
}
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-4 bg-white p-3">
            <div class="hidden ps-3 sm:block">
                <input v-model="allSelected" type="checkbox" class="h-4 w-4 rounded-xl border-gray-300 shadow-sm">
            </div>

            <div class="flex-grow">
                <div class="relative text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="ابحث .."
                        class="block w-full bg-gray-100 rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                    >
                </div>
            </div>

            <div v-if="selectedIds.length > 0" class="hidden items-center gap-x-2 sm:flex">
                <div class="flex items-center gap-1 text-sm text-gray-600">
                    <span>{{ selectedIds.length }}</span>
                    <span>محددة</span>
                </div>
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg border bg-white px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-200"
                    :disabled="store.saving"
                    @click="removeSelected"
                >
                    حذف المحددة ({{ selectedIds.length }})
                </button>
            </div>

            <Button label="نشرة جديدة" @click="openModal('add-newsletter')">
                <template #icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                </template>
            </Button>

            <Modal title="إضافة نشرة بريدية جديدة" size="2xl" name="add-newsletter">
                <AddNewsletter />
            </Modal>
        </div>

        <div class="relative overflow-x-auto">
            <div v-if="store.loading && !store.loaded" class="flex items-center justify-center p-10"><LoadingSpinner size="lg" /></div>

            <div v-else-if="store.error" class="p-6 text-center text-sm text-red-600">
                {{ store.error }}
            </div>

            <div v-else-if="store.isEmpty" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-12 w-12 opacity-50" alt="">
                <p class="text-gray-700">لا توجد نشرات بريدية.</p>
                <small class="text-gray-500">سيتم عرض النشرات البريدية هنا بعد إضافتها.</small>
            </div>

            <table v-else class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-500">
                    <tr>
                        <th class="hidden w-10 px-6 py-3 text-start font-medium sm:table-cell"></th>
                        <th class="px-4 py-3 text-start font-medium">النشرة</th>
                        <th class="px-4 py-3 text-start font-medium">حالة الإرسال</th>
                        <th class="px-4 py-3 text-start font-medium">التاريخ</th>
                        <th class="px-4 py-3 text-start font-medium">المستلمين</th>
                        <th class="px-4 py-3 text-start font-medium">الموقع</th>
                        <th class="px-4 py-3 text-end font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="item in store.items" :key="item.uuid" class="hover:bg-gray-50">
                        <td class="hidden px-6 py-4 sm:table-cell">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded-xl border-gray-300 shadow-sm"
                                :checked="selectedIds.includes(String(item.id))"
                                @change="toggleOne(item.id, $event.target.checked)"
                            >
                        </td>
                        <td class="px-4 py-4">
                            <RouterLink
                                :to="`/manage/newsletter/detail/${item.uuid}`"
                                class="block min-w-0 transition hover:text-primary-600"
                            >
                                <p class="truncate font-medium text-gray-800">{{ item.title }}</p>
                                <p v-if="item.subject" class="mt-0.5 truncate text-xs text-gray-500">{{ item.subject }}</p>
                            </RouterLink>
                        </td>
                        <td class="px-4 py-4">
                            <span
                                class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium"
                                :class="mailStatusClass(item.mail_status)"
                            >
                                {{ item.mail_status_label }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-4 text-gray-600">
                            <template v-if="item.display_date_label">
                                <span v-if="item.date_kind_label" class="block text-xs text-gray-400">{{ item.date_kind_label }}</span>
                                <span dir="ltr">{{ item.display_date_label }}</span>
                            </template>
                            <span v-else class="text-gray-400">—</span>
                        </td>
                        <td class="px-4 py-4 text-gray-600">
                            {{ formatRecipients(item.recipients_count) }}
                        </td>
                        <td class="px-4 py-4">
                            <span
                                v-if="item.status === 'published'"
                                class="inline-flex items-center gap-1.5 text-xs text-emerald-700"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                منشورة
                            </span>
                            <span v-else class="text-xs text-gray-400">مسودة</span>
                        </td>
                        <td class="px-4 py-4 text-end">
                            <Dropdown width="w-40">
                                <template #trigger>
                                    <button type="button" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" aria-label="menu">
                                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                    </button>
                                </template>
                                <RouterLink
                                    :to="`/manage/newsletter/detail/${item.uuid}`"
                                    class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100"
                                >
                                    تعديل
                                </RouterLink>
                            </Dropdown>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="store.hasPages" class="flex items-center justify-between rounded-b-2xl bg-gray-50 p-4 px-6">
                <div class="text-sm text-gray-500">
                    النتائج : <b>{{ new Intl.NumberFormat('ar').format(store.meta.total) }}</b>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        type="button"
                        variant="secondary"
                        label="السابق"
                        :disabled="store.meta.current_page <= 1 || store.loading"
                        @click="store.goToPage(store.meta.current_page - 1)"
                    />
                    <span class="text-sm text-gray-500">
                        {{ store.meta.current_page }} / {{ store.meta.last_page }}
                    </span>
                    <Button
                        type="button"
                        variant="secondary"
                        label="التالي"
                        :disabled="store.meta.current_page >= store.meta.last_page || store.loading"
                        @click="store.goToPage(store.meta.current_page + 1)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>
