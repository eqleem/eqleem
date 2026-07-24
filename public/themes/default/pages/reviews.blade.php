<x-tenant-theme::pages.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => $sectionTitle]]" />

    <section class="mt-8">
        <div class="grid grid-cols-1 gap-8 sm:grid-cols-2 sm:gap-16">
            <div class="rounded-xl bg-stone-100 p-5">
                <h3 class="text-xl font-bold text-gray-900">{{ $sectionTitle }}</h3>
                <div class="mt-6 flex items-center">
                    <div class="flex items-center space-x-px">
                        @for ($star = 1; $star <= 5; $star++)
                            @if ($averageRating >= $star)
                                <iconify-icon icon="solar:star-bold" class="text-amber-500 text-xl"></iconify-icon>
                            @elseif ($averageRating >= $star - 0.5)
                                <iconify-icon icon="solar:star-half-bold" class="text-amber-500 text-xl"></iconify-icon>
                            @else
                                <iconify-icon icon="solar:star-bold" class="text-stone-300 text-xl"></iconify-icon>
                            @endif
                        @endfor
                    </div>
                    <span class="mr-3 text-sm font-medium text-gray-600">
                        ({{ $totalReviews > 0 ? number_format($averageRating, 1) : '—' }} من 5)
                    </span>
                </div>
                <p class="mt-2.5 text-sm font-medium text-gray-600">
                    @if ($totalReviews > 0)
                        بناءً على {{ number_format($totalReviews) }} تقييم
                    @else
                        لا توجد تقييمات منشورة بعد
                    @endif
                </p>
                <button
                    type="button"
                    wire:click="openAddReview"
                    class="mt-4 inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                >
                    <iconify-icon icon="{{ $hasClientReview ? 'hugeicons:edit-02' : 'hugeicons:comment-add-01' }}" class="text-lg"></iconify-icon>
                    {{ $hasClientReview ? 'تعديل تقييمي' : 'إضافة تقييم' }}
                </button>
            </div>

            <div>
                <ul class="space-y-2.5">
                    @foreach ($distribution as $stars => $count)
                        @php
                            $percent = $totalReviews > 0 ? (int) round(($count / $totalReviews) * 100) : 0;
                        @endphp
                        <li class="grid grid-cols-5 items-center gap-x-4">
                            <span class="whitespace-nowrap text-sm font-medium text-gray-600">
                                {{ $stars }} {{ $stars === 1 ? 'نجمة' : 'نجوم' }}
                            </span>
                            <div class="relative col-span-3 h-1.5 w-full rounded-full bg-gray-200">
                                <div
                                    class="absolute inset-y-0 left-0 rounded-full bg-gray-900"
                                    style="width: {{ $percent }}%"
                                ></div>
                            </div>
                            <span class="whitespace-nowrap text-sm font-medium text-gray-600">{{ number_format($count) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <hr class="mt-10 border-gray-200" />

        <div class="mt-10 flow-root">
            @if ($reviews->isEmpty())
                <div class="rounded-xl bg-stone-100 px-5 py-12 text-center">
                    <iconify-icon icon="solar:star-bold" class="text-4xl text-stone-300"></iconify-icon>
                    <p class="mt-3 text-sm font-medium text-stone-700">لا توجد تقييمات لعرضها حالياً.</p>
                    <p class="mt-1 text-sm text-stone-500">كن أول من يشارك تجربته.</p>
                </div>
            @else
                <ul class="flex flex-col gap-y-5">
                    @foreach ($reviews as $review)
                        <li wire:key="review-{{ $review->id }}" class="grid grid-cols-1 gap-x-8 gap-y-8 rounded-xl bg-stone-100 p-5 py-8 md:grid-cols-7">
                            <div class="md:col-span-2">
                                <div class="flex items-center space-x-px">
                                    @for ($star = 1; $star <= 5; $star++)
                                        <iconify-icon
                                            icon="solar:star-bold"
                                            @class([
                                                'text-xl',
                                                'text-amber-500' => (int) $review->rating >= $star,
                                                'text-stone-300' => (int) $review->rating < $star,
                                            ])
                                        ></iconify-icon>
                                    @endfor
                                </div>
                                <div class="mt-5 flex items-start md:flex-col">
                                    <div class="flex-shrink-0">
                                        <p class="text-sm font-bold text-gray-900">{{ $review->reviewerName() }}</p>
                                        <p class="mt-1 text-sm font-normal text-gray-500">
                                            {{ $review->created_at?->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                    @if ($review->client_id)
                                        <div class="mr-4 flex items-center text-sm font-medium text-gray-600 md:mr-0 md:mt-4">
                                            <iconify-icon icon="solar:verified-check-bold" class="ml-1.5 text-green-500 text-xl"></iconify-icon>
                                            عميل موثق
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="md:col-span-5">
                                @if (filled($review->title))
                                    <p class="text-base font-bold text-gray-900">{{ $review->title }}</p>
                                @endif
                                @if (filled($review->score))
                                    <blockquote @class(['mt-4' => filled($review->title)])>
                                        <p class="text-sm font-normal leading-7 text-gray-900">{{ $review->score }}</p>
                                    </blockquote>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>

                @if ($reviews->hasPages())
                    <div class="mt-8">
                        {{ $reviews->links() }}
                    </div>
                @endif
            @endif
        </div>
    </section>

    <x-tenant-theme::modal name="reviews-login-modal" maxWidth="md">
        <x-slot:title>تسجيل الدخول مطلوب</x-slot:title>

        <div class="space-y-4" dir="rtl">
            <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                الرجاء تسجيل الدخول لإضافة تقييم.
            </p>

            <livewire:tenant.client-login />
        </div>
    </x-tenant-theme::modal>

    <x-tenant-theme::modal name="add-testimonial-modal" maxWidth="xl">
        <x-slot:title>{{ $editingReviewId ? 'تعديل تقييمي' : 'إضافة تقييم جديد' }}</x-slot:title>

        <form wire:submit="submitReview" class="space-y-4" dir="rtl">
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">التقييم</label>
                <div class="flex items-center gap-2 rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-amber-500">
                    @for ($star = 1; $star <= 5; $star++)
                        <button
                            type="button"
                            wire:click="setRating({{ $star }})"
                            class="transition hover:scale-110"
                            aria-label="تقييم {{ $star }} من 5"
                        >
                            <iconify-icon
                                icon="solar:star-bold"
                                @class([
                                    'text-2xl',
                                    'text-amber-500' => $rating >= $star,
                                    'text-stone-300' => $rating < $star,
                                ])
                            ></iconify-icon>
                        </button>
                    @endfor
                </div>
                @error('rating')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="review-title" class="text-sm font-medium text-stone-700">عنوان مختصر</label>
                <input
                    id="review-title"
                    type="text"
                    wire:model="title"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none @error('title') border-red-400 @enderror"
                    placeholder="مثال: سرعة إنجاز وجودة عالية"
                >
                @error('title')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="review-score" class="text-sm font-medium text-stone-700">نص التقييم</label>
                <textarea
                    id="review-score"
                    rows="4"
                    wire:model="score"
                    class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none @error('score') border-red-400 @enderror"
                    placeholder="اكتب تجربتك"
                ></textarea>
                @error('score')
                    <p class="text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:opacity-60"
            >
                <iconify-icon icon="hugeicons:sent" class="text-xl"></iconify-icon>
                <span wire:loading.remove wire:target="submitReview">
                    {{ $editingReviewId ? 'حفظ التعديلات' : 'إرسال التقييم' }}
                </span>
                <span wire:loading wire:target="submitReview">جاري الحفظ...</span>
            </button>
        </form>
    </x-tenant-theme::modal>
</x-tenant-theme::pages.layout>
