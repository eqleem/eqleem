<script setup>
import BrandMark from './ui/BrandMark.vue';
import Icon from './ui/Icon.vue';

defineProps({
    variant: { type: String, default: 'compact' },
    userName: { type: String, default: '' },
    userEmail: { type: String, default: '' },
    userPhone: { type: String, default: '' },
    userImage: { type: String, default: '' },
    tenantName: { type: String, default: '' },
    tenantLogo: { type: String, default: null },
    tenantBrandMark: { type: Object, default: null },
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
    panel: 'flex items-center gap-x-3 rounded-xl px-3 py-2.5 text-sm text-stone-700 transition hover:bg-white',
};
</script>

<template>
    <div v-if="variant === 'panel'" class="flex min-h-full flex-1 flex-col">
        <div class="px-4 pb-5 pt-12">
            <RouterLink
                to="/plan"
                class="flex items-center justify-between gap-3 rounded-xl transition hover:bg-stone-50"
                aria-label="إدارة الباقة"
                @click="onNavigate"
            >
                <div class="flex min-w-0 items-center gap-3">
                    <BrandMark
                        :mark="tenantBrandMark"
                        :url="tenantLogo"
                        :alt="tenantName"
                        size-class="size-11 rounded-xl"
                        icon-class="text-2xl leading-none"
                        img-class="rounded-xl object-cover"
                    />
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-stone-900">{{ tenantName }}</p>
                        <p class="mt-0.5 truncate text-xs text-stone-500">باقة {{ tenantPlan }}</p>
                    </div>
                </div>
                <span class="shrink-0 rounded-lg p-2 text-stone-400">
                    <Icon name="settings" class="size-5" />
                </span>
            </RouterLink>

            <a
                :href="tenantUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="mt-5 flex items-center justify-between gap-3 rounded-xl bg-green-600 px-4 py-3.5 text-white shadow-sm transition hover:bg-green-500"
                @click="onNavigate"
            >
                <div class="min-w-0">
                    <p class="text-sm font-medium">معاينة الصفحة</p>
                    <p class="truncate text-xs text-green-100">معاينة صفحتك في تبويب جديد</p>
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
        </div>

        <div class="mx-4 border-t border-stone-200"></div>

        <RouterLink
            to="/account"
            class="mx-2 flex items-center justify-between gap-3 rounded-xl px-2 py-5 transition hover:bg-stone-50"
            aria-label="إدارة الحساب"
            @click="onNavigate"
        >
            <div class="flex min-w-0 items-center gap-3">
                <img :src="userImage" alt="" class="size-11 rounded-full object-cover ring-2 ring-primary-100">
                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-stone-900">{{ userName || '…' }}</p>
                    <p class="truncate text-xs text-stone-500">{{ userEmail || userPhone }}</p>
                </div>
            </div>
            <span class="shrink-0 rounded-lg p-2 text-stone-400">
                <Icon name="settings" class="size-5" />
            </span>
        </RouterLink>

        <div class="mt-auto space-y-2 px-4 pb-4">
            <a
                :href="homeUrl"
                class="flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-stone-500 transition hover:bg-stone-50 hover:text-stone-800"
                @click="onNavigate"
            >
                <img :src="'/assets/images/logo-shape-black.webp'" alt="" class="size-5 object-contain">
                {{ appName }} 
            </a>
            <a
                :href="logoutUrl"
                class="flex items-center gap-2 rounded-xl bg-red-50 px-3 py-2.5 text-sm text-red-600 transition hover:bg-red-100"
                @click="onNavigate"
            >
                <Icon name="logout" class="size-5" />
                <span>تسجيل الخروج</span>
            </a>
        </div>
    </div>

    <div v-else class="space-y-1">
        <div class="truncate p-3">
            <p>{{ userName || '…' }}</p>
            <p class="opacity-50">{{ userEmail || userPhone }}</p>
        </div>

        <RouterLink to="/account" :class="linkClass.compact" @click="onNavigate">
            <Icon name="user" class="size-5 shrink-0" />
            إدارة الحساب
        </RouterLink>

        <RouterLink to="/plan" :class="linkClass.compact" @click="onNavigate">
            <Icon name="coin" class="size-5 shrink-0" />
            إدارة الاشتراك
        </RouterLink>

        <a :href="homeUrl" :class="linkClass.compact" @click="onNavigate">
            {{ appName }}
        </a>

        <a :href="logoutUrl" :class="linkClass.compact" @click="onNavigate">
            <Icon name="logout" class="size-5 shrink-0" />
            <span>تسجيل الخروج</span>
        </a>
    </div>
</template>
