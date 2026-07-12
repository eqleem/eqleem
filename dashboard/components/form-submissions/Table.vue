<script setup>
import { onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Dropdown from '../Dropdown.vue';
import Badge from '../ui/Badge.vue';
import Icon from '../ui/Icon.vue';
import { useFormSubmissionsStore } from '../../stores/formSubmissions.js';

const submissionsStore = useFormSubmissionsStore();
const { items, meta, search, loading, loaded, error, isEmpty, hasPages } = storeToRefs(submissionsStore);

const searchInput = ref('');
let searchTimer = null;

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => submissionsStore.setSearch(value), 300);
});

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }
    submissionsStore.goToPage(page);
}

onMounted(() => {
    searchInput.value = search.value;
    if (!loaded.value) {
        submissionsStore.fetchList();
    }
});
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-7 bg-white p-3">
            <div class="flex-grow bg-gray-100 rounded-lg">
                <div class="relative col-span-3 text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input v-model="searchInput" type="text" placeholder="ابحث .." class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6">
                </div>
            </div>
        </div>

        <div class="relative p-1">
            <div
                v-if="loading"
                class="animate-pulse"
                aria-busy="true"
                aria-label="جاري تحميل الردود"
            >
                <div
                    v-for="n in 6"
                    :key="`skeleton-${n}`"
                    class="flex w-full items-center justify-between gap-x-4 border-s-4 border-transparent px-4 sm:px-6"
                >
                    <div class="min-w-0 flex-1 space-y-2 py-3.5">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <div
                                class="h-5 rounded-md bg-gray-200"
                                :class="n % 2 === 0 ? 'w-14' : 'w-16'"
                            ></div>
                            <div class="h-5 w-16 rounded-md bg-gray-100"></div>
                            <div
                                class="h-5 rounded-md bg-gray-100"
                                :class="n % 3 === 0 ? 'w-20' : 'w-28'"
                            ></div>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                            <div
                                class="h-4 rounded-md bg-gray-200"
                                :class="n % 3 === 0 ? 'w-20' : 'w-28'"
                            ></div>
                            <div
                                class="h-5 rounded-md bg-gray-100"
                                :class="n % 2 === 0 ? 'w-32' : 'w-40'"
                            ></div>
                            <div class="h-4 w-20 rounded-md bg-gray-100"></div>
                        </div>
                    </div>

                    <div class="hidden shrink-0 items-center gap-x-6 sm:flex">
                        <div class="min-w-24 space-y-1.5 text-end">
                            <div class="ms-auto h-4 w-20 rounded-md bg-gray-200"></div>
                            <div class="ms-auto h-3 w-14 rounded-md bg-gray-100"></div>
                        </div>
                    </div>

                    <div class="flex shrink-0 flex-col items-center gap-1 pe-2 px-1.5 py-1.5">
                        <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                        <div class="h-1.5 w-1.5 rounded-full bg-gray-200"></div>
                    </div>
                </div>
            </div>

            <div v-else-if="error" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <p class="text-sm text-red-600">{{ error }}</p>
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm" @click="submissionsStore.fetchList({ page: meta.current_page })">إعادة المحاولة</button>
            </div>

            <div v-else-if="isEmpty" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <Icon name="clipboard" class="h-12 w-12 p-0.5 text-gray-400" />
                <p class="text-gray-700">لا توجد ردود.</p>
                <small class="text-gray-500">سيتم عرض ردود النماذج هنا بعد إرسالها من الموقع.</small>
            </div>

            <div v-else-if="items.length > 0">
                <div
                    v-for="item in items"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-4 border-s-4 px-4 transition-colors sm:px-6"
                    :class="item.unread ? 'border-primary-500 bg-primary-50/60 hover:bg-primary-50' : 'border-transparent hover:bg-gray-50'"
                >
                    <div class="min-w-0 flex-1 py-3.5">
                        <RouterLink :to="`/form-submissions/${item.id}`" class="block">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span v-if="item.unread" class="h-2 w-2 shrink-0 rounded-full bg-primary-500" aria-hidden="true"></span>
                                <h2 class="text-lg font-semibold" :class="item.unread ? 'text-gray-900' : 'text-gray-700'">#{{ item.id }}</h2>
                                <Badge v-if="item.unread" color="blue">غير مقروء</Badge>
                                <Badge v-else :color="item.status_color">{{ item.status_label }}</Badge>
                                <span v-if="item.form_title" class="inline-flex items-center gap-x-1 truncate rounded-md bg-white/80 p-1 px-2 text-xs text-gray-600 ring-1 ring-gray-200/80">
                                    <Icon name="clipboard" class="h-3.5 w-3.5 text-gray-400" />
                                    {{ item.form_title }}
                                </span>
                            </div>
                            <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm" :class="item.unread ? 'text-gray-700' : 'text-gray-500'">
                                <template v-if="item.client">
                                    <span class="truncate font-medium">{{ item.client.name }}</span>
                                    <span v-if="item.client.email" class="inline-flex items-center gap-x-1 truncate rounded-md bg-white/80 p-1 px-2 text-xs ring-1 ring-gray-200/80">{{ item.client.email }}</span>
                                    <span v-if="item.client.phone" class="inline-block text-xs" dir="ltr">{{ item.client.phone }}</span>
                                </template>
                                <span v-else-if="item.preview" class="truncate">{{ item.preview }}</span>
                                <span v-else class="text-gray-400">زائر</span>
                            </div>
                        </RouterLink>
                    </div>

                    <div class="hidden shrink-0 items-center gap-x-6 text-sm sm:flex" :class="item.unread ? 'text-gray-700' : 'text-gray-600'">
                        <div class="min-w-24 text-end">
                            <div :class="{ 'font-medium': item.unread }">{{ item.submitted }}</div>
                            <div class="mt-0.5 text-xs text-gray-400" dir="ltr">{{ item.time }}</div>
                        </div>
                    </div>

                    <div class="shrink-0 pe-2">
                        <Dropdown width="w-36">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-gray-500 hover:bg-gray-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                </button>
                            </template>
                            <RouterLink :to="`/form-submissions/${item.id}`" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">عرض</RouterLink>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="hasPages" class="flex items-center justify-between rounded-b-2xl bg-gray-50 p-4 px-6">
            <div class="text-sm text-gray-500">النتائج : <b>{{ meta.total.toLocaleString('ar-SA') }}</b></div>
            <div class="flex items-center gap-2">
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm disabled:opacity-40" :disabled="meta.current_page <= 1 || loading" @click="goToPage(meta.current_page - 1)">السابق</button>
                <span class="text-sm text-gray-500">{{ meta.current_page }} / {{ meta.last_page }}</span>
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm disabled:opacity-40" :disabled="meta.current_page >= meta.last_page || loading" @click="goToPage(meta.current_page + 1)">التالي</button>
            </div>
        </div>
    </div>
</template>
