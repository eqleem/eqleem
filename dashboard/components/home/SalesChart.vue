<script setup>
// Port of resources/views/admin/home/sales-chart.blade.php (dummy data).
import ChartWidget from '../ChartWidget.vue';
import { lineChartOptions, dummyTrend } from './chartOptions.js';

const { labels, data } = dummyTrend(14, 0, 3500);
const options = lineChartOptions('المبيعات', data, labels);
</script>

<template>
    <ChartWidget chart-title="المبيعات" :options="options" />
</template>
