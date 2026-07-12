<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import Button from '../../ui/Button.vue';
import Modal from '../../ui/Modal.vue';
import Dropdown from '../../Dropdown.vue';
import AddPage from './AddPage.vue';
import { usePagesStore } from '../../../stores/pages.js';
import { openModal } from '../../../lib/modal.js';

const store = usePagesStore();
const search = ref('');
const selectedIds = ref([]);
let searchTimer = null;

onMounted(() => {
    store.fetchPages({ page: 1 });
});

watch(search, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        store.setSearch(value);
        selectedIds.value = [];
    }, 300);
});

const selectableItems = computed(() => store.items.filter((item) => !item.is_system_page));

const allSelected = computed({
    get: () => selectableItems.value.length > 0
        && selectableItems.value.every((item) => selectedIds.value.includes(String(item.id))),
    set: (value) => {
        selectedIds.value = value ? selectableItems.value.map((item) => String(item.id)) : [];
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

async function removeSelected() {
    if (selectedIds.value.length === 0) {
        return;
    }

    if (!confirm('هل أنت متأكد من حذف العناصر المحددة؟')) {
        return;
    }

    await store.deletePages(selectedIds.value);
    selectedIds.value = [];
}

async function removeOne(item) {
    if (!confirm('هل أنت متأكد من حذف هذه الصفحة؟')) {
        return;
    }

    await store.deletePages([item.id]);
    selectedIds.value = selectedIds.value.filter((id) => id !== String(item.id));
}

async function toggleActive(item) {
    await store.togglePageActive(item.uuid, !item.active);
}
</script>

<template>
    <div class="divide-y divide-dotted divide-stone-200">
        <div class="flex w-full items-center gap-x-4 bg-white p-3">
            <div class="hidden ps-3 sm:block">
                <input v-model="allSelected" type="checkbox" class="h-4 w-4 rounded-xl border-stone-300 shadow-sm">
            </div>

            <div class="flex-grow">
                <div class="relative text-sm text-stone-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-stone-500">
                        <svg class="h-5 w-5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="ابحث .."
                        class="block w-full bg-stone-100 rounded-lg border border-transparent py-1.5 ps-10 text-stone-800 ring-inset ring-stone-200 placeholder:text-stone-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                    >
                </div>
            </div>

            <div v-if="selectedIds.length > 0" class="hidden items-center gap-x-2 sm:flex">
                <div class="flex items-center gap-1 text-sm text-stone-600">
                    <span>{{ selectedIds.length }}</span>
                    <span>محددة</span>
                </div>
                <button
                    type="button"
                    class="flex items-center gap-2 rounded-lg border bg-white px-3 py-1.5 text-sm text-stone-700 hover:bg-stone-200"
                    :disabled="store.saving"
                    @click="removeSelected"
                >
                    حذف المحددة ({{ selectedIds.length }})
                </button>
            </div>

            <Button label="صفحة جديدة" @click="openModal('add-page')">
                <template #icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                </template>
            </Button>

            <Modal title="إضافة صفحة جديدة" size="2xl" name="add-page">
                <AddPage />
            </Modal>
        </div>

        <div class="relative p-1">
            <div v-if="store.loading && !store.loaded" class="flex items-center justify-center p-10"><LoadingSpinner size="lg" /></div>

            <div v-else-if="store.error" class="p-6 text-center text-sm text-red-600">
                {{ store.error }}
            </div>

            <div v-else-if="store.isEmpty" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-12 w-12 opacity-50" alt="">
                <p class="text-stone-700">لا توجد صفحات.</p>
                <small class="text-stone-500">سيتم عرض الصفحات هنا بعد إضافتها.</small>
            </div>

            <div v-else>
                <div
                    v-for="item in store.items"
                    :key="item.uuid"
                    class="flex w-full items-center justify-between ps-3 sm:ps-0 gap-x-7 hover:bg-stone-50 last:rounded-b-2xl"
                >
                    <div class="hidden ps-4 sm:block">
                        <div v-if="!item.is_system_page" class="flex items-center">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded-xl border-stone-300 shadow-sm"
                                :checked="selectedIds.includes(String(item.id))"
                                @change="toggleOne(item.id, $event.target.checked)"
                            >
                        </div>
                    </div>

                    <div class="w-full py-3">
                        <RouterLink
                            :to="`/manage/pages/detail/${item.uuid}`"
                            class="flex w-full items-center gap-x-3 text-start"
                        >
                            <div class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-stone-100">
                                <img
                                    v-if="store.type?.icon"
                                    :src="`/${store.type.icon}`"
                                    class="h-6 w-6 opacity-60"
                                    alt=""
                                >
                            </div>
                            <div class="min-w-0">
                                <h2 class="truncate text-sm font-semibold text-stone-700">{{ item.title }}</h2>
                                <div class="mt-1 flex items-center gap-x-2">
                                    <div v-if="item.active" class="mt-1 flex items-center gap-x-1.5">
                                        <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                        </div>
                                    </div>
                                    <p class="mt-1 flex items-center gap-1 text-xs text-stone-500">
                                        <span class="inline-flex items-center gap-x-1 rounded-md bg-stone-100 p-1 px-2 text-xs">
                                            {{ item.status_label }}
                                        </span>
                                        <span
                                            v-if="item.is_system_page && item.template_label"
                                            class="inline-flex items-center gap-x-1 rounded-md bg-blue-50 p-1 px-2 text-xs text-blue-700"
                                        >
                                            {{ item.template_label }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="pe-6">
                        <Dropdown width="w-44">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-stone-500 hover:bg-stone-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                </button>
                            </template>
                            <RouterLink
                                :to="`/manage/pages/detail/${item.uuid}`"
                                class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100"
                            >
                                تعديل
                            </RouterLink>
                            <button
                                type="button"
                                class="flex w-full items-center gap-x-2 rounded p-1.5 text-start hover:bg-stone-100"
                                :disabled="store.saving"
                                @click="toggleActive(item)"
                            >
                                {{ item.active ? 'تعطيل' : 'تفعيل' }}
                            </button>
                            <button
                                v-if="!item.is_system_page"
                                type="button"
                                class="flex w-full items-center gap-x-2 rounded p-1.5 text-start text-red-600 hover:bg-stone-100"
                                :disabled="store.saving"
                                @click="removeOne(item)"
                            >
                                حذف
                            </button>
                        </Dropdown>
                    </div>
                </div>
            </div>

            <div
                v-if="store.loading"
                class="absolute inset-0 bg-white opacity-50"
            ></div>
        </div>

        <div v-if="store.hasPages" class="flex items-center justify-between rounded-b-2xl bg-stone-50 p-4 px-6">
            <div class="text-sm text-stone-500">
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
                <span class="text-sm text-stone-500">
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
</template>
