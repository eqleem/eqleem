<script setup>
import { defineAsyncComponent, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import Container from '../../components/ui/Container.vue';
import Button from '../../components/ui/Button.vue';
import SummaryCard from '../../components/analytics/SummaryCard.vue';
import StatsList from '../../components/analytics/StatsList.vue';
import { useAnalyticsStore } from '../../stores/analytics.js';
import { sectionIcons } from '../../lib/analyticsIcons.js';

const TrafficChart = defineAsyncComponent(() => import('../../components/analytics/TrafficChart.vue'));

const store = useAnalyticsStore();
const {
    summary,
    chart,
    topPages,
    topReferrers,
    browsers,
    devices,
    countries,
    operatingSystems,
    loading,
    loaded,
    error,
    dateRangeDays,
} = storeToRefs(store);

const draftDateRange = ref(store.dateRangeDays);

const dateRangeOptions = [
    { value: 7, label: 'آخر 7 أيام' },
    { value: 14, label: 'آخر 14 يومًا' },
    { value: 30, label: 'آخر 30 يومًا' },
    { value: 90, label: 'آخر 90 يومًا' },
];

function applyFilters() {
    store.setFilters({
        dateRangeDays: draftDateRange.value,
        requestCategory: '',
    });
    store.fetchOverview({ force: true });
}

onMounted(() => {
    draftDateRange.value = dateRangeDays.value;
    store.fetchOverview();
});
</script>

<template>
    <Container>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex items-start gap-3">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-stone-200/70 p-1.5">
                    <img :src="sectionIcons.chart" alt="" class="h-7 w-7 object-contain">
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-stone-900">إحصائيات الزيارات</h1>
                    <p class="mt-1 text-sm text-stone-500">تابع أداء صفحتك وسلوك الزوار</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <select
                    v-model.number="draftDateRange"
                    class="h-9 rounded-md border border-stone-200 bg-white px-3 text-sm text-stone-700 outline-none focus:border-primary-400"
                >
                    <option v-for="option in dateRangeOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>

                <Button
                    type="button"
                    label="تطبيق"
                    :loading="loading"
                    @click="applyFilters"
                />
            </div>
        </div>

        <div v-if="error" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ error }}
        </div>

        <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <SummaryCard
                label="المشاهدات"
                :value="summary.views"
                :icon="sectionIcons.views"
                :loading="loading && !loaded"
            />
            <SummaryCard
                label="الزوار"
                :value="summary.visitors"
                :icon="sectionIcons.visitors"
                :loading="loading && !loaded"
            />
            <SummaryCard
                label="معدل الارتداد"
                :value="summary.bounce_rate"
                :icon="sectionIcons.bounce"
                :loading="loading && !loaded"
            />
            <SummaryCard
                label="متوسط مدة الزيارة"
                :value="summary.average_visit_time"
                :icon="sectionIcons.duration"
                :loading="loading && !loaded"
            />
        </div>

        <div class="mb-4">
            <TrafficChart
                :labels="chart.labels"
                :datasets="chart.datasets"
                :loading="loading && !loaded"
            />
        </div>

        <div class="mb-4 grid grid-cols-1 gap-4 lg:grid-cols-2">
            <StatsList
                title="الصفحات"
                secondary-label="المشاهدات"
                :icon="sectionIcons.pages"
                :items="topPages"
                label-key="path"
                count-key="views"
                empty-text="لا توجد صفحات"
                :loading="loading && !loaded"
            />
            <StatsList
                title="مصادر الزيارات"
                secondary-label="الزيارات"
                :icon="sectionIcons.referrers"
                :items="topReferrers"
                label-key="domain"
                count-key="visits"
                empty-text="لا توجد مصادر"
                :loading="loading && !loaded"
            />
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <StatsList
                title="المتصفح"
                :icon="sectionIcons.browsers"
                item-icon-kind="browser"
                :items="browsers"
                label-key="browser"
                empty-text="لا توجد متصفحات"
                :loading="loading && !loaded"
            />
            <StatsList
                title="أنظمة التشغيل"
                :icon="sectionIcons.operatingSystems"
                item-icon-kind="os"
                :items="operatingSystems"
                label-key="name"
                empty-text="لا توجد أنظمة"
                :loading="loading && !loaded"
            />
            <StatsList
                title="الأجهزة"
                :icon="sectionIcons.devices"
                item-icon-kind="device"
                :items="devices"
                label-key="name"
                empty-text="لا توجد أجهزة"
                :loading="loading && !loaded"
            />
            <StatsList
                title="الدول"
                :icon="sectionIcons.countries"
                item-icon-kind="country"
                :items="countries"
                label-key="name"
                empty-text="لا توجد دول"
                :loading="loading && !loaded"
            />
        </div>
    </Container>
</template>
