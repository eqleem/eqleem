<script setup>
import { computed } from 'vue';
import { cssCoverBackground, isCssCover } from '../../data/coverPresets.js';
import { appDomain } from '../../data/settings.js';

const props = defineProps({
    name: { type: String, default: '' },
    bio: { type: String, default: '' },
    handle: { type: String, default: '' },
    pageUrl: { type: String, default: '' },
    brandMark: { type: Object, default: null },
    logoUrl: { type: String, default: '' },
    headerImage: { type: String, default: '' },
    headerImageUrl: { type: String, default: '' },
    headerImagePosition: { type: [Number, String], default: 50 },
    primaryColor: { type: String, default: 'blue' },
    primaryActionLabel: { type: String, default: '' },
    secondaryActionLabel: { type: String, default: '' },
    socialLinks: { type: Array, default: () => [] },
    showCover: { type: Boolean, default: true },
    compactCover: { type: Boolean, default: false },
});

const networkIcons = {
    twitter: 'ri:twitter-x-fill',
    instagram: 'ri:instagram-fill',
    snapchat: 'ri:snapchat-fill',
    youtube: 'ri:youtube-fill',
    facebook: 'ri:facebook-fill',
    tiktok: 'ri:tiktok-fill',
    linkedin: 'ri:linkedin-fill',
    whatsapp: 'ri:whatsapp-fill',
    telegram: 'ri:telegram-fill',
    website: 'ri:global-line',
};

const namedColorClass = {
    blue: 'bg-blue-700',
    cyan: 'bg-cyan-700',
    green: 'bg-green-700',
    teal: 'bg-teal-700',
    sky: 'bg-sky-700',
    purple: 'bg-purple-700',
    violet: 'bg-violet-700',
    indigo: 'bg-indigo-700',
    red: 'bg-red-700',
    pink: 'bg-pink-700',
    fuchsia: 'bg-fuchsia-700',
    orange: 'bg-orange-700',
    amber: 'bg-amber-600',
    yellow: 'bg-yellow-500',
    zinc: 'bg-zinc-700',
    emerald: 'bg-emerald-700',
    lime: 'bg-lime-600',
    rose: 'bg-rose-700',
    gray: 'bg-stone-700',
};

