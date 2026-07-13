<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

// Port of resources/views/ui/modal.blade.php — x-teleport -> <Teleport>, Alpine open/close
// via the same 'openmodal'/'closemodal' window events (see dashboard/lib/modal.js).
const props = defineProps({
    title: { type: String, default: null },
    size: { type: String, default: '4xl' },
    name: { type: String, default: 'default' },
    escape: { type: Boolean, default: true },
    close: { type: Boolean, default: true },
});

const show = ref(false);

// Literal classes so Tailwind picks them up (dynamic string interpolation would be purged).
const sizeClasses = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
    '3xl': 'sm:max-w-3xl',
    '4xl': 'sm:max-w-4xl',
    '5xl': 'sm:max-w-5xl',
};

function hide() {
    if (!show.value) {
        return;
    }

    show.value = false;
    document.body.classList.remove('overflow-hidden');
    window.dispatchEvent(new CustomEvent('closemodal', { detail: { modal: props.name } }));
}

function onOpen(event) {
    if (event.detail?.modal === props.name) {
        show.value = true;
        document.body.classList.add('overflow-hidden');
    }
}

function onClose(event) {
    if (!event.detail?.modal || event.detail.modal === props.name) {
        if (show.value) {
            show.value = false;
            document.body.classList.remove('overflow-hidden');
        }
    }
}

function onKeydown(event) {
    if (props.escape && event.key === 'Escape') {
        hide();
    }
}

onMounted(() => {
    window.addEventListener('openmodal', onOpen);
    window.addEventListener('closemodal', onClose);
    window.addEventListener('keydown', onKeydown);
});

onBeforeUnmount(() => {
    window.removeEventListener('openmodal', onOpen);
    window.removeEventListener('closemodal', onClose);
    window.removeEventListener('keydown', onKeydown);
    document.body.classList.remove('overflow-hidden');
});
</script>

<template>
    <Teleport to="body">
        <div v-if="show" class="relative z-40" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-stone-800/75 transition-opacity"></div>

            <div class="fixed inset-0 overflow-y-auto">
                <div
                    class="flex min-h-full w-full items-center justify-center"
                    @click.self="escape && hide()"
                >
                    <div
                        class="relative m-4 w-full max-w-5xl transform rounded-xl bg-white pb-2.5 shadow-xl transition-all lg:m-auto"
                        :class="sizeClasses[size]"
                    >
                        <div class="flex w-full items-center justify-between border-b border-stone-100 p-2">
                            <p v-if="title" class="flex items-center gap-x-2 px-2 text-sm font-semibold text-stone-600">
                                {{ title }}
                            </p>
                            <span v-else></span>

                            <button
                                v-if="close"
                                type="button"
                                class="rounded-md bg-stone-100 p-1 text-stone-400 hover:bg-stone-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                @click.prevent="hide"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="w-full">
                            <slot />
                        </div>

                        <div v-if="$slots.footer" class="border-t-2 border-stone-100 p-3 px-5 shadow">
                            <slot name="footer" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>
