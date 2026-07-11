<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import Button from '../../ui/Button.vue';
import Modal from '../../ui/Modal.vue';
import Dropdown from '../../Dropdown.vue';
import AddForm from './AddForm.vue';
import { useFormsStore } from '../../../stores/forms.js';
import { openModal } from '../../../lib/modal.js';

const store = useFormsStore();
const router = useRouter();
const search = ref('');
const selectedIds = ref([]);
const cloningId = ref(null);
let searchTimer = null;

onMounted(() => {
    store.fetchForms({ page: 1 });
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

function submissionsLabel(count) {
    const value = Number(count);

    return value === 1 ? 'إرسالية' : 'إرساليات';
}

async function removeSelected() {
    if (selectedIds.value.length === 0) {
        return;
    }

    if (!confirm('هل أنت متأكد من حذف العناصر المحددة؟')) {
        return;
    }

    await store.deleteForms(selectedIds.value);
    selectedIds.value = [];
}

async function cloneItem(id, uuid) {
    cloningId.value = id;

    try {
        const clone = await store.cloneForm(uuid);
        if (clone?.uuid) {
            router.push(`/manage/forms/detail/${clone.uuid}`);
        }
    } finally {
        cloningId.value = null;
    }
}
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-4 bg-gray-100 p-3">
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
                        class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
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

            <Button label="نموذج جديد" @click="openModal('add-form')">
                <template #icon>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                </template>
            </Button>

            <Modal title="إضافة نموذج جديد" size="2xl" name="add-form">
                <AddForm />
            </Modal>
        </div>

        <div class="relative pb-4 p-1">
            <div v-if="store.loading && !store.loaded" class="flex items-center justify-center p-10"><LoadingSpinner size="lg" /></div>

            <div v-else-if="store.error" class="p-6 text-center text-sm text-red-600">
                {{ store.error }}
            </div>

            <div v-else-if="store.isEmpty" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <img v-if="store.type?.icon" :src="`/${store.type.icon}`" class="h-12 w-12 opacity-50" alt="">
                <p class="text-gray-700">لا توجد نماذج.</p>
                <small class="text-gray-500">سيتم عرض النماذج هنا بعد إضافتها.</small>
            </div>

            <div v-else>
                <div
                    v-for="item in store.items"
                    :key="item.uuid"
                    class="flex w-full items-center justify-between ps-3 sm:ps-0 gap-x-7 hover:bg-gray-50 last:rounded-b-2xl"
                >
                    <div class="hidden ps-4 sm:block">
                        <input
                            type="checkbox"
                            class="h-4 w-4 rounded-xl border-gray-300 shadow-sm"
                            :checked="selectedIds.includes(String(item.id))"
                            @change="toggleOne(item.id, $event.target.checked)"
                        >
                    </div>

                    <div class="w-full py-3">
                        <RouterLink
                            :to="`/manage/forms/detail/${item.uuid}`"
                            class="flex w-full items-center gap-x-3 text-start"
                        >
                            <div class="flex h-10 w-10 flex-none items-center justify-center rounded-xl bg-gray-100">
                                <img
                                    v-if="store.type?.icon"
                                    :src="`/${store.type.icon}`"
                                    class="h-6 w-6 opacity-60"
                                    alt=""
                                >
                            </div>
                            <div>
                                <h2 class="truncate text-sm font-semibold text-gray-700">{{ item.title }}</h2>
                                <div class="mt-1 flex items-center gap-x-2">
                                    <div v-if="item.active" class="mt-1 flex items-center gap-x-1.5">
                                        <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                                            <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                        </div>
                                    </div>
                                    <p class="mt-1 flex items-center gap-1 text-xs text-gray-500">
                                        <span class="inline-flex items-center gap-x-1 truncate rounded-md bg-gray-100 p-1 px-2 text-xs">
                                            {{ item.status_label }}
                                        </span>
                                        <span class="inline-flex items-center gap-x-1 truncate rounded-md bg-gray-100 p-1 px-2 text-xs">
                                            {{ new Intl.NumberFormat('ar').format(item.form_submissions_count) }}
                                            {{ submissionsLabel(item.form_submissions_count) }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="pe-6">
                        <Dropdown width="w-44">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                </button>
                            </template>
                            <RouterLink
                                :to="`/manage/forms/detail/${item.uuid}`"
                                class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100"
                            >
                                تعديل
                            </RouterLink>
                            <button
                                type="button"
                                class="flex w-full items-center gap-x-2 rounded p-1.5 text-start hover:bg-stone-100"
                                :disabled="store.saving || cloningId === item.id"
                                @click="cloneItem(item.id, item.uuid)"
                            >
                                <span v-if="cloningId === item.id" class="text-xs text-gray-400">جاري النسخ…</span>
                                <span v-else>نسخ النموذج</span>
                            </button>
                        </Dropdown>
                    </div>
                </div>
            </div>

            <div
                v-if="store.loading"
                class="absolute inset-0 bg-white opacity-50"
            />

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
