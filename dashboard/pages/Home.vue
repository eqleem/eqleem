<script setup>
import Container from '../components/ui/Container.vue';

import NewUserOnboardingWidget from '../components/home/NewUserOnboardingWidget.vue';
import WelcomeWidget from '../components/home/WelcomeWidget.vue';
import OrdersCount from '../components/home/OrdersCount.vue';
import SalesTotalCount from '../components/home/SalesTotalCount.vue';
import VisitsCount from '../components/home/VisitsCount.vue';
import ClientsCount from '../components/home/ClientsCount.vue';
import OrdersChart from '../components/home/OrdersChart.vue';
import VisitsChart from '../components/home/VisitsChart.vue';
import ClientsChart from '../components/home/ClientsChart.vue';
import SalesChart from '../components/home/SalesChart.vue';

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
