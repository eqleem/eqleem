<script setup>
import { useRoute } from 'vue-router';
import { fixedTabs, contentTabs, colorBg, colorHover } from '../../data/page.js';

// Mirror Nav.vue: a content type stays lit across its whole section
// (/manage/:type, /categories, /settings, /detail/:id). Fixed tabs only
// light on /manage itself, keyed by the `tab` query param.
const route = useRoute();

function isFixedActive(tabId) {
    if (route.path !== '/manage') {
        return false;
    }

    return (route.query.tab ?? 'structure') === tabId;
}

function isTypeActive(slug) {
    const base = `/manage/${slug}`;

    return route.path === base || route.path.startsWith(`${base}/`);
}
</script>

<template>
    <nav class="w-auto shrink-0 space-y-0.5 rounded-xl bg-gray-300/30 p-0.5 lg:w-48">
        <!-- Fixed tabs — bg only in one branch so it never fights Tailwind source order -->
        <RouterLink
            v-for="tab in fixedTabs"
            :key="tab.id"
            :to="{ path: '/manage', query: { tab: tab.id } }"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-start text-sm transition"
            :class="isFixedActive(tab.id) ? 'bg-white text-gray-700' : 'bg-stone-100/50 text-gray-600 hover:bg-white/60 hover:text-gray-800'"
        >
            <img :src="`/${tab.icon}`" :alt="tab.label" class="h-5 w-5 shrink-0">
            <span class="hidden truncate md:block">{{ tab.label }}</span>
        </RouterLink>

        <div>
            <p class="mt-3 hidden px-3 py-1 text-xs text-gray-400 md:block">المحتوى</p>
            <div class="mx-1 mb-2 border-t border-dotted border-gray-300 max-lg:mb-6"></div>
        </div>

        <!-- Content tabs — same: never stack bg-stone with the active color class -->
        <RouterLink
            v-for="tab in contentTabs"
            :key="tab.id"
            :to="`/manage/${tab.contentType.slug}`"
            class="flex w-full items-center gap-2 rounded-lg text-start text-sm transition"
            :class="isTypeActive(tab.contentType.slug) ? `${colorBg[tab.color]} text-gray-900` : `bg-stone-100/50 ${colorHover[tab.color]} text-gray-600 hover:text-gray-800`"
        >
            <span class="flex shrink-0 items-center justify-center rounded-s-lg p-2 max-md:!bg-transparent" :class="colorBg[tab.color]">
                <img :src="`/${tab.icon}`" :alt="tab.label" class="h-5 w-5">
            </span>
            <span class="hidden truncate md:block">{{ tab.label }}</span>
        </RouterLink>

        <!-- Tailwind color safelist -->
        <div class="hidden bg-blue-50 hover:bg-blue-50 bg-orange-50 hover:bg-orange-50 bg-violet-50 hover:bg-violet-50 bg-yellow-50 hover:bg-yellow-50 bg-green-50 hover:bg-green-50 bg-red-50 hover:bg-red-50 bg-teal-50 hover:bg-teal-50 bg-rose-50 hover:bg-rose-50 bg-amber-50 hover:bg-amber-50 bg-lime-50 hover:bg-lime-50 bg-pink-50 hover:bg-pink-50 bg-gray-50 hover:bg-gray-50" aria-hidden="true"></div>
    </nav>
</template>
