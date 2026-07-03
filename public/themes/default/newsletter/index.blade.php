<x-tenant-theme::newsletter.layout>
    <section class="mb-6 rounded-2xl border border-primary-100 bg-primary-50/40 p-4 md:p-6">
        <div class="mb-4">
            <p class="mb-1 text-sm font-semibold text-primary-600">النشرة البريدية</p>
            <h2 class="text-xl font-extrabold text-stone-900 md:text-2xl">اشترك ليصلك جديدنا</h2>
            <p class="mt-2 text-sm text-stone-600 md:text-base">
                محتوى مختصر وعملي يصلك مباشرة إلى بريدك الإلكتروني.
            </p>
        </div>

        <form class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-grow">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <iconify-icon icon="hugeicons:mail-02" class="text-2xl text-stone-400" stroke-width="1.5"></iconify-icon>
                </div>
                <input type="email" placeholder="your@email.com" dir="ltr" class="custom-input w-full rounded-xl border border-primary-100 bg-white py-3 pl-11 pr-4 text-base text-stone-700 placeholder-stone-400 outline-none focus:border-primary-300" required>
            </div>
            <button type="button" class="flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-3 text-base font-medium text-white transition hover:bg-primary-600">
                <span>اشترك الآن</span>
            </button>
        </form>
    </section>

    <section class="mb-6 w-full">
        <div class="flex items-center gap-3" x-data="{ open: false }">
            <div x-show="open" x-transition class="flex-grow">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="ابحث في أعداد النشرة..."
                    class="w-full rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 outline-none focus:border-stone-400"
                >
            </div>

            <button type="button" @click="open = !open" class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition" aria-label="البحث">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
            </button>
        </div>
    </section>

    <section>
        @if ($issues->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد أعداد منشورة حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر أعداد النشرة هنا عند نشرها من لوحة التحكم.</p>
            </div>
        @else
            <div class="space-y-5 md:space-y-6">
                @foreach ($issues as $issue)
                    @php
                        $imageUrl = contentImageUrl(data_get($issue->data, 'image')) ?? $issue->avatar;
                        $subtitle = (string) data_get($issue->data, 'subtitle', '');
                        $displayDate = $issue->newsletterSentAt() ?? $issue->published_at;
                    @endphp

                    <article wire:key="newsletter-{{ $issue->id }}" class="rounded-2xl bg-stone-100/80 p-2 transition hover:bg-stone-200/50 md:p-4">
                        <a href="{{ route('tenant.newsletter.detail', $issue->slug) }}" wire:navigate class="flex items-start gap-4 md:gap-6">
                            <img src="{{ $imageUrl }}" alt="{{ $issue->title }}" class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28">

                            <div class="flex-1">
                                <h3 class="mb-2 text-base font-extrabold leading-tight text-stone-900 md:text-xl">{{ $issue->title }}</h3>

                                @if ($subtitle !== '')
                                    <p class="mb-3 text-sm text-stone-500 md:text-base">{{ $subtitle }}</p>
                                @endif

                                <p class="flex flex-wrap items-center gap-2 text-sm text-stone-400 md:text-base">
                                    <span class="text-base font-extrabold text-orange-600 md:text-lg">{{ tenant('name') }}</span>

                                    @if ($displayDate)
                                        <span>· {{ $displayDate->translatedFormat('j F Y') }}</span>
                                    @endif
                                </p>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::newsletter.layout>
