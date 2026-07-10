<script setup>
import { ref, computed } from 'vue';

// Port of resources/views/admin/home/⚡welcome-widget.blade.php — dummy data.
const userName = 'أحمد الأحمدي';
const pageUrl = 'https://eqleem.com/my-store';
const shareText = 'شاهد صفحة متجري';

const completedSteps = 2;
const totalSteps = 4;
const percentage = Math.round((completedSteps / totalSteps) * 100);
const nextStep = { label: 'أضف بيانات الاتصال', modal: 'home-step-contact' };

const greeting = computed(() => {
    const hour = new Date().getHours();
    return hour < 12 ? 'صباح الخير' : 'مساء الخير';
});

const qrImageUrl = (size = 220) =>
    `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodeURIComponent(pageUrl)}`;

const copied = ref(false);
const shareInput = ref(null);
const shareOpen = ref(false);
const qrOpen = ref(false);

async function copyLink() {
    try {
        await navigator.clipboard.writeText(pageUrl);
    } catch {
        shareInput.value?.select();
        document.execCommand('copy');
    }
    copied.value = true;
    setTimeout(() => (copied.value = false), 2500);
}

function shareLink(platform) {
    const url = encodeURIComponent(pageUrl);
    const text = encodeURIComponent(shareText);

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
            return pageUrl;
    }
}

const socials = [
    { platform: 'whatsapp', label: 'واتساب' },
    { platform: 'telegram', label: 'تيلجرام' },
    { platform: 'x', label: 'X' },
    { platform: 'facebook', label: 'فيسبوك' },
];
</script>

<template>
    <div class="mb-6 overflow-hidden rounded-2xl bg-primary-700 text-white">
        <div class="grid gap-0 lg:grid-cols-[1fr_auto]">
            <div class="p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-medium text-primary-100">{{ greeting }}</p>
                    <button
                        type="button"
                        class="inline-flex shrink-0 items-center gap-1.5 rounded-xl bg-white px-3 py-1.5 text-xs font-semibold text-primary-700 transition hover:bg-primary-50 sm:gap-2 sm:px-4 sm:py-2 sm:text-sm"
                    >
                        <span class="text-base leading-none">＋</span>
                        إضافة محتوى
                    </button>
                </div>
                <h2 class="mt-1 text-xl font-bold sm:text-2xl">مرحباً، {{ userName }} 👋</h2>

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
                    >
                        <span class="leading-none">←</span>
                        {{ nextStep.label }}
                    </button>
                </template>
                <p v-else class="mt-2 text-sm text-primary-100/90">صفحتك جاهزة — شاركها مع عملائك.</p>
            </div>

            <div class="border-t border-white/10 bg-black/10 p-5 sm:p-6 lg:w-96 lg:border-s lg:border-t-0">
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
                        :href="pageUrl"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                        title="معاينة الصفحة"
                    >
                        <span class="text-lg leading-none">👁</span>
                        معاينة
                    </a>

                    <button
                        type="button"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                        title="نسخ الرابط"
                        @click="copyLink"
                    >
                        <span class="text-lg leading-none">{{ copied ? '✓' : '⧉' }}</span>
                        {{ copied ? 'تم النسخ' : 'نسخ' }}
                    </button>

                    <button
                        type="button"
                        class="flex flex-col items-center justify-center gap-1 rounded-xl bg-white/10 p-2.5 text-center text-[11px] font-medium transition hover:bg-white/20"
                        title="مشاركة الصفحة"
                        @click="shareOpen = true"
                    >
                        <span class="text-lg leading-none">↗</span>
                        مشاركة
                    </button>

                    <button
                        type="button"
                        class="flex items-center justify-center rounded-xl bg-white p-1 ring-1 ring-white/20 transition hover:bg-white/90"
                        title="رمز QR — اضغط للتكبير"
                        @click="qrOpen = true"
                    >
                        <img :src="qrImageUrl(120)" alt="رمز QR للصفحة" class="size-16 rounded-md" loading="lazy">
                    </button>
                </div>
            </div>
        </div>

        <!-- Share modal -->
        <div
            v-if="shareOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="shareOpen = false"
        >
            <div class="w-full max-w-lg rounded-2xl bg-white p-4 text-gray-800" dir="rtl">
                <div class="mb-3 flex items-center justify-between">
                    <h3 class="font-semibold">مشاركة الصفحة</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-700" @click="shareOpen = false">✕</button>
                </div>
                <p class="text-sm text-gray-600">انسخ الرابط أو شاركه مباشرة عبر المنصات.</p>

                <div class="mt-4 flex items-center gap-2">
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
                        {{ copied ? 'تم النسخ ✓' : 'نسخ' }}
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 sm:grid-cols-4">
                    <a
                        v-for="social in socials"
                        :key="social.platform"
                        :href="shareLink(social.platform)"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                    >
                        {{ social.label }}
                    </a>
                </div>
            </div>
        </div>

        <!-- QR modal -->
        <div
            v-if="qrOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
            @click.self="qrOpen = false"
        >
            <div class="w-full max-w-sm space-y-4 rounded-2xl bg-white p-4 text-center text-gray-800" dir="rtl">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold">رمز QR للصفحة</h3>
                    <button type="button" class="text-gray-400 hover:text-gray-700" @click="qrOpen = false">✕</button>
                </div>
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
                    {{ copied ? 'تم النسخ ✓' : 'نسخ الرابط' }}
                </button>
            </div>
        </div>
    </div>
</template>
