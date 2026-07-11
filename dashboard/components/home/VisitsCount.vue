<script setup>
import { storeToRefs } from 'pinia';
import { onMounted } from 'vue';
import { useDashboardStatsStore } from '../../stores/dashboardStats.js';

const store = useDashboardStatsStore();
const { visits, loading, loaded } = storeToRefs(store);

onMounted(() => {
    store.fetchStats();
});
</script>

<template>
    <div class="group relative col-span-1 flex rounded-xl shadow-sm" :class="{ 'animate-pulse opacity-70': loading && !loaded }">
        <RouterLink
            to="/manage-page"
            class="flex w-16 flex-shrink-0 items-center justify-center rounded-s-xl bg-pgray-100 text-sm font-medium text-white group-hover:bg-opacity-75"
        >
            <img class="h-10" :src="'/assets/icons/business/009-web browser.svg'" alt="">
        </RouterLink>

        <div class="flex flex-1 items-center justify-between truncate rounded-e-xl border-stone-200 bg-white">
            <RouterLink to="/manage-page" class="flex-1 truncate px-3 py-3 text-sm">
                <span class="font-semibold text-stone-700 hover:text-stone-600"> الزيارات </span>
                <p class="mt-1 text-stone-400">
                    <b class="me-1 text-2xl font-bold text-pgray-800">{{ visits.value }}</b>
                    <span class="ms-1 text-xs font-normal" :title="`${visits.growth}% مقارنة بنفس الفترة السابقة`">
                        <span v-if="visits.growth < 0" dir="ltr" class="text-red-500"> ⬇ {{ visits.growth }}%</span>
                        <span v-else dir="ltr" class="text-green-500"> ⬆ {{ visits.growth }}%</span>
                    </span>
                </p>
            </RouterLink>
        </div>
    </div>
</template>
