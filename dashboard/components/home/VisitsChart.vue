<script setup>
// Port of resources/views/admin/home/visits-chart.blade.php (dummy data).
import ChartWidget from '../ChartWidget.vue';
import { lineChartOptions, dummyTrend } from './chartOptions.js';

const { labels, data } = dummyTrend(14, 120, 600);
const options = lineChartOptions('العدد', data, labels);
</script>

<template>
    <ChartWidget chart-title="الزيارات" :options="options" />
</template>
