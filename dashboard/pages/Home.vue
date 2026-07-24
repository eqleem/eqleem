<script setup>
import { defineAsyncComponent } from 'vue';
import Container from '../components/ui/Container.vue';

const NewUserOnboardingWidget = defineAsyncComponent(() => import('../components/home/NewUserOnboardingWidget.vue'));
const WelcomeWidget = defineAsyncComponent(() => import('../components/home/WelcomeWidget.vue'));
const OrdersCount = defineAsyncComponent(() => import('../components/home/OrdersCount.vue'));
const SalesTotalCount = defineAsyncComponent(() => import('../components/home/SalesTotalCount.vue'));
const VisitsCount = defineAsyncComponent(() => import('../components/home/VisitsCount.vue'));
const ClientsCount = defineAsyncComponent(() => import('../components/home/ClientsCount.vue'));
const OrdersChart = defineAsyncComponent(() => import('../components/home/OrdersChart.vue'));
const VisitsChart = defineAsyncComponent(() => import('../components/home/VisitsChart.vue'));
const ClientsChart = defineAsyncComponent(() => import('../components/home/ClientsChart.vue'));
const SalesChart = defineAsyncComponent(() => import('../components/home/SalesChart.vue'));

const monthLabel = new Intl.DateTimeFormat('ar', { month: 'long', year: 'numeric' }).format(new Date());
</script>

<template>
    <Container>
        <NewUserOnboardingWidget />
 
        <div class="grid grid-cols-1 gap-3  lg:grid-cols-2 lg:gap-8 mb-5">
            <div>
                <WelcomeWidget />
            </div>
            <div>
                <div class="mb-2 rounded-lg bg-stone-300/30 p-2 px-3 text-sm text-stone-700">
                    ملخّص الشهر <b class="ms-2 inline-block">{{ monthLabel }}</b>
                </div>

                <div class=" grid grid-cols-2 gap-3 rounded-xl bg-stone-300/30 p-1   lg:gap-5">
                    <OrdersCount /> 
                    <SalesTotalCount />
                    <VisitsCount />
                    <ClientsCount />     
                </div>
            </div>
            
        </div>

        <div class="mb-8 grid grid-cols-1 gap-3 rounded-2xl bg-stone-300/30 p-1 lg:grid-cols-2 lg:gap-5">
            <OrdersChart />
            <VisitsChart />
            <ClientsChart />
            <SalesChart /> 
        </div>
    </Container>
</template>
