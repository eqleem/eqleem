<script setup>
import { computed, onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import Modal from '../ui/Modal.vue';
import Button from '../ui/Button.vue';
import CompletionContent from './CompletionContent.vue';
import CompletionContentAddModals from './CompletionContentAddModals.vue';
import { useWelcomeStore } from '../../stores/welcome.js';
import { useOnboardingStore } from '../../stores/onboarding.js';
import { openModal } from '../../lib/modal.js';

const store = useWelcomeStore();
const onboardingStore = useOnboardingStore();
const {
    userName,
    pageUrl,
    shareText,
    loading,
    loaded,
} = storeToRefs(store);

const { completed: onboardingCompleted } = storeToRefs(onboardingStore);

const copied = ref(false);
const shareInput = ref(null);

const isReady = computed(() => onboardingCompleted.value);

const qrImageUrl = (size = 220) =>
    `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(pageUrl.value || '')}`;

const socials = [
    { platform: 'whatsapp', label: 'واتساب', icon: 'mdi:whatsapp', class: 'text-green-600' },
    { platform: 'telegram', label: 'تيلجرام', icon: 'mdi:telegram', class: 'text-sky-500' },
    { platform: 'x', label: 'X', icon: 'ri:twitter-x-fill', class: 'text-stone-900' },
    { platform: 'facebook', label: 'فيسبوك', icon: 'ic:baseline-facebook', class: 'text-blue-600' },
];

onMounted(() => {
    store.fetchWelcome();
});

async function copyLink() {
    try {
        await navigator.clipboard.writeText(pageUrl.value);
    } catch {
        shareInput.value?.select();
        document.execCommand('copy');
    }

    copied.value = true;
    setTimeout(() => {
        copied.value = false;
    }, 2500);
}

function shareLink(platform) {
    const url = encodeURIComponent(pageUrl.value);
    const text = encodeURIComponent(shareText.value);

    switch (platform) {
        case 'whatsapp':
            return `https://wa.me/?text=${text}%20${url}`;
        case 'telegram':
            return `https://t.me/share/url?url=${url}&text=${text}`;
        case 'x':
            return `https://twitter.com/intent/tweet?url=${url}&text=${text}`;
        case 'facebook':
            return `https://www.facebook.com/sharer/sharer.php?u=${url}`;
        default:
            return pageUrl.value;
    }
}
</script>

<template>
    <div
        class="mb-6 overflow-hidden rounded-2xl bg-primary-700 text-white"
        :class="{ 'animate-pulse opacity-80': loading && !loaded }"
    >
        <div
            class="gap-0"
            :class="isReady ? 'flex flex-col' : 'grid lg:grid-cols-[1fr_auto]'"
        >
            <div
                class="p-5 sm:p-6"
                :class="isReady ? 'border-b border-white/10 pb-4' : ''"
            >
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold">مرحباً، {{ userName || '…' }} 👋</h2>
                    <Button
                        variant="light"
                        class="h-auto shrink-0 rounded-lg px-2 py-1.5 text-xs font-semibold sm:px-4 sm:py-2 sm:text-sm"
                        @click="openModal('home-step-content')"
                    >
                        <template #icon>
                            <iconify-icon icon="hugeicons:plus-sign-square" class="text-xl -mx-1 me-px"></iconify-icon>
                        </template>
                        إضافة محتوى
                    </Button>
                </div>
            </div>

            <div
                class="bg-black/10 p-5 sm:p-6"
                :class="isReady ? '' : 'border-t border-white/10 lg:w-96 lg:border-s lg:border-t-0'"
            >
                <div class="flex items-center gap-2 rounded-xl bg-white/10 p-2 ring-1 ring-white/10">
                    <input
                        ref="shareInput"
                        type="text"
                        dir="ltr"
                        readonly
                        :value="pageUrl"
                        class="min-w-0 flex-1 truncate bg-transparent px-2 text-sm text-white outline-none"
                    >
                </div>

                <div class="mt-3 grid grid-cols-4 gap-2">
                    <a
                        :href="pageUrl || '#'"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl bg-green-500 p-2 text-center text-[11px] font-medium text-white transition hover:bg-green-600"
                        title="معاينة الصفحة"
                    >
                        <iconify-icon icon="hugeicons:share-05" class="text-2xl -rotate-90"></iconify-icon>
                        معاينة
                    </a>

                    <button
                        type="button"
                        class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2 text-center text-[11px] font-medium transition hover:bg-white/20"
                        title="نسخ الرابط"
                        @click="copyLink"
                    >
                        <iconify-icon :icon="copied ? 'hugeicons:copy-check' : 'hugeicons:copy-02'" class="text-2xl"></iconify-icon>
                        {{ copied ? 'تم' : 'نسخ' }}
                    </button>

                    <button
                        type="button"
                        class="flex cursor-pointer flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2 text-center text-[11px] font-medium transition hover:bg-white/20"
                        title="مشاركة الصفحة"
                        @click="openModal('home-share-page')"
                    >
                        <iconify-icon icon="hugeicons:share-03" class="text-2xl"></iconify-icon>
                        مشاركة
                    </button>

                    <button
                        type="button"
                        class="flex cursor-pointer items-center justify-center rounded-xl bg-white p-2 ring-1 ring-white/20 transition hover:bg-white/90"
                        title="رمز QR — اضغط للتكبير"
                        @click="openModal('home-page-qr')"
                    >
                        <img :src="qrImageUrl(120)" alt="رمز QR للصفحة" class="size-12 rounded-md" loading="lazy">
                    </button>
                </div>
            </div>
        </div>

        <Modal title="مشاركة الصفحة" size="lg" name="home-share-page">
            <div class="space-y-4 p-4 text-stone-800" dir="rtl">
                <p class="text-sm text-stone-600">انسخ الرابط أو شاركه مباشرة عبر المنصات.</p>

                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        dir="ltr"
                        readonly
                        :value="pageUrl"
                        class="w-full rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5 text-sm text-stone-700"
                    >
                    <Button
                        variant="primary"
                        class="h-auto shrink-0 rounded-xl px-4 py-2.5 font-semibold"
                        @click="copyLink"
                    >
                        <template #icon>
                            <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                        </template>
                        {{ copied ? 'تم النسخ' : 'نسخ' }}
                    </Button>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <a
                        v-for="social in socials"
                        :key="social.platform"
                        :href="shareLink(social.platform)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex cursor-pointer items-center justify-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
                    >
                        <iconify-icon :icon="social.icon" class="text-lg" :class="social.class"></iconify-icon>
                        {{ social.label }}
                    </a>
                </div>
            </div>
        </Modal>

        <Modal title="رمز QR للصفحة" size="md" name="home-page-qr">
            <div class="space-y-4 p-4 text-center text-stone-800" dir="rtl">
                <p class="text-sm text-stone-600">امسح الرمز لمشاركة صفحتك بسرعة.</p>
                <div class="mx-auto inline-block rounded-2xl border border-stone-100 bg-white p-4 shadow-sm">
                    <img :src="qrImageUrl(220)" alt="رمز QR للصفحة" class="mx-auto size-[220px]" loading="lazy">
                </div>
                <p class="truncate text-xs text-stone-500" dir="ltr">{{ pageUrl }}</p>
                <Button
                    variant="primary"
                    class="h-auto rounded-xl px-4 py-2.5 font-semibold"
                    @click="copyLink"
                >
                    <template #icon>
                        <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                    </template>
                    {{ copied ? 'تم النسخ' : 'نسخ الرابط' }}
                </Button>
            </div>
        </Modal>

        <Modal title="إضافة محتوى" size="2xl" name="home-step-content">
            <CompletionContent />
        </Modal>

        <CompletionContentAddModals />
    </div>
</template>
