<script setup>
import { useRoute } from 'vue-router';

const route = useRoute();

const linkClass =
    'h-9 text-sm md:text-base w-full md:w-auto justify-center flex items-center gap-x-2 p-1.5 px-3 rounded-sm hover:bg-gray-300 hover:text-gray-900';

function activeClass(name) {
    return route.name === name ? 'bg-gray-100 text-gray-900' : '';
}
</script>

<template>
    <nav class="bg-primary-800 p-0.5 text-base text-white">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-x-0.5 md:justify-start">
            <RouterLink to="/" :class="[linkClass, activeClass('home')]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path
                        d="m9.02 2.838-5.39 4.2c-.9.7-1.63 2.19-1.63 3.32v7.41c0 2.32 1.89 4.22 4.21 4.22h11.58c2.32 0 4.21-1.9 4.21-4.21v-7.28c0-1.21-.81-2.76-1.8-3.45l-6.18-4.33c-1.4-.98-3.65-.93-5 .12Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <g opacity=".4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m16.5 11.5-4.2 4.2-1.6-2.4-3.2 3.2" />
                        <path d="M14.5 11.5h2v2" />
                    </g>
                </svg>
                <span class="hidden md:block">الرئيسية</span>
            </RouterLink>

            <RouterLink to="/orders" :class="[linkClass, activeClass('orders')]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
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
                <span>الطلبات</span>
            </RouterLink>

            <RouterLink to="/clients" :class="[linkClass, activeClass('clients')]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <circle cx="15" cy="6" r="3" fill="currentColor" opacity=".4" />
                    <ellipse cx="16" cy="17" fill="currentColor" opacity=".4" rx="5" ry="3" />
                    <circle cx="9.001" cy="6" r="4" fill="currentColor" />
                    <ellipse cx="9.001" cy="17.001" fill="currentColor" rx="7" ry="4" />
                </svg>
                <span>العملاء</span>
            </RouterLink>

            <RouterLink to="/manage-page" :class="[linkClass, activeClass('page')]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24">
                    <path d="M0 0h24v24H0z" fill="none" />
                    <g fill="none" stroke="currentColor" stroke-width="1.5">
                        <path
                            stroke-linecap="round"
                            d="m15.5 9l.172.172c1.333 1.333 2 2 2 2.828s-.667 1.495-2 2.828L15.5 15m-2.206-7.83L12 12l-1.294 4.83M8.5 9l-.172.172c-1.333 1.333-2 2-2 2.828s.667 1.495 2 2.828L8.5 15"
                        />
                        <path
                            d="M2 12c0-4.714 0-7.071 1.464-8.536C4.93 2 7.286 2 12 2s7.071 0 8.535 1.464C22 4.93 22 7.286 22 12s0 7.071-1.465 8.535C19.072 22 16.714 22 12 22s-7.071 0-8.536-1.465C2 19.072 2 16.714 2 12Z"
                            opacity=".5"
                        />
                    </g>
                </svg>
                <span class="hidden md:block">إدارة الصفحة</span>
                <span class="md:hidden">الصفحة</span>
            </RouterLink>

            <RouterLink to="/settings" :class="[linkClass, 'hidden md:flex', activeClass('settings')]">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                    <path
                        opacity=".34"
                        d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-miterlimit="10"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                    <path
                        d="M2 12.881v-1.76c0-1.04.85-1.9 1.9-1.9 1.81 0 2.55-1.28 1.64-2.85-.52-.9-.21-2.07.7-2.59l1.73-.99c.79-.47 1.81-.19 2.28.6l.11.19c.9 1.57 2.38 1.57 3.29 0l.11-.19c.47-.79 1.49-1.07 2.28-.6l1.73.99c.91.52 1.22 1.69.7 2.59-.91 1.57-.17 2.85 1.64 2.85 1.04 0 1.9.85 1.9 1.9v1.76c0 1.04-.85 1.9-1.9 1.9-1.81 0-2.55 1.28-1.64 2.85.52.91.21 2.07-.7 2.59l-1.73.99c-.79.47-1.81.19-2.28-.6l-.11-.19c-.9-1.57-2.38-1.57-3.29 0l-.11.19c-.47.79-1.49 1.07-2.28.6l-1.73-.99a1.899 1.899 0 0 1-.7-2.59c.91-1.57.17-2.85-1.64-2.85-1.05 0-1.9-.86-1.9-1.9Z"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-miterlimit="10"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <span class="hidden md:block">الإعدادات</span>
            </RouterLink>
        </div>
    </nav>
</template>
