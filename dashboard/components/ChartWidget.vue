<script setup>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue';
import Chart from 'chart.js/auto';

// Port of resources/views/admin/chart-widget.blade.php (Alpine + Chart.js -> Vue).
const props = defineProps({
    chartTitle: { type: String, default: '' },
    options: { type: Object, default: null },
    loading: { type: Boolean, default: false },
});

const canvas = ref(null);
let chart = null;
let rendering = false;

function cloneOptions(options) {
    // Chart.js mutates its config; never hand it a reactive Pinia object.
    return JSON.parse(JSON.stringify(options));
}

async function renderChart() {
    if (!props.options || rendering) {
        return;
    }

    rendering = true;

    try {
        await nextTick();

        if (!canvas.value) {
            return;
        }

        if (chart) {
            chart.destroy();
            chart = null;
        }

        chart = new Chart(canvas.value, cloneOptions(props.options));
    } finally {
        rendering = false;
    }
}

onMounted(renderChart);
// Shallow watch only — deep watch + Chart.js mutations caused recursive updates.
watch(() => props.options, renderChart);
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div class="rounded-2xl bg-white" :class="{ 'animate-pulse opacity-70': loading }">
        <div class="border-b-2 border-stone-200/50 p-3 text-sm font-semibold text-stone-500">
            {{ chartTitle }}
        </div>
        <div class="relative min-h-48 p-3">
            <div
                v-if="loading || !options"
                class="absolute inset-0 z-10 flex items-center justify-center"
            >
                <LoadingSpinner />
            </div>
            <canvas ref="canvas" :class="{ invisible: !options }"></canvas>
        </div>
    </div>
</template>
