<script setup>
defineProps({
    variant: { type: String, default: 'compact' },
    userName: { type: String, default: '' },
    userEmail: { type: String, default: '' },
    userImage: { type: String, default: '' },
    tenantName: { type: String, default: '' },
    tenantLogo: { type: String, default: null },
    tenantPlan: { type: String, default: '' },
    tenantUrl: { type: String, default: '#' },
    appName: { type: String, default: '' },
    homeUrl: { type: String, default: '/' },
    logoutUrl: { type: String, default: '/logout' },
});

const emit = defineEmits(['navigate']);

function onNavigate() {
    emit('navigate');
}

const linkClass = {
    compact: 'flex items-center gap-x-2 rounded bg-stone-100 p-1.5 hover:bg-stone-200',
    panel: 'flex items-center gap-x-3 rounded-xl px-3 py-2.5 text-sm text-gray-700 transition hover:bg-white',
};
</script>

<template>
    <div v-if="variant === 'panel'" class="border-b border-gray-200 bg-white px-4 py-5">
        <div class="flex items-center gap-3">
            <img :src="userImage" alt="" class="h-12 w-12 rounded-full ring-2 ring-primary-100">
            <div class="min-w-0 flex-1">
                <p class="truncate font-semibold text-gray-900">{{ userName || '…' }}</p>
                <p class="truncate text-sm text-gray-500">{{ userEmail }}</p>
            </div>
        </div>

        <div class="mt-4 flex items-center gap-2.5 rounded-xl bg-stone-50 p-2.5">
            <img
                :src="tenantLogo ?? '/assets/images/user.png'"
                alt=""
                class="h-9 w-9 rounded-lg object-cover"
            >
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-medium text-gray-800">{{ tenantName }}</p>
                <span class="mt-0.5 inline-block rounded-md bg-purple-100 px-1.5 py-0.5 text-xs font-medium text-purple-700">
                    {{ tenantPlan }}
                </span>
            </div>
        </div>
    </div>

    <div v-else class="truncate p-3">
        <p>{{ userName || '…' }}</p>
        <p class="opacity-50">{{ userEmail }}</p>
    </div>

    <a
        v-if="variant === 'panel'"
        :href="tenantUrl"
        target="_blank"
        rel="noopener noreferrer"
        class="mx-4 mt-4 flex items-center justify-between gap-3 rounded-xl bg-green-600 px-4 py-3.5 text-white shadow-sm transition hover:bg-green-500"
        @click="onNavigate"
    >
        <div class="min-w-0">
            <p class="text-sm font-medium">معاينة الصفحة</p>
            <p class="truncate text-xs text-green-100">افتح موقعك في تبويب جديد</p>
        </div>
        <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-5 w-5 shrink-0 ltr:rotate-90"
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

    <div :class="variant === 'panel' ? 'space-y-0.5 px-3 py-4' : 'space-y-1'">
        <RouterLink
            to="/account"
            :class="linkClass[variant]"
            @click="onNavigate"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
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
            :class="linkClass[variant]"
            @click="onNavigate"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
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

        <a :href="homeUrl" :class="linkClass[variant]" @click="onNavigate">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
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

        <a :href="logoutUrl" :class="linkClass[variant]" @click="onNavigate">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none">
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
    </div>
</template>
