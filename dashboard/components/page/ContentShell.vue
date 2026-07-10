<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import MainBox from '../ui/MainBox.vue';

// Header + sub-nav shared by a content type's index / categories / settings / detail routes.
// Section is derived from the route so the correct sub-tab stays lit when the shell is reused
// across sibling routes (and when switching content types on the same section).
const props = defineProps({
    contentType: { type: Object, required: true },
});

const route = useRoute();

const section = computed(() => {
    if (route.name === 'manage-categories') {
        return 'categories';
    }

    if (route.name === 'manage-settings') {
        return 'settings';
    }

    // manage-index + manage-detail both keep "العناصر" active
    return 'index';
});

const subTabs = computed(() => {
    const slug = props.contentType.slug;

    return [
        { key: 'index', label: 'العناصر', to: `/manage/${slug}` },
        { key: 'categories', label: 'التصنيفات', to: `/manage/${slug}/categories` },
        { key: 'settings', label: 'الإعدادات', to: `/manage/${slug}/settings` },
    ];
});
</script>

<template>
    <MainBox :title="contentType.name" :subtitle="contentType.description">
        <template #icon>
            <img :src="`/${contentType.icon}`" class="h-7 w-7" alt="">
        </template>

        <div>
            <div class="flex border-b border-stone-200 px-px">
                <RouterLink
                    v-for="tab in subTabs"
                    :key="tab.key"
                    :to="tab.to"
                    class="px-4 py-3 text-sm transition"
                    :class="section === tab.key ? 'border-b-2 border-primary-500 text-stone-900' : 'text-gray-500 hover:text-gray-800'"
                >
                    {{ tab.label }}
                </RouterLink>
            </div>

            <slot />
        </div>
    </MainBox>
</template>
