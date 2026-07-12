<script setup>
// Port of resources/views/ui/field.blade.php
defineProps({
    name: { type: String, default: '' },
    label: { type: String, default: null },
    labelWidth: { type: String, default: 'w-36' },
    info: { type: String, default: '' },
    block: { type: Boolean, default: false },
    prefix: { type: String, default: null },
    suffix: { type: String, default: null },
    dir: { type: String, default: null },
    width: { type: String, default: 'w-full' },
    errormsg: { type: Boolean, default: true },
    error: { type: String, default: null },
    prime: { type: Boolean, default: false },
    infoDir: { type: String, default: null },
});
</script>

<template>
    <div
        class="relative items-center gap-x-2 rounded-md bg-stone-100/75 p-1 lg:flex"
        :class="[block ? 'w-full flex-col items-start' : width, { 'border border-dashed border-red-500 pt-10': prime }]"
    >
        <div v-if="prime" title="هذا الخيار يتطلب اشتراك برايم" class="absolute end-0 top-0 p-px text-end">
            <span class="ms-1 inline-block rounded-sm bg-orange-50 px-2 py-1 text-sm font-bold text-orange-600">برايم 👑</span>
        </div>

        <label
            v-if="label"
            :for="name"
            class="inline-block flex-shrink-0 p-2 text-sm font-semibold text-stone-500"
            :class="block ? 'w-full' : labelWidth"
        >
            {{ label }}
        </label>

        <div class="relative" :class="width" :dir="dir">
            <div class="flex w-full items-center text-stone-500">
                <div v-if="prefix" class="shrink-0 px-2 text-xs opacity-70">
                    {{ prefix }}
                </div>

                <div class="w-full">
                    <slot />
                </div>

                <div v-if="suffix" class="shrink-0 px-2 text-xs opacity-70">
                    {{ suffix }}
                </div>
            </div>

            <div>
                <small
                    v-if="info"
                    class="mt-1 flex items-center gap-x-1 rounded-md bg-stone-50 px-1 text-xs text-stone-400"
                    :dir="infoDir"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8h.01M11 12h1v4h1" />
                    </svg>
                    {{ info }}
                </small>
            </div>

            <small
                v-if="errormsg && error"
                class="mt-1 flex items-center gap-x-1 rounded-md bg-red-50 px-1 text-xs text-red-600"
                :dir="infoDir"
            >
                <svg class="h-4 w-4 text-red-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v5M12 16h.01" />
                </svg>
                {{ error }}
            </small>
        </div>
    </div>
</template>
