<script setup>
import { computed } from 'vue';
import Button from '../ui/Button.vue';
import Badge from '../ui/Badge.vue';
import Dropdown from '../Dropdown.vue';
import { categoriesFor } from '../../data/page.js';

// Port of resources/views/admin/page/content/<type>/categories-table.blade.php (dummy data).
const props = defineProps({ contentType: { type: Object, required: true } });
const categories = computed(() => categoriesFor(props.contentType.slug));
</script>

<template>
    <div class="divide-y divide-dotted divide-stone-200">
        <div class="flex w-full items-center justify-between bg-white p-3">
            <p class="text-sm font-medium text-stone-600">التصنيفات</p>
            <Button label="تصنيف جديد">
                <template #icon><svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" d="M12 5v14M5 12h14" /></svg></template>
            </Button>
        </div>

        <div class="p-1">
            <div
                v-for="category in categories"
                :key="category.id"
                class="flex w-full items-center justify-between gap-x-4 px-4 py-3 last:rounded-b-2xl hover:bg-stone-50 sm:px-6"
                :class="{ 'opacity-50': !category.active }"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-stone-100 text-stone-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h10" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-medium text-stone-800">{{ category.name }}</p>
                        <span class="text-xs text-stone-400">{{ category.count }} عنصر</span>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-2 pe-1">
                    <Badge :color="category.active ? 'green' : 'gray'">{{ category.active ? 'مفعّل' : 'مؤرشف' }}</Badge>
                    <Dropdown width="w-36">
                        <template #trigger>
                            <button type="button" class="rounded p-1.5 text-stone-500 hover:bg-stone-100" aria-label="menu">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="5" r="1.6" /><circle cx="12" cy="12" r="1.6" /><circle cx="12" cy="19" r="1.6" /></svg>
                            </button>
                        </template>
                        <a href="#" class="flex items-center gap-x-2 rounded p-1.5 hover:bg-stone-100">تعديل</a>
                    </Dropdown>
                </div>
            </div>
        </div>
    </div>
</template>