const displayUrl = computed(() => {
    if (props.pageUrl) {
        return props.pageUrl.replace(/^https?:\/\//, '');
    }

    const handle = props.handle || 'your-page';

    return `${appDomain}/${handle}`;
});

const coverStyle = computed(() => {
    const value = props.headerImage || props.headerImageUrl || '';

    if (isCssCover(value)) {
        return { background: cssCoverBackground(value) };
    }

    const imageUrl = props.headerImageUrl
        || (value && !value.startsWith('color:') && !value.startsWith('gradient:') ? value : '');

    if (imageUrl && (imageUrl.startsWith('http') || imageUrl.startsWith('/') || imageUrl.startsWith('blob:'))) {
        return {
            backgroundImage: `url(${imageUrl})`,
            backgroundSize: 'cover',
            backgroundPosition: `center ${props.headerImagePosition}%`,
        };
    }

    return {
        background: 'radial-gradient(ellipse at bottom right, #4f46e5, #3730a3, #1e1b4b)',
    };
});

const primaryButtonClass = computed(() => {
    if (typeof props.primaryColor === 'string' && props.primaryColor.startsWith('#')) {
        return '';
    }

    return namedColorClass[props.primaryColor] || 'bg-indigo-700';
});

const primaryButtonStyle = computed(() => {
    if (typeof props.primaryColor === 'string' && props.primaryColor.startsWith('#')) {
        return { backgroundColor: props.primaryColor };
    }

    return {};
});

const mark = computed(() => props.brandMark || null);

const showImage = computed(() => mark.value?.type === 'image' && (mark.value?.url || mark.value?.file || props.logoUrl));

const imageSrc = computed(() => {
    if (mark.value?.file instanceof File) {
        return URL.createObjectURL(mark.value.file);
    }

    return mark.value?.url || props.logoUrl || '';
});

const previewSocials = computed(() => {
    const links = Array.isArray(props.socialLinks) ? props.socialLinks : [];

    if (links.length > 0) {
        return links.slice(0, 4).map((link) => ({
            network: link.network,
            icon: networkIcons[link.network] || 'ri:link',
        }));
    }

    return [
        { network: 'tiktok', icon: networkIcons.tiktok },
        { network: 'snapchat', icon: networkIcons.snapchat },
        { network: 'facebook', icon: networkIcons.facebook },
        { network: 'twitter', icon: networkIcons.twitter },
    ];
});
</script>

<template>
    <div class="w-full" dir="rtl">
        <div class="w-full overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-lg shadow-stone-200/60">
            <div class="flex items-center gap-2 border-b border-stone-200/80 bg-stone-200/70 px-3 py-2">
                <div class="flex items-center gap-1">
                    <span class="size-2 rounded-full bg-red-400"></span>
                    <span class="size-2 rounded-full bg-amber-400"></span>
                    <span class="size-2 rounded-full bg-emerald-400"></span>
                </div>
                <div
                    class="min-w-0 flex-1 truncate rounded-md bg-white px-2.5 py-1 text-center text-[11px] text-stone-500"
                    dir="ltr"
                >
                    {{ displayUrl }}
                </div>
            </div>

            <div class="relative bg-white">
                <div
                    v-if="showCover"
                    class="relative w-full overflow-hidden"
                    :class="compactCover ? 'h-6' : 'h-40 sm:h-44'"
                    :style="coverStyle"
                >
                    <div
                        v-if="!compactCover"
                        class="absolute start-2 top-2 z-10 flex items-center gap-1.5"
                    >
                        <span
                            v-for="link in previewSocials"
                            :key="link.network"
                            class="inline-flex size-8 items-center justify-center rounded-xl bg-black/40 text-white/90"
                        >
                            <iconify-icon :icon="link.icon" class="text-lg"></iconify-icon>
                        </span>
                    </div>
                </div>

                <div class="px-3 pt-3 sm:px-4">
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-full bg-stone-100 text-2xl"
                        >
                            <img
                                v-if="showImage && imageSrc"
                                :src="imageSrc"
                                alt=""
                                class="size-full object-cover"
                            >
                            <iconify-icon
                                v-else-if="mark?.type === 'icon' && mark?.value"
                                :icon="mark.value"
                                class="text-3xl"
                                :style="mark.color ? { color: mark.color } : undefined"
                            ></iconify-icon>
                            <span v-else-if="mark?.type === 'emoji' && mark?.value">{{ mark.value }}</span>
                            <iconify-icon v-else icon="hugeicons:user-circle" class="text-3xl text-stone-300"></iconify-icon>
                        </div>

                        <div class="min-w-0 flex-1">
                            <p class="flex items-center gap-1 truncate text-base font-bold text-stone-900">
                                <span class="truncate">{{ name || 'اسم نشاطك' }}</span>
                                <iconify-icon icon="solar:verified-check-bold" class="shrink-0 text-xl text-blue-800"></iconify-icon>
                            </p>
                            <p class="mt-0.5 line-clamp-2 text-xs leading-relaxed text-stone-500">
                                {{ bio || 'نبذة قصيرة عن نشاطك تظهر هنا…' }}
                            </p>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <div
                            class="flex items-center justify-center gap-1.5 rounded-xl px-2 py-2.5 text-center text-[11px] font-semibold text-white shadow-sm sm:text-xs"
                            :class="primaryButtonClass"
                            :style="primaryButtonStyle"
                        >
                            <iconify-icon icon="hugeicons:cursor-magic-selection-02" class="shrink-0 text-sm"></iconify-icon>
                            <span class="truncate">{{ primaryActionLabel || 'الزر الرئيسي' }}</span>
                        </div>
                        <div
                            class="flex items-center justify-center gap-1.5 rounded-xl bg-stone-100 px-2 py-2.5 text-center text-[11px] font-semibold text-stone-700 sm:text-xs"
                        >
                            <span class="truncate">{{ secondaryActionLabel || 'الزر الثانوي' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Empty space below CTAs so the fade never covers the action buttons -->
                <div v-if="showCover && !compactCover" class="relative h-20 sm:h-24">
                    <div
                        class="pointer-events-none absolute inset-x-0 bottom-0 h-full bg-gradient-to-t from-white via-white/85 to-transparent"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>
