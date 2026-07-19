<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

const notices = ref([]);
const dismissTimers = new Map();
let counter = 0;

const toneClasses = {
    success: 'bg-emerald-50 text-emerald-800',
    error: 'bg-red-50 text-red-800',
    info: 'bg-primary-50 text-primary-800',
    warning: 'bg-amber-50 text-amber-900',
};

async function addNotice(detail) {
    const id = ++counter;
    const notice = {
        id,
        text: detail.text,
        type: detail.type ?? 'success',
        visible: false,
    };

    notices.value.push(notice);
    await nextTick();

    const item = notices.value.find((entry) => entry.id === id);

    if (item) {
        item.visible = true;
    }

    dismissTimers.set(id, window.setTimeout(() => dismiss(id), detail.duration ?? 3500));
}

function dismiss(id) {
    const timer = dismissTimers.get(id);

    if (timer) {
        window.clearTimeout(timer);
        dismissTimers.delete(id);
    }

    const item = notices.value.find((entry) => entry.id === id);

    if (item) {
        item.visible = false;
    }

    window.setTimeout(() => {
        notices.value = notices.value.filter((entry) => entry.id !== id);
    }, 280);
}

function onNotify(event) {
    if (!event.detail?.text) {
        return;
    }

    addNotice(event.detail);
}

onMounted(() => {
    window.addEventListener('dashboard:notify', onNotify);
});

onBeforeUnmount(() => {
    window.removeEventListener('dashboard:notify', onNotify);
});
</script>

<template>
    <div
        class="pointer-events-none fixed right-4 bottom-4 left-4 z-[60] flex flex-col gap-2 md:top-4 md:right-auto md:bottom-auto md:max-w-sm"
        aria-live="polite"
        aria-relevant="additions"
    >
        <TransitionGroup
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="translate-y-2 opacity-0 md:-translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="translate-y-2 opacity-0 md:-translate-y-2"
        >
            <button
                v-for="notice in notices"
                v-show="notice.visible"
                :key="notice.id"
                type="button"
                class="pointer-events-auto w-full cursor-pointer rounded-lg px-4 py-3 text-start text-sm transition hover:opacity-90"
                :class="toneClasses[notice.type] ?? toneClasses.success"
                @click="dismiss(notice.id)"
            >
                <span class="leading-relaxed">{{ notice.text }}</span>
            </button>
        </TransitionGroup>
    </div>
</template>
