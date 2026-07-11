<script setup>
import { computed, onMounted } from 'vue';
import { storeToRefs } from 'pinia';
import ChartWidget from '../ChartWidget.vue';
import { useDashboardChartsStore } from '../../stores/dashboardCharts.js';

const store = useDashboardChartsStore();
const { charts } = storeToRefs(store);

const slot = computed(() => charts.value.orders);

onMounted(() => {
    store.fetchChart('orders');
});
</script>

<template>
    <ChartWidget
        chart-title="الطلبات"
        :options="slot.options"
        :loading="slot.loading && !slot.loaded"
    />
</template>
