<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import Dropdown from './Dropdown.vue';
import HeaderUserMenu from './HeaderUserMenu.vue';
import BrandMark from './ui/BrandMark.vue';
import Icon from './ui/Icon.vue';
import { useSession } from '../stores/session.js';

const route = useRoute();
const { user, tenant, loaded } = useSession();

const mobileMenuOpen = ref(false);

const tenantName = computed(() => tenant.value?.name ?? '…');
const tenantLogo = computed(() => tenant.value?.logo ?? null);
const tenantBrandMark = computed(() => tenant.value?.brand_mark ?? null);
const tenantUrl = computed(() => tenant.value?.url ?? '#');
const tenantPlan = computed(() => tenant.value?.plan ?? 'بداية');
const userImage = computed(() => user.value?.image ?? 'https://www.gravatar.com/avatar/?d=mp');

function openMobileMenu() {
    mobileMenuOpen.value = true;
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}

function onEscape(event) {
    if (event.key === 'Escape') {
        closeMobileMenu();
    }
}

watch(mobileMenuOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

onMounted(() => {
    window.addEventListener('keydown', onEscape);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <header class="fixed inset-x-0 top-0 z-40 bg-primary-700 p-2 text-white">
        <div class="mx-auto flex max-w-7xl justify-between gap-x-2 lg:gap-x-3">
            <div class="flex min-w-0 flex-1 items-center justify-start gap-x-2">
                <RouterLink to="/" class="flex min-w-0 cursor-pointer items-center gap-x-2">
                    <BrandMark
                        class="ms-1"
                        :mark="tenantBrandMark"
                        :url="tenantLogo"
                        :alt="tenantName"
                        size-class="size-8 rounded-sm"
                        icon-class="text-2xl leading-none"
                        img-class="rounded-sm object-cover"
                    />
                    <span class="truncate">{{ loaded ? tenantName : '…' }}</span>
                </RouterLink>

                <RouterLink
                    to="/plan"
                    class="shrink-0 cursor-pointer"
                    :title="tenantPlan"
                    :aria-label="`الباقة: ${tenantPlan}`"
                >
                    <span class="flex items-center gap-x-1 rounded-full bg-amber-700  p-1 ms-0 lg:ms-3 text-amber-100 hover:bg-amber-600 md:px-1.5">
                        <!-- <Icon name="coin" class="h-4 w-4 shrink-0" /> -->
                        <iconify-icon icon="hugeicons:crown-03" class="text-lg md:text-base shrink-0" />
                        <span class="hidden text-sm md:inline">{{ tenantPlan }}</span>
                    </span>
                </RouterLink>
            </div>

            <div class="flex shrink-0 items-center gap-x-3">
                <a
                    :href="tenantUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="hidden cursor-pointer items-center gap-x-2 rounded-full bg-green-600 p-1 px-3 text-sm text-white hover:bg-green-500 md:flex"
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
                    class="flex cursor-pointer items-center gap-x-2 rounded-full p-1 px-2 text-sm text-white hover:bg-black/30"
                    :class="{ 'bg-black/30': route.name === 'settings' }"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-6 md:size-5" viewBox="0 0 24 24" fill="none">
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

                <button
                    type="button"
                    class="flex cursor-pointer items-center gap-2 md:hidden"
                    aria-haspopup="dialog"
                    aria-label="فتح القائمة"
                    @click="openMobileMenu"
                >
                    <img :src="userImage" alt="" class="w-8 rounded-full">
                </button>

                <div class="hidden md:block ms-0 md:ms-2">
                    <Dropdown width="w-64" class="text-stone-800">
                        <template #trigger>
                            <button type="button" class="flex cursor-pointer items-center gap-2" aria-haspopup="menu">
                                <div class="flex items-center justify-center gap-x-2 text-center">
                                    <img :src="userImage" alt="" class="w-8 rounded-full">
                                    <span class="-mt-2 text-white opacity-50">⌄</span>
                                </div>
                            </button>
                        </template>

                        <HeaderUserMenu variant="compact" />
                    </Dropdown>
                </div>
            </div>
        </div>
    </header>

    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity ease-out duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileMenuOpen"
                class="fixed inset-0 z-[60] bg-black/40 md:hidden"
                aria-hidden="true"
                @click="closeMobileMenu"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transform transition ease-in duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside
                v-if="mobileMenuOpen"
                class="fixed bottom-2 left-2 top-2 z-[60] flex w-72 max-w-[85vw] flex-col overflow-y-auto overscroll-y-contain rounded-2xl bg-white shadow-2xl md:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="قائمة الحساب"
            >
                <div class="absolute left-3 top-3 z-10">
                    <button
                        type="button"
                        class="cursor-pointer rounded-lg p-1.5 text-stone-400 transition hover:bg-stone-100 hover:text-stone-800"
                        aria-label="إغلاق القائمة"
                        @click="closeMobileMenu"
                    >
                        <Icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <HeaderUserMenu
                    variant="panel"
                    @navigate="closeMobileMenu"
                />
            </aside>
        </Transition>
    </Teleport>
</template>
