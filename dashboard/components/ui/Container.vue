<script setup>
// Port of resources/views/ui/container.blade.php
defineOptions({ inheritAttrs: false });

defineProps({
    title: { type: String, default: '' },
    backRoute: { type: [String, Object], default: null },
});
</script>

<template>
    <div>
        <div v-if="title" class="bg-stone-300 p-px text-base text-stone-500">
            <div class="mx-auto flex max-w-7xl items-center gap-x-3 truncate text-sm">
                <RouterLink
                    v-if="backRoute"
                    :to="backRoute"
                    class="flex items-center gap-x-2 bg-stone-200 p-2 text-sm hover:bg-stone-100"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ltr:rotate-180" viewBox="0 0 24 24" fill="none">
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-miterlimit="10"
                            stroke-width="1.5"
                            d="M14.43 5.93L20.5 12l-6.07 6.07"
                        />
                        <path
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-miterlimit="10"
                            stroke-width="1.5"
                            d="M3.5 12h16.83"
                            opacity=".4"
                        />
                    </svg>
                </RouterLink>
                <p>{{ title }}</p>
            </div>
        </div>

        <div class="mx-auto max-w-7xl px-1 py-6 md:px-0 md:py-10" v-bind="$attrs">
            <slot />
        </div>
    </div>
</template>
