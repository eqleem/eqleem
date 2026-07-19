<script setup>
import { ref } from 'vue';
import Container from '../ui/Container.vue';
import Icon from '../ui/Icon.vue';
import PageNav from './PageNav.vue';

const pageNav = ref(null);

function openMobileNav() {
    pageNav.value?.openMobileNav();
}
</script>

<template>
    <Container>
        <div class="sticky top-[calc(var(--dashboard-chrome-h)+0.5rem)] z-30 mb-2 flex justify-start lg:hidden">
            <button
                type="button"
                class="flex cursor-pointer items-center gap-2 rounded-xl bg-primary-500 px-3 py-2 text-sm font-medium text-white shadow-lg transition hover:bg-primary-600"
                aria-label="فتح قائمة أقسام صفحتي"
                aria-controls="page-mobile-nav"
                @click="openMobileNav"
            >
                <Icon name="menu-2" class="h-5 w-5" />
                <span>أقسام صفحتي</span>
            </button>
        </div>

        <div class="flex items-start gap-2 lg:gap-5">
            <PageNav ref="pageNav" />
            <div class="min-w-0 flex-1">
                <slot />
            </div>
        </div>
    </Container>
</template>
