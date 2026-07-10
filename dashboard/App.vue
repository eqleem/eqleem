<script setup>
import Header from './components/Header.vue';
import Nav from './components/Nav.vue';
</script>

<template>
    <div class="antialiased bg-stone-200">
        <Header />
        <Nav />
        <main class="min-h-[92.7vh]">
            <RouterView />
        </main>
    </div>
</template>
