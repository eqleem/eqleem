<script setup>
import { ref, computed } from 'vue';
import Dropdown from '../Dropdown.vue';
import Badge from '../ui/Badge.vue';
import Icon from '../ui/Icon.vue';
import { formSubmissions, submissionStatusLabel, submissionStatusColor } from '../../data/formSubmissions.js';

// Port of resources/views/admin/orders/form-submissions-table.blade.php (dummy data).
const search = ref('');

const results = computed(() => {
    const query = search.value.trim().toLowerCase();
    if (!query) {
        return formSubmissions;
    }
    return formSubmissions.filter(
        (item) => String(item.id).includes(query) || item.form_title.toLowerCase().includes(query) || (item.client?.name || '').toLowerCase().includes(query),
    );
});
</script>

<template>
    <div class="divide-y divide-dotted divide-gray-200">
        <div class="flex w-full items-center gap-x-7 bg-gray-100 p-3">
            <div class="flex-grow">
                <div class="relative col-span-3 text-sm text-gray-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-gray-500">
                        <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input v-model="search" type="text" placeholder="ابحث .." class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-gray-800 ring-inset ring-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6">
                </div>
            </div>
        </div>

        <div class="relative p-1">
            <div v-if="results.length === 0" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <Icon name="clipboard" class="h-12 w-12 p-0.5 text-gray-400" />
                <p class="text-gray-700">لا توجد ردود.</p>
                <small class="text-gray-500">سيتم عرض ردود النماذج هنا بعد إرسالها من الموقع.</small>
            </div>

            <div v-else>
                <div
                    v-for="item in results"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-4 border-s-4 px-4 transition-colors sm:px-6"
                    :class="item.unread ? 'border-primary-500 bg-primary-50/60 hover:bg-primary-50' : 'border-transparent hover:bg-gray-50'"
                >
                    <div class="min-w-0 flex-1 py-3.5">
                        <a href="#" class="block">
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <span v-if="item.unread" class="h-2 w-2 shrink-0 rounded-full bg-primary-500" aria-hidden="true"></span>
                                <h2 class="text-lg font-semibold" :class="item.unread ? 'text-gray-900' : 'text-gray-700'">#{{ item.id }}</h2>
                                <Badge v-if="item.unread" color="blue">غير مقروء</Badge>
                                <Badge v-else :color="submissionStatusColor(item.status)">{{ submissionStatusLabel(item.status) }}</Badge>
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
                        </a>
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
                            <a href="#" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</a>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
