<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import ChartWidget from '../ChartWidget.vue';
import { useDashboardChartsStore } from '../../stores/dashboardCharts.js';

const store = useDashboardChartsStore();
const { charts } = storeToRefs(store);

const slot = computed(() => charts.value.sales);

onMounted(() => {
    store.fetchChart('sales');
});
</script>

<template>
    <ChartWidget
        chart-title="المبيعات"
        :options="slot.options"
        :loading="slot.loading && !slot.loaded"
    />
</template>
