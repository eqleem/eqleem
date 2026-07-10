<script setup>
import { useRoute } from 'vue-router';
import Dropdown from './Dropdown.vue';

const route = useRoute();

// Placeholder data (no real backend calls) — mirrors the admin header's tenant/user.
const tenant = {
    name: 'متجري',
    logo: null,
    url: '#',
    plan: 'مجاني',
};

const user = {
    name: 'أحمد الأحمدي',
    email: 'contact@ahmad.tech',
    image: 'https://www.gravatar.com/avatar/?d=mp',
};

const appName = 'Eqleem';
</script>

<template>
    <header class="bg-primary-700 p-2 text-white">
        <div class="mx-auto flex max-w-7xl justify-between">
            <div class="flex items-center gap-x-3">
                <RouterLink to="/" class="flex items-center justify-center gap-x-2 text-center">
                    <img
                        :src="tenant.logo ?? '/assets/images/t-logo.png'"
                        alt=""
                        class="ms-1 h-8 rounded-sm"
                    >
                    <span class="hidden md:block">{{ tenant.name }}</span>
                </RouterLink>

                <RouterLink to="/plan" class="flex items-center justify-center gap-x-1 text-center">
                    <span class="rounded bg-purple-500 p-0.5 px-1.5 text-xs text-purple-100 hover:bg-purple-600">
                        {{ tenant.plan }}
                    </span>
                </RouterLink>
            </div>

            <div class="flex items-center gap-x-3">
                <a
                    :href="tenant.url"
                    target="_blank"
                    class="flex items-center gap-x-2 rounded-full bg-green-600 p-1 px-3 text-sm text-white hover:bg-green-500"
                >
                    معاينة <span class="hidden lg:block">الصفحة</span>
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 ltr:rotate-90"
                        viewBox="0 0 24 24"
                        stroke-width="2"
                        stroke="currentColor"
                        fill="none"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M7 7l10 10" />
                        <path d="M16 7l-9 0l0 9" />
                    </svg>
                </a>

                <RouterLink
                    to="/settings"
                    title="الإعدادات"
                    class="flex items-center gap-x-2 rounded-full p-1 px-2 text-sm text-white hover:bg-black/30 md:hidden"
                    :class="{ 'bg-black/30': route.name === 'settings' }"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                        <path
                            d="M2 12.88v-1.76c0-1.04.85-1.9 1.9-1.9 1.81 0 2.55-1.28 1.64-2.85a1.9 1.9 0 0 1 .7-2.59l1.73-.99c.82-.49 1.88-.2 2.37.62l.11.19c.9 1.57 2.38 1.57 3.29 0l.11-.19c.49-.82 1.55-1.11 2.37-.62l1.73.99c.91.53 1.22 1.69.7 2.59-.91 1.57-.17 2.85 1.64 2.85 1.04 0 1.9.85 1.9 1.9v1.76c0 1.04-.85 1.9-1.9 1.9-1.81 0-2.55 1.28-1.64 2.85.52.91.21 2.06-.7 2.59l-1.73.99c-.82.49-1.88.2-2.37-.62l-.11-.19c-.9-1.57-2.38-1.57-3.29 0l-.11.19c-.49.82-1.55 1.11-2.37.62l-1.73-.99a1.9 1.9 0 0 1-.7-2.59c.91-1.57.17-2.85-1.64-2.85-1.05 0-1.9-.86-1.9-1.9Z"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />
                    </svg>
                    <span class="hidden lg:block">الإعدادات</span>
                </RouterLink>

                <Dropdown width="w-48" class="text-gray-800">
                    <template #trigger>
                        <button type="button" class="flex items-center gap-2" aria-haspopup="menu">
                            <div class="flex items-center justify-center gap-x-2 text-center">
                                <img :src="user.image" alt="" class="w-8 rounded-full">
                                <span class="-mt-2 opacity-50">⌄</span>
                            </div>
                        </button>
                    </template>

                    <div class="truncate p-3">
                        <p>{{ user.name }}</p>
                        <p class="opacity-50">{{ user.email }}</p>
                    </div>

                    <RouterLink
                        to="/account"
                        class="flex items-center gap-x-2 rounded bg-stone-100 p-1.5 hover:bg-stone-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 12a5 5 0 1 0 0-10 5 5 0 0 0 0 10Z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                opacity=".4"
                                d="M20.59 22c0-3.87-3.85-7-8.59-7s-8.59 3.13-8.59 7"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        إدارة الحساب
                    </RouterLink>

                    <RouterLink
                        to="/plan"
                        class="flex items-center gap-x-2 rounded bg-stone-100 p-1.5 hover:bg-stone-200"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                d="M12 15c3.728 0 6.75-2.91 6.75-6.5S15.728 2 12 2 5.25 4.91 5.25 8.5 8.272 15 12 15Z"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                opacity=".4"
                                d="m7.52 13.52-.01 7.38c0 .9.63 1.34 1.41.97l2.68-1.27c.22-.11.59-.11.81 0l2.69 1.27c.77.36 1.41-.07 1.41-.97v-7.56"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                        إدارة الاشتراك
                    </RouterLink>

                    <a href="#" class="flex items-center gap-x-2 rounded bg-gray-100 p-1.5 hover:bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.5"
                                d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"
                            />
                            <g opacity=".4">
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M8 3h1a28.424 28.424 0 000 18H8M15 3a28.424 28.424 0 010 18"
                                />
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.5"
                                    d="M3 16v-1a28.424 28.424 0 0018 0v1M3 9a28.424 28.424 0 0118 0"
                                />
                            </g>
                        </svg>
                        {{ appName }}
                    </a>

                    <a href="#" class="flex items-center gap-x-2 rounded bg-gray-100 p-1.5 hover:bg-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none">
                            <g opacity=".4">
                                <path
                                    stroke="currentColor"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-miterlimit="10"
                                    stroke-width="1.5"
                                    d="M17.44 14.62L20 12.06 17.44 9.5M9.76 12.06h10.17"
                                />
                            </g>
                            <path
                                stroke="currentColor"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-miterlimit="10"
                                stroke-width="1.5"
                                d="M11.76 20c-4.42 0-8-3-8-8s3.58-8 8-8"
                            />
                        </svg>
                        <span>تسجيل الخروج</span>
                    </a>
                </Dropdown>
            </div>
        </div>
    </header>
</template>
