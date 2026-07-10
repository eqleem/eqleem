<script setup>
// Port of resources/views/admin/home/orders-chart.blade.php (dummy data).
import ChartWidget from '../ChartWidget.vue';
import { lineChartOptions, dummyTrend } from './chartOptions.js';

const { labels, data } = dummyTrend(14, 0, 18);
const options = lineChartOptions('العدد', data, labels);
</script>

<template>
    <ChartWidget chart-title="الطلبات" :options="options" />
</template>
