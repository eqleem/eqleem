<script setup>
defineProps({
    rows: { type: Number, default: 5 },
});
</script>

<template>
    <div
        class="animate-pulse space-y-4 p-4"
        aria-busy="true"
        aria-live="polite"
        aria-label="جاري تحميل إعدادات البلوك"
    >
        <div class="space-y-2">
            <div class="h-3 w-40 max-w-[70%] rounded bg-gray-100" />
            <div class="h-2.5 w-56 max-w-full rounded bg-gray-100/80" />
        </div>

        <div class="space-y-2.5">
            <div
                v-for="n in rows"
                :key="n"
                class="flex items-center justify-between gap-3 rounded-xl border border-gray-100 bg-gray-50/70 px-3 py-3"
            >
                <div class="min-w-0 flex-1 space-y-2">
                    <div
                        class="h-3 rounded bg-gray-200/80"
                        :class="n % 2 === 0 ? 'w-28' : 'w-36'"
                    />
                    <div
                        v-if="n === 1 || n === 3"
                        class="h-2.5 w-44 max-w-full rounded bg-gray-100"
                    />
                </div>
                <div class="h-5 w-10 shrink-0 rounded-full bg-gray-200/80" />
            </div>
        </div>

        <div class="flex justify-end border-t border-gray-100 pt-4">
            <div class="h-9 w-20 rounded-lg bg-gray-200/80" />
        </div>
    </div>
</template>
