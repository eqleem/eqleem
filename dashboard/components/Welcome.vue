<script setup>
import { ref } from 'vue';

const message = ref('Welcome to your new Vue dashboard 👋');
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center gap-2 font-sans text-gray-800">
        <h1 class="text-3xl font-bold">{{ message }}</h1>
        <p>This is an isolated Vue app served at <code class="rounded bg-gray-100 px-1.5 py-0.5">/dashboard</code>.</p>
    </div>
</template>
