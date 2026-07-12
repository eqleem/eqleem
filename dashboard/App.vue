<script setup>
import { onMounted } from 'vue';
import Header from './components/Header.vue';
import Nav from './components/Nav.vue';
import Notify from './components/ui/Notify.vue';
import { loadDashboardContext } from './stores/session.js';

onMounted(() => {
    loadDashboardContext();
});
</script>

<template>
    <div class="antialiased bg-stone-200">
        <Header />
        <Nav />
        <Notify />
        <main class="bg-stone-200 min-h-[calc(102.5vh-var(--dashboard-chrome-h))] pt-[var(--dashboard-chrome-h)]">
            <RouterView />
        </main>
    </div> 
</template>
