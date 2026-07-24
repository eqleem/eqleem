import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            hotFile: 'public/dashboard.hot',
            // Must NOT be "dashboard" — that path conflicts with the /dashboard Laravel
            // route; nginx would serve public/dashboard/ as a static dir and 403.
            buildDirectory: 'build-dashboard',
            input: ['dashboard/app.css', 'dashboard/main.js'],
            refresh: true,
        }),
        vue({
            template: {
                compilerOptions: {
                    isCustomElement: (tag) => tag === 'iconify-icon',
                },
            },
        }),
        tailwindcss(),
    ],
});
