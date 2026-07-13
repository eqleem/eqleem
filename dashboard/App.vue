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
        <main class="bg-stone-200 min-h-[calc(100.3vh-var(--dashboard-chrome-h))] pt-[var(--dashboard-chrome-h)]">
            <RouterView />
        </main>
        <div class="bg-stone-200 p-4 pt-10 pb-6">
            <a href="https://eqleem.com" target="_blank" class="flex items-center justify-center gap-2">
                <img :src="'/assets/images/logo.webp'" class="h-5 mx-auto" alt="إقليم">
            </a>
        </div>
    </div> 
</template>
