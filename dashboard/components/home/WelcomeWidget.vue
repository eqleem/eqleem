<script setup>
import { onMounted, ref } from 'vue';
import { storeToRefs } from 'pinia';
import Modal from '../ui/Modal.vue';
import CompletionBasicInfo from './CompletionBasicInfo.vue';
import CompletionContact from './CompletionContact.vue';
import CompletionSocial from './CompletionSocial.vue';
import CompletionContent from './CompletionContent.vue';
import CompletionContentAddModals from './CompletionContentAddModals.vue';
import CompletionVerification from './CompletionVerification.vue';
import { useWelcomeStore } from '../../stores/welcome.js';
import { openModal } from '../../lib/modal.js';

const store = useWelcomeStore();
const {
    greeting,
    userName,
    pageUrl,
    shareText,
    percentage,
    completedSteps,
    totalSteps,
    nextStep,
    loading,
    loaded,
} = storeToRefs(store);

const copied = ref(false);
const shareInput = ref(null);

const qrImageUrl = (size = 220) =>
    `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(pageUrl.value || '')}`;

const socials = [
    { platform: 'whatsapp', label: 'واتساب', icon: 'mdi:whatsapp', class: 'text-green-600' },
    { platform: 'telegram', label: 'تيلجرام', icon: 'mdi:telegram', class: 'text-sky-500' },
    { platform: 'x', label: 'X', icon: 'ri:twitter-x-fill', class: 'text-gray-900' },
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

function openStep(modal) {
    if (!modal) {
        return;
    }

    openModal(modal);
}
</script>

<template>
    <div
        class="mb-6 overflow-hidden rounded-2xl bg-primary-700 text-white"
        :class="{ 'animate-pulse opacity-80': loading && !loaded }"
    >
        <div
            class="gap-0"
            :class="percentage >= 100 ? 'flex flex-col' : 'grid lg:grid-cols-[1fr_auto]'"
        >
            <div
                class="p-5 sm:p-6"
                :class="percentage >= 100 ? 'border-b border-white/10 pb-4' : ''"
            >
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-bold  ">مرحباً، {{ userName || '…' }} 👋</h2>
                    <button 
                        type="button"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-semibold text-primary-700 transition hover:bg-primary-50 sm:gap-2 sm:px-4 sm:py-2 sm:text-sm"
                        @click="openModal('home-step-content')"
                    >
                        <iconify-icon icon="solar:add-circle-bold" class="text-base"></iconify-icon>
                        إضافة محتوى
                    </button>
                </div>

                <template v-if="percentage < 100">
                    <div class="mt-4">
                        <div class="flex items-center justify-between text-xs sm:text-sm">
                            <span class="text-primary-100">{{ completedSteps }}/{{ totalSteps }} خطوات</span>
                            <span class="font-bold">{{ percentage }}%</span>
                        </div>
                        <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-white/20 sm:h-2">
                            <div
                                class="h-full rounded-full bg-amber-300 transition-all duration-700 ease-out"
                                :style="{ width: percentage + '%' }"
                            ></div>
                        </div>
                    </div>

                    <button
                        v-if="nextStep"
                        type="button"
                        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-primary-700 transition hover:bg-primary-50 sm:w-auto"
                        @click="openStep(nextStep.modal)"
                    >
                        <iconify-icon icon="solar:arrow-left-bold" class="text-base"></iconify-icon>
                        {{ nextStep.label }}
                    </button>
                </template>
                <p v-else class="mt-2 text-sm text-primary-100/90">صفحتك جاهزة — شاركها مع عملائك.</p>
            </div>

            <div
                class="bg-black/10 p-5 sm:p-6"
                :class="percentage >= 100 ? '' : 'border-t border-white/10 lg:w-96 lg:border-s lg:border-t-0'"
            >
                <p class="mb-2 text-xs font-medium text-primary-100">رابط صفحتك</p>

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
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-green-500 p-2.5 text-center text-[11px] font-medium text-white transition hover:bg-green-600"
                        title="معاينة الصفحة"
                    >
                        <iconify-icon icon="solar:eye-bold" class="text-lg"></iconify-icon>
                        معاينة
                    </a>

                    <button
                        type="button"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                        title="نسخ الرابط"
                        @click="copyLink"
                    >
                        <iconify-icon :icon="copied ? 'solar:check-circle-bold' : 'solar:copy-bold'" class="text-lg"></iconify-icon>
                        {{ copied ? 'تم' : 'نسخ' }}
                    </button>

                    <button
                        type="button"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                        title="مشاركة الصفحة"
                        @click="openModal('home-share-page')"
                    >
                        <iconify-icon icon="solar:share-bold" class="text-lg"></iconify-icon>
                        مشاركة
                    </button>

                    <button
                        type="button"
                        class="flex items-center justify-center rounded-xl bg-white p-1 ring-1 ring-white/20 transition hover:bg-white/90"
                        title="رمز QR — اضغط للتكبير"
                        @click="openModal('home-page-qr')"
                    >
                        <img :src="qrImageUrl(120)" alt="رمز QR للصفحة" class="size-16 rounded-md" loading="lazy">
                    </button>
                </div>
            </div>
        </div>

        <Modal title="مشاركة الصفحة" size="lg" name="home-share-page">
            <div class="space-y-4 p-4 text-gray-800" dir="rtl">
                <p class="text-sm text-gray-600">انسخ الرابط أو شاركه مباشرة عبر المنصات.</p>

                <div class="flex items-center gap-2">
                    <input
                        type="text"
                        dir="ltr"
                        readonly
                        :value="pageUrl"
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700"
                    >
                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700"
                        @click="copyLink"
                    >
                        <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                        {{ copied ? 'تم النسخ' : 'نسخ' }}
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <a
                        v-for="social in socials"
                        :key="social.platform"
                        :href="shareLink(social.platform)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        <iconify-icon :icon="social.icon" class="text-lg" :class="social.class"></iconify-icon>
                        {{ social.label }}
                    </a>
                </div>
            </div>
        </Modal>

        <Modal title="رمز QR للصفحة" size="md" name="home-page-qr">
            <div class="space-y-4 p-4 text-center text-gray-800" dir="rtl">
                <p class="text-sm text-gray-600">امسح الرمز لمشاركة صفحتك بسرعة.</p>
                <div class="mx-auto inline-block rounded-2xl border border-gray-100 bg-white p-4 shadow-sm">
                    <img :src="qrImageUrl(220)" alt="رمز QR للصفحة" class="mx-auto size-[220px]" loading="lazy">
                </div>
                <p class="truncate text-xs text-gray-500" dir="ltr">{{ pageUrl }}</p>
                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-primary-700"
                    @click="copyLink"
                >
                    <iconify-icon icon="solar:copy-bold" class="text-lg"></iconify-icon>
                    {{ copied ? 'تم النسخ' : 'نسخ الرابط' }}
                </button>
            </div>
        </Modal>

        <Modal title="البيانات الأساسية" size="lg" name="home-step-basic-info">
            <CompletionBasicInfo />
        </Modal>

        <Modal title="بيانات الاتصال" size="lg" name="home-step-contact">
            <CompletionContact />
        </Modal>

        <Modal title="السوشال ميديا" size="lg" name="home-step-social">
            <CompletionSocial />
        </Modal>

        <Modal title="إضافة محتوى" size="2xl" name="home-step-content">
            <CompletionContent />
        </Modal>

        <CompletionContentAddModals />

        <Modal title="توثيق المتجر" size="lg" name="home-step-verification">
            <CompletionVerification />
        </Modal>
    </div>
</template>
