<script setup>
import { onMounted, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import Badge from '../ui/Badge.vue';
import Icon from '../ui/Icon.vue';
import { useReviewsStore } from '../../stores/reviews.js';

const reviewsStore = useReviewsStore();
const { items, meta, search, loading, loaded, error, isEmpty, hasPages } = storeToRefs(reviewsStore);

const searchInput = ref('');
let searchTimer = null;

watch(searchInput, (value) => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => reviewsStore.setSearch(value), 300);
});

function goToPage(page) {
    if (page < 1 || page > meta.value.last_page || page === meta.value.current_page) {
        return;
    }

    reviewsStore.goToPage(page);
}

onMounted(() => {
    searchInput.value = search.value;

    if (!loaded.value) {
        reviewsStore.fetchReviews();
    }
});
</script>

<template>
    <div class="divide-y divide-dotted divide-stone-200">
        <div class="flex w-full items-center bg-white p-3">
            <div class="min-w-0 flex-grow rounded-lg bg-stone-100">
                <div class="relative text-sm text-stone-800">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center ps-2 text-stone-500">
                        <svg class="h-5 w-5 text-stone-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="m20 20-3-3" />
                        </svg>
                    </div>
                    <input
                        v-model="searchInput"
                        type="text"
                        placeholder="ابحث في التقييمات .."
                        class="block w-full rounded-lg border border-transparent py-1.5 ps-10 text-stone-800 ring-inset ring-stone-200 placeholder:text-stone-400 focus:border-primary-500 focus:outline-none sm:text-sm sm:leading-6"
                    >
                </div>
            </div>
        </div>

        <div class="relative p-1">
            <div v-if="loading" class="animate-pulse divide-y-2 divide-dotted divide-stone-200/50" aria-busy="true">
                <div v-for="n in 5" :key="n" class="space-y-2 px-5 py-4">
                    <div class="h-5 w-1/3 rounded bg-stone-200"></div>
                    <div class="h-4 w-2/3 rounded bg-stone-100"></div>
                </div>
            </div>

            <div v-else-if="error" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <p class="text-sm text-red-600">{{ error }}</p>
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm" @click="reviewsStore.fetchReviews({ page: meta.current_page })">
                    إعادة المحاولة
                </button>
            </div>

            <div v-else-if="isEmpty" class="flex flex-col items-center justify-center gap-2 p-10 text-center">
                <Icon name="message" class="h-12 w-12 text-stone-400" />
                <p class="text-stone-700">لا توجد تقييمات.</p>
                <small class="text-stone-500">ستظهر تقييمات العملاء هنا عند استلامها.</small>
            </div>

            <div v-else class="divide-y-2 divide-dotted divide-stone-200/50">
                <article v-for="review in items" :key="review.id" class="px-4 py-4 transition-colors hover:bg-stone-50 sm:px-6">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="truncate font-semibold text-stone-800">{{ review.title || `تقييم #${review.id}` }}</h2>
                                <Badge :color="review.published ? 'green' : 'gray'">
                                    {{ review.published ? 'منشور' : 'غير منشور' }}
                                </Badge>
                                <Badge v-if="review.reviewer.registered" color="blue">عميل مسجل</Badge>
                            </div>
                            <p v-if="review.score" class="mt-2 line-clamp-2 text-sm leading-6 text-stone-600">{{ review.score }}</p>
                            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-stone-500">
                                <span class="font-medium text-stone-700">{{ review.reviewer.name }}</span>
                                <span v-if="review.reviewer.email">{{ review.reviewer.email }}</span>
                                <span v-if="review.reviewer.phone" dir="ltr">{{ review.reviewer.phone }}</span>
                                <span v-if="review.content">{{ review.content.title }}</span>
                                <span v-if="review.order?.number">طلب #{{ review.order.number }}</span>
                            </div>
                        </div>

                        <div class="shrink-0 text-end">
                            <div v-if="review.rating" class="flex items-center justify-end gap-1 text-amber-500">
                                <iconify-icon icon="solar:star-bold" class="text-lg"></iconify-icon>
                                <span class="font-semibold">{{ review.rating }}/5</span>
                            </div>
                            <div class="mt-1 text-xs text-stone-400">{{ review.created }}</div>
                        </div>
                    </div>
                </article>
            </div>
        </div>

        <div v-if="hasPages" class="flex items-center justify-between rounded-b-2xl bg-stone-50 p-4 px-6">
            <div class="text-sm text-stone-500">النتائج: <b>{{ meta.total.toLocaleString('ar-SA') }}</b></div>
            <div class="flex items-center gap-2">
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm disabled:opacity-40" :disabled="meta.current_page <= 1 || loading" @click="goToPage(meta.current_page - 1)">السابق</button>
                <span class="text-sm text-stone-500">{{ meta.current_page }} / {{ meta.last_page }}</span>
                <button type="button" class="rounded-lg border bg-white px-3 py-1.5 text-sm disabled:opacity-40" :disabled="meta.current_page >= meta.last_page || loading" @click="goToPage(meta.current_page + 1)">التالي</button>
            </div>
        </div>
    </div>
</template>
