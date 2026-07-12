<script setup>
import { ref, computed } from 'vue';
import Button from '../ui/Button.vue';
import Badge from '../ui/Badge.vue';
import Dropdown from '../Dropdown.vue';
import { itemsFor } from '../../data/page.js';

// Port of resources/views/admin/page/content/<type>/table.blade.php (dummy data).
const props = defineProps({ contentType: { type: Object, required: true } });

const search = ref('');
const items = computed(() => itemsFor(props.contentType.slug));
const results = computed(() => {
    const query = search.value.trim();
    return query ? items.value.filter((item) => item.title.includes(query)) : items.value;
});
</script>

<template>
    <div class="divide-y divide-dotted divide-stone-200">
        <div class="flex w-full items-center gap-x-4 bg-white p-3">
            <div class="flex-grow">
                <div class="relative text-sm text-stone-800">
                    <div class="pointer-events-none absolute bottom-0 right-0 top-0 flex items-center ps-2 text-stone-500">
                        <svg class="h-5 w-5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="7" /><path stroke-linecap="round" d="m20 20-3-3" /></svg>
                    </div>
                    <input v-model="search" type="text" placeholder="ابحث .." class="block w-full bg-stone-100 rounded-lg border border-transparent py-1.5 ps-10 text-stone-800 ring-inset ring-stone-200 placeholder:text-stone-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6">
                </div>
            </div>
            <Button label="عنصر جديد">
                <template #icon><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg></template>
            </Button>
        </div>

        <div class="p-1">
            <div v-if="results.length === 0" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <img :src="`/${contentType.icon}`" class="h-12 w-12 opacity-50" alt="">
                <p class="text-stone-700">لا توجد عناصر.</p>
                <small class="text-stone-500">سيتم عرض العناصر هنا بعد إضافتها.</small>
            </div>

            <div v-else>
                <div
                    v-for="item in results"
                    :key="item.id"
                    class="flex w-full items-center justify-between gap-x-4 px-4 py-3 last:rounded-b-2xl hover:bg-stone-50 sm:px-6"
                >
                    <RouterLink :to="`/manage/${contentType.slug}/detail/${item.id}`" class="flex min-w-0 flex-1 items-center gap-3">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-100 p-1.5">
                            <img :src="`/${contentType.icon}`" class="h-full w-full" alt="">
                        </div>
                        <div class="min-w-0">
                            <p class="truncate font-medium text-stone-800">{{ item.title }}</p>
                            <div class="mt-1 flex items-center gap-2">
                                <Badge :color="item.status === 'published' ? 'green' : 'gray'">{{ item.status === 'published' ? 'منشور' : 'مسودة' }}</Badge>
                                <span class="text-xs text-stone-400">{{ item.date }}</span>
                            </div>
                        </div>
                    </RouterLink>
                    <div class="shrink-0 pe-1">
                        <Dropdown width="w-36">
                            <template #trigger>
                                <button type="button" class="rounded p-1.5 text-stone-500 hover:bg-stone-100" aria-label="menu">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                                </button>
                            </template>
                            <RouterLink :to="`/manage/${contentType.slug}/detail/${item.id}`" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</RouterLink>
                        </Dropdown>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
