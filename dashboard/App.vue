<script setup>
import { onMounted } from 'vue';
import Header from './components/Header.vue';
import Nav from './components/Nav.vue';
import { loadDashboardContext } from './stores/session.js';

onMounted(() => {
    loadDashboardContext();
});
</script>

<template>
    <div class="antialiased bg-stone-200">
        <Header />
        <Nav />
        <main class="min-h-[calc(100vh-5.5rem)] pt-[5.5rem]">
            <RouterView />
        </main>
    </div>
</template>
