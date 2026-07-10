<script setup>
import MainBox from '../ui/MainBox.vue';
import Button from '../ui/Button.vue';
import Icon from '../ui/Icon.vue';
import { systemTopBlocks, userBlocks, systemBottomBlocks } from '../../data/page.js';
</script>

<template>
    <MainBox title="هيكل الصفحة" subtitle="إدارة وترتيب بلوكات الصفحة الرئيسية.">
        <template #icon>
            <img :src="'/assets/icons/tabler/puzzle-2.svg'" class="h-7 w-7" alt="">
        </template>

        <div class="space-y-4 p-4">
            <!-- System (top) blocks -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50/80">
                <ul class="space-y-1.5 p-2">
                    <li v-for="block in systemTopBlocks" :key="block.id" class="flex items-center gap-2 rounded-lg bg-white px-2 py-2">
                        <span class="rounded-md p-1 text-gray-300"><Icon name="list" class="h-4 w-4" /></span>
                        <img :src="`/${block.icon}`" alt="" class="h-6 w-6 shrink-0 rounded-md bg-gray-100 p-1">
                        <span class="truncate text-sm font-medium text-gray-800">{{ block.title }}</span>
                        <span class="ms-auto rounded bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400">نظام</span>
                    </li>
                </ul>
            </div>

            <!-- User blocks -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50/80">
                <div class="flex items-center justify-between gap-3 border-b border-dotted border-gray-200 px-3 py-2.5">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-700">بلوكات الصفحة</p>
                        <p class="text-xs text-gray-400">البلوكات التي تضيفها تظهر هنا بين الهيدر والفوتر</p>
                    </div>
                    <Button label="إضافة بلوك" variant="secondary" class="shrink-0">
                        <template #icon><Icon name="plus" class="h-4 w-4" /></template>
                    </Button>
                </div>

                <ul class="space-y-1.5 p-2">
                    <li
                        v-for="block in userBlocks"
                        :key="block.id"
                        class="group flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2 transition hover:border-gray-200"
                        :class="{ 'opacity-50': !block.active }"
                    >
                        <button type="button" class="cursor-grab rounded-md p-1 text-gray-300 transition hover:bg-gray-100 hover:text-gray-500 active:cursor-grabbing" aria-label="سحب لإعادة الترتيب">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" /><circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" /><circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" /></svg>
                        </button>
                        <img :src="`/${block.icon}`" alt="" class="h-6 w-6 shrink-0 rounded-md bg-gray-100 p-1">
                        <span class="truncate text-sm font-medium text-gray-800">{{ block.title }}</span>
                        <span v-if="!block.active" class="ms-auto text-xs text-gray-400">غير مفعّل</span>
                    </li>
                </ul>
            </div>

            <!-- System (bottom) blocks -->
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-gray-50/80">
                <ul class="space-y-1.5 p-2">
                    <li v-for="block in systemBottomBlocks" :key="block.id" class="flex items-center gap-2 rounded-lg bg-white px-2 py-2">
                        <span class="rounded-md p-1 text-gray-300"><Icon name="list" class="h-4 w-4" /></span>
                        <img :src="`/${block.icon}`" alt="" class="h-6 w-6 shrink-0 rounded-md bg-gray-100 p-1">
                        <span class="truncate text-sm font-medium text-gray-800">{{ block.title }}</span>
                        <span class="ms-auto rounded bg-gray-100 px-2 py-0.5 text-[10px] text-gray-400">نظام</span>
                    </li>
                </ul>
            </div>
        </div>
    </MainBox>
</template>
