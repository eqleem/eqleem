<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch, nextTick } from 'vue';
import { Chart } from '../../lib/chart.js';
import { sectionIcons } from '../../lib/analyticsIcons.js';

const props = defineProps({
    labels: { type: Array, default: () => [] },
    datasets: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
});

const canvas = ref(null);
let chart = null;

const chartOptions = computed(() => {
    const views = props.datasets.find((d) => d.label === 'Views')?.data ?? [];
    const visitors = props.datasets.find((d) => d.label === 'Visitors')?.data ?? [];

    return {
        type: 'line',
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: 'المشاهدات',
                    data: views,
                    borderColor: 'rgba(220, 38, 127, 1)',
                    backgroundColor: 'rgba(220, 38, 127, 0.1)',
                    borderWidth: 3,
                    fill: false,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(220, 38, 127, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'الزوار',
                    data: visitors,
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    borderDash: [8, 4],
                    fill: false,
                    tension: 0.3,
                    pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    position: 'top',
                    align: 'end',
                    labels: { usePointStyle: true, boxWidth: 8 },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: 'rgba(0,0,0,0.04)' },
                },
                x: {
                    grid: { display: false },
                },
            },
        },
    };
});

async function renderChart() {
    await nextTick();

    if (!canvas.value || !props.labels.length) {
        return;
    }

    if (chart) {
        chart.destroy();
        chart = null;
    }

    chart = new Chart(canvas.value, JSON.parse(JSON.stringify(chartOptions.value)));
}

onMounted(renderChart);
watch(() => [props.labels, props.datasets], renderChart);
onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div
        class="rounded-xl border border-stone-200/80 bg-white shadow-sm"
        :class="{ 'animate-pulse opacity-70': loading }"
    >
        <div class="flex items-center gap-2.5 border-b border-stone-100 px-4 py-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-stone-100 p-1">
                <img :src="sectionIcons.chart" alt="" class="h-6 w-6 object-contain">
            </div>
            <div>
                <h3 class="text-sm font-semibold text-stone-800">نظرة عامة على الزيارات</h3>
                <p class="text-xs text-stone-400">اتجاه المشاهدات والزوار اليومي</p>
            </div>
        </div>
        <div class="relative h-72 p-4">
            <div
                v-if="loading || !labels.length"
                class="absolute inset-0 z-10 flex items-center justify-center"
            >
                <LoadingSpinner v-if="loading" />
                <span v-else class="text-sm text-stone-400">لا توجد بيانات للفترة المحددة</span>
            </div>
            <canvas ref="canvas" :class="{ invisible: !labels.length }"></canvas>
        </div>
    </div>
</template>
