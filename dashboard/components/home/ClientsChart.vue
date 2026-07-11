<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import ChartWidget from '../ChartWidget.vue';
import { useDashboardChartsStore } from '../../stores/dashboardCharts.js';

const store = useDashboardChartsStore();
const { charts } = storeToRefs(store);

const slot = computed(() => charts.value.clients);

onMounted(() => {
    store.fetchChart('clients');
});
</script>

<template>
    <ChartWidget
        chart-title="العملاء"
        :options="slot.options"
        :loading="slot.loading && !slot.loaded"
    />
</template>
