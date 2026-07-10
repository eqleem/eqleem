import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'public/dashboard.hot',
            buildDirectory: 'dashboard',
            input: ['dashboard/app.css', 'dashboard/main.js'],
            refresh: true,
        }),
        vue(),
        tailwindcss(),
    ],
});
