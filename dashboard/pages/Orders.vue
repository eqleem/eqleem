<script setup>
import { ref, computed } from 'vue';
import Container from '../components/ui/Container.vue';
import MainBox from '../components/ui/MainBox.vue';

const tabs = [
    { name: 'orders', label: 'الطلبات' },
    { name: 'payments', label: 'المبيعات' },
    { name: 'invoices', label: 'الفواتير' },
    { name: 'form-submissions', label: 'ردود النماذج' },
];

const active = ref('orders');
const activeLabel = computed(() => tabs.find((tab) => tab.name === active.value)?.label);
</script>

<template>
    <Container>
        <MainBox title="الطلبات" subtitle="الطلبات والمشتريات، تجدها هنا.">
            <template #icon>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gray-500" viewBox="0 0 24 24" fill="none">
                    <path
                        d="M8.5 19H8c-4 0-6-1-6-6V8c0-4 2-6 6-6h8c4 0 6 2 6 6v5c0 4-2 6-6 6h-.5c-.31 0-.61.15-.8.4l-1.5 2c-.66.88-1.74.88-2.4 0l-1.5-2c-.16-.22-.53-.4-.8-.4Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-miterlimit="10"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        opacity=".4"
                        d="M15.995 11h.008M11.995 11h.009M7.995 11h.008"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </template>

            <div>
                <div class="flex border-b border-stone-200 px-px">
                    <button
                        v-for="tab in tabs"
                        :key="tab.name"
                        type="button"
                        class="px-4 py-3 text-sm transition"
                        :class="active === tab.name
                            ? 'border-b-2 border-primary-500 text-stone-900'
                            : 'text-gray-500 hover:text-gray-800'"
                        @click="active = tab.name"
                    >
                        {{ tab.label }}
                    </button>
                </div>

                <div class="p-12 text-center text-sm text-gray-400">
                    لا توجد {{ activeLabel }} لعرضها بعد.
                </div>
            </div>
        </MainBox>
    </Container>
</template>
