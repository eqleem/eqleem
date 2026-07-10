<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import Chart from 'chart.js/auto';

// Port of resources/views/admin/chart-widget.blade.php (Alpine + Chart.js -> Vue).
const props = defineProps({
    chartTitle: { type: String, default: '' },
    options: { type: Object, required: true },
});

const canvas = ref(null);
let chart = null;

function renderChart() {
    if (chart) {
        chart.destroy();
    }
    chart = new Chart(canvas.value, props.options);
}

onMounted(renderChart);
watch(() => props.options, renderChart, { deep: true });
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div class="rounded-2xl bg-white">
        <div class="border-b-2 border-gray-200/50 p-3 text-sm font-semibold text-gray-500">
            {{ chartTitle }}
        </div>
        <div class="p-3">
            <canvas ref="canvas"></canvas>
        </div>
    </div>
</template>
