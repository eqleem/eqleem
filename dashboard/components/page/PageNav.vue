<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import { useRoute } from 'vue-router';
import Icon from '../ui/Icon.vue';
import { fixedTabs } from '../../data/page.js';
import { useContentTypesStore } from '../../stores/contentTypes.js';

// Mirror Nav.vue: a content type stays lit across its whole section
// (/manage/:type, /categories, /settings, /detail/:id). Fixed tabs only
// light on /manage itself, keyed by the `tab` query param.
const route = useRoute();
const mobileNavOpen = ref(false);
const contentTypesStore = useContentTypesStore();
const { tabs } = storeToRefs(contentTypesStore);

/** Shared section order for desktop sidebar + mobile slideout. */
const sectionLabels = {
    sell: 'البيع',
    content: 'المحتوى',
    permanent: '. . .',
    trust: 'الثقة والمصداقية',
};
const sectionOrder = ['sell', 'content', 'permanent', 'trust'];
const permanentContentTypes = new Set(['pages', 'forms']);
const contentSections = computed(() => {
    const groups = new Map();

    for (const tab of tabs.value) {
        const section = permanentContentTypes.has(tab.slug)
            ? 'permanent'
            : (tab.section || 'content');

        if (!groups.has(section)) {
            groups.set(section, []);
        }

        groups.get(section).push(tab);
    }

    return [...groups.entries()]
        .map(([id, sectionTabs]) => ({
            id,
            label: sectionLabels[id] || id,
            tabs: sectionTabs,
        }))
        .sort((first, second) => {
            const firstIndex = sectionOrder.indexOf(first.id);
            const secondIndex = sectionOrder.indexOf(second.id);

            return (firstIndex === -1 ? sectionOrder.length : firstIndex)
                - (secondIndex === -1 ? sectionOrder.length : secondIndex);
        });
});

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

function openMobileNav() {
    mobileNavOpen.value = true;
}

function closeMobileNav() {
    mobileNavOpen.value = false;
}

function onEscape(event) {
    if (event.key === 'Escape') {
        closeMobileNav();
    }
}

watch(mobileNavOpen, (open) => {
    document.body.style.overflow = open ? 'hidden' : '';
});

onMounted(() => {
    window.addEventListener('keydown', onEscape);
    contentTypesStore.fetchContentTypes();
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', onEscape);
    document.body.style.overflow = '';
});
</script>

<template>
    <nav class="sticky top-[var(--dashboard-chrome-h)] z-20 max-h-[calc(100vh-var(--dashboard-chrome-h))] w-auto shrink-0 self-start space-y-0.5 overflow-y-auto overflow-x-hidden overscroll-y-contain rounded-xl bg-stone-300/30 p-0.5 lg:w-48">
        <button
            type="button"
            class="flex w-full items-center justify-center mb-2 gap-2 rounded-lg px-1 py-1.5 text-white transition bg-primary-500 hover:bg-primary-600 lg:hidden"
            aria-label="فتح قائمة التبويبات"
            @click="openMobileNav"
        >
            <Icon name="menu-2" class="size-7 lg:size-5 shrink-0" />
            <span class="hidden truncate text-sm lg:block">كل التبويبات</span>
        </button>

        <!-- Fixed tabs — bg only in one branch so it never fights Tailwind source order -->
        <RouterLink
            v-for="tab in fixedTabs"
            :key="tab.id"
            :to="{ path: '/manage', query: { tab: tab.id } }"
            class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-start text-sm transition"
            :class="isFixedActive(tab.id) ? 'bg-white text-stone-700' : 'xbg-stone-100/50 text-stone-600 hover:bg-white/60 hover:text-stone-800'"
        >
            <img :src="`/${tab.icon}`" :alt="tab.label" class="size-6 lg:size-5 shrink-0">
            <span class="hidden truncate lg:block">{{ tab.label }}</span>
        </RouterLink>

        <template v-for="section in contentSections" :key="section.id">
            <div class="hidden px-2 pt-6 lg:block">
                <p class="truncate text-xs text-stone-400">{{ section.label }}</p>
            </div>
            <div class="mx-1 mb-2 border-t border-dotted border-stone-300 max-lg:mb-6"></div>

            <RouterLink
                v-for="tab in section.tabs"
                :key="tab.id"
                :to="`/manage/${tab.slug}`"
                class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-start text-sm transition"
                :class="isTypeActive(tab.slug) ? 'bg-white text-stone-700' : 'xbg-stone-100/50 text-stone-600 hover:bg-white/60 hover:text-stone-800'"
            >
                <img :src="`/${tab.icon}`" :alt="tab.label" class="size-6 lg:size-5 shrink-0">
                <span class="hidden truncate lg:block">{{ tab.label }}</span>
            </RouterLink>
        </template>
    </nav>

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
                v-if="mobileNavOpen"
                class="fixed inset-0 z-50 bg-black/40 lg:hidden"
                aria-hidden="true"
                @click="closeMobileNav"
            ></div>
        </Transition>

        <Transition
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transform transition ease-in duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="translate-x-full"
        >
            <nav
                v-if="mobileNavOpen"
                class="fixed bottom-0 right-0 top-[var(--dashboard-chrome-h)] z-50 w-72 max-w-[85vw] space-y-0.5 overflow-y-auto overscroll-y-contain rounded-e-2xl bg-stone-100 p-3 shadow-2xl lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-label="قائمة التبويبات"
            >
                <div class="mb-2 flex items-center justify-between px-1">
                    <p class="text-sm font-medium text-stone-700">التبويبات</p>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 text-stone-500 transition hover:bg-white/70 hover:text-stone-800"
                        aria-label="إغلاق القائمة"
                        @click="closeMobileNav"
                    >
                        <Icon name="x" class="h-5 w-5" />
                    </button>
                </div>

                <RouterLink
                    v-for="tab in fixedTabs"
                    :key="`slideout-${tab.id}`"
                    :to="{ path: '/manage', query: { tab: tab.id } }"
                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-start text-sm transition"
                    :class="isFixedActive(tab.id) ? 'bg-white text-stone-700' : 'text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                    @click="closeMobileNav"
                >
                    <img :src="`/${tab.icon}`" :alt="tab.label" class="h-5 w-5 shrink-0">
                    <span class="truncate">{{ tab.label }}</span>
                </RouterLink>

                <template v-for="section in contentSections" :key="`slideout-section-${section.id}`">
                    <div class="px-2 pt-6">
                        <p class="truncate text-xs text-stone-400">{{ section.label }}</p>
                    </div>
                    <div class="mx-1 mb-2 border-t border-dotted border-stone-300"></div>

                    <RouterLink
                        v-for="tab in section.tabs"
                        :key="`slideout-${section.id}-${tab.id}`"
                        :to="`/manage/${tab.slug}`"
                        class="flex w-full items-center gap-2 rounded-lg px-3 py-2.5 text-start text-sm transition"
                        :class="isTypeActive(tab.slug) ? 'bg-white text-stone-700' : 'bg-stone-100/50 text-stone-600 hover:bg-white/60 hover:text-stone-800'"
                        @click="closeMobileNav"
                    >
                        <img :src="`/${tab.icon}`" :alt="tab.label" class="h-5 w-5 shrink-0">
                        <span class="truncate">{{ tab.label }}</span>
                    </RouterLink>
                </template>
            </nav>
        </Transition>
    </Teleport>
</template>
