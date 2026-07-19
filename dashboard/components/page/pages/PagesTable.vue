<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import Button from '../../ui/Button.vue';
import Modal from '../../ui/Modal.vue';
import Dropdown from '../../Dropdown.vue';
import AddPage from './AddPage.vue';
import { usePagesStore } from '../../../stores/pages.js';
import { openModal } from '../../../lib/modal.js';
import { pageEditPath } from '../../../lib/pagePaths.js';
import { notifySuccess, notifyApiError } from '../../../lib/notify.js';

const store = usePagesStore();
const router = useRouter();
const search = ref('');
const selectedIds = ref([]);
const creatingTemplate = ref(null);
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

const hasContactPage = computed(() => store.existingTemplates.includes('contact')
    || store.items.some((item) => item.template === 'contact'));

const hasFaqPage = computed(() => store.existingTemplates.includes('faq')
    || store.items.some((item) => item.template === 'faq'));

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

async function createTemplatePage(template) {
    if (creatingTemplate.value) {
        return;
    }

    if ((template === 'contact' && hasContactPage.value) || (template === 'faq' && hasFaqPage.value)) {
        return;
    }

    const defaults = {
        contact: 'اتصل بنا',
        faq: 'الأسئلة المتكررة',
    };

    creatingTemplate.value = template;

    try {
        const page = await store.createPage({
            title: defaults[template],
            template,
        });

        notifySuccess('Saved');
        router.push(pageEditPath(page));
    } catch (error) {
        notifyApiError(error, 'تعذر إنشاء الصفحة.');
    } finally {
        creatingTemplate.value = null;
    }
}
</script>

<template>
    <div class="divide-y divide-dotted divide-stone-200">
        <div class="relative z-40 flex w-full items-center gap-x-4 bg-white p-3">
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

            <Dropdown width="w-60">
                <template #trigger>
                    <Button label="صفحة جديدة" :disabled="Boolean(creatingTemplate) || store.saving">
                        <template #icon>
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg>
                        </template>
                        <template #default>
                            <svg class="h-4 w-4 opacity-70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" /></svg>
                        </template>
                    </Button>
                </template>

                <button
                    type="button"
                    class="flex w-full items-center gap-x-2.5 rounded-md p-2 text-start text-sm text-stone-700 hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent"
                    :disabled="Boolean(creatingTemplate) || store.saving || hasContactPage"
                    @click="createTemplatePage('contact')"
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-50 text-sky-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium">صفحة اتصل بنا</span>
                        <span class="block text-xs text-stone-500">
                            {{ hasContactPage ? 'تمت إضافتها مسبقاً' : 'نموذج تواصل وبيانات الاتصال' }}
                        </span>
                    </span>
                </button>
                <button
                    type="button"
                    class="flex w-full items-center gap-x-2.5 rounded-md p-2 text-start text-sm text-stone-700 hover:bg-stone-100 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-transparent"
                    :disabled="Boolean(creatingTemplate) || store.saving || hasFaqPage"
                    @click="createTemplatePage('faq')"
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                        </svg>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-medium">صفحة أسئلة متكررة</span>
                        <span class="block text-xs text-stone-500">
                            {{ hasFaqPage ? 'تمت إضافتها مسبقاً' : 'أسئلة وأجوبة جاهزة للزوار' }}
                        </span>
                    </span>
                </button>
                <button
                    type="button"
                    class="flex w-full items-center gap-x-2.5 rounded-md p-2 text-start text-sm text-stone-700 hover:bg-stone-100"
                    :disabled="Boolean(creatingTemplate) || store.saving"
                    @click="openModal('add-page')"
                >
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-600">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                        </svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block font-medium">صفحة مخصصة</span>
                        <span class="block text-xs text-stone-500">محتوى حر مع محرر وبلوكات</span>
                    </span>
                </button>
            </Dropdown>

            <Modal title="إضافة صفحة جديدة" size="2xl" name="add-page">
                <AddPage />
            </Modal>
        </div>

        <div class="relative z-0 p-1">
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
                    <div class="hidden flex-none ps-4 sm:block">
                        <div v-if="!item.is_system_page" class="flex items-center">
                            <input
                                type="checkbox"
                                class="h-4 w-4 rounded-xl border-stone-300 shadow-sm"
                                :checked="selectedIds.includes(String(item.id))"
                                @change="toggleOne(item.id, $event.target.checked)"
                            >
                        </div>
                    </div>

                    <div class="min-w-0 flex-1 py-3">
                        <RouterLink
                            :to="pageEditPath(item)"
                            class="flex items-center gap-x-3 text-start"
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
                                            v-if="item.template_label"
                                            class="inline-flex items-center gap-x-1 rounded-md bg-blue-50 p-1 px-2 text-xs text-blue-700"
                                        >
                                            {{ item.template_label }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="flex-none pe-6">
                        <Dropdown width="w-44">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-stone-500 hover:bg-stone-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                </button>
                            </template>
                            <RouterLink
                                :to="pageEditPath(item)"
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
