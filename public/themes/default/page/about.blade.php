<x-tenant-theme::module-layout>
    <section class="mb-6">
        <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => $page->title]]" />
    </section>

    {{-- Hero + stats --}}
    <section class="overflow-hidden rounded-[1.75rem] bg-stone-50/90 ring-1 ring-stone-200/60">
        <div class="grid items-center gap-8 px-5 py-8 sm:px-8 sm:py-10 lg:grid-cols-12 lg:gap-10 lg:px-10 lg:py-12">
            <div class="lg:col-span-6 xl:col-span-5">
                <h1 class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl lg:text-[2rem] lg:leading-snug">
                    {{ $page->title }}
                </h1>

                @if ($subtitle !== '')
                    <p class="mt-3 max-w-lg text-sm leading-7 text-stone-500 sm:text-[0.95rem] sm:leading-7">
                        {{ $subtitle }}
                    </p>
                @endif

                @if ($primaryButton)
                    <div class="mt-7 flex flex-wrap items-center gap-3">
                        @if ($primaryButton['isForm'] || $primaryButton['isBooking'])
                            @php
                                $modalName = $primaryButton['isBooking']
                                    ? 'about-cta-booking-'.$primaryButton['id']
                                    : 'about-cta-form-'.$primaryButton['id'];
                            @endphp
                            <button
                                type="button"
                                x-on:click="$dispatch('open-modal', { name: '{{ $modalName }}' })"
                                class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                            >
                                {{ $primaryButton['label'] }}
                                <iconify-icon icon="hugeicons:arrow-left-02" class="text-base"></iconify-icon>
                            </button>
                        @elseif (filled($primaryButton['url']))
                            <a
                                href="{{ $primaryButton['url'] }}"
                                @if ($primaryButton['opensInNewTab']) target="_blank" rel="noopener noreferrer" @else wire:navigate @endif
                                class="inline-flex items-center gap-2 rounded-full bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700"
                            >
                                {{ $primaryButton['label'] }}
                                <iconify-icon icon="hugeicons:arrow-left-02" class="text-base"></iconify-icon>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="lg:col-span-6 xl:col-span-7">
                @if ($heroImageUrl)
                    <div class="overflow-hidden rounded-[1.5rem] bg-stone-200/70">
                        <img
                            src="{{ $heroImageUrl }}"
                            alt="{{ $page->title }}"
                            class="aspect-[4/5] w-full object-cover sm:aspect-[5/6] lg:aspect-[4/5] lg:max-h-[28rem]"
                        >
                    </div>
                @else
                    <div class="flex aspect-[4/5] w-full items-center justify-center rounded-[1.5rem] bg-stone-200/60 text-stone-400 sm:aspect-[5/6] lg:aspect-[4/5] lg:max-h-[28rem]">
                        <iconify-icon icon="hugeicons:image-02" class="text-4xl"></iconify-icon>
                    </div>
                @endif
            </div>
        </div>

        @if (count($stats) > 0)
            <div class="border-t border-stone-200/80 px-5 py-7 sm:px-8 lg:px-10">
                <div @class([
                    'grid gap-6 sm:gap-8',
                    'grid-cols-1' => count($stats) === 1,
                    'grid-cols-1 sm:grid-cols-2' => count($stats) === 2,
                    'grid-cols-1 sm:grid-cols-3' => count($stats) === 3,
                    'grid-cols-2 lg:grid-cols-4' => count($stats) >= 4,
                ])>
                    @foreach ($stats as $stat)
                        <div wire:key="about-stat-{{ $stat['id'] !== '' ? $stat['id'] : $loop->index }}" class="min-w-0">
                            <p class="text-2xl font-semibold tracking-tight text-stone-900 sm:text-3xl">{{ $stat['value'] }}</p>
                            @if ($stat['label'] !== '')
                                <p class="mt-1.5 max-w-[12rem] text-xs leading-5 text-stone-500 sm:text-sm sm:leading-6">{{ $stat['label'] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    {{-- Features --}}
    @if (count($features) > 0 || $featuresTitle !== '' || $featuresDescription !== '')
        <section class="mt-12 sm:mt-14">
            @if ($featuresTitle !== '' || $featuresDescription !== '')
                <div class="max-w-2xl">
                    @if ($featuresTitle !== '')
                        <h2 class="text-xl font-semibold tracking-tight text-stone-900 sm:text-2xl lg:text-[1.65rem] lg:leading-snug">
                            {{ $featuresTitle }}
                        </h2>
                    @endif
                    @if ($featuresDescription !== '')
                        <p class="mt-2.5 text-sm leading-7 text-stone-500 sm:text-[0.95rem]">
                            {{ $featuresDescription }}
                        </p>
                    @endif
                </div>
            @endif

            @if (count($features) > 0)
                <div @class([
                    'mt-8 grid gap-4 sm:gap-5',
                    'grid-cols-1' => count($features) === 1,
                    'grid-cols-1 sm:grid-cols-2' => count($features) === 2,
                    'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' => count($features) >= 3,
                ])>
                    @foreach ($features as $feature)
                        @php
                            $brandMark = $feature['brand_mark'] ?? null;
                            $accentColors = [
                                'bg-rose-50 text-rose-500',
                                'bg-orange-50 text-orange-500',
                                'bg-stone-100 text-stone-600',
                                'bg-sky-50 text-sky-600',
                                'bg-emerald-50 text-emerald-600',
                            ];
                            $accent = $accentColors[$loop->index % count($accentColors)];
                        @endphp
                        <article
                            wire:key="about-feature-{{ $feature['id'] !== '' ? $feature['id'] : $loop->index }}"
                            class="flex h-full flex-col rounded-2xl border border-stone-200/80 bg-white p-5 shadow-[0_1px_2px_rgba(0,0,0,0.03)] sm:p-6"
                        >
                            <div @class(['mb-4 flex size-10 items-center justify-center rounded-xl', $accent])>
                                @if (is_array($brandMark) && filled($brandMark['type'] ?? null))
                                    <x-brand-mark
                                        :mark="$brandMark"
                                        :alt="$feature['title']"
                                        class="size-6 object-cover"
                                        icon-size="1.25rem"
                                    />
                                @else
                                    <iconify-icon icon="hugeicons:checkmark-circle-02" class="text-lg"></iconify-icon>
                                @endif
                            </div>

                            <h3 class="text-base font-semibold text-stone-900">{{ $feature['title'] }}</h3>

                            @if ($feature['description'] !== '')
                                <p class="mt-2 flex-1 text-sm leading-6 text-stone-500">{{ $feature['description'] }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    @endif

    @if ($primaryButton)
        @if ($primaryButton['isForm'] && filled($primaryButton['formContentId']))
            <x-tenant-theme::modal wire:key="about-cta-form-modal" name="about-cta-form-{{ $primaryButton['id'] }}" maxWidth="md">
                <x-slot:title>{{ $primaryButton['label'] }}</x-slot:title>

                <livewire:tenant.forms.submit
                    :form-content-id="$primaryButton['formContentId']"
                    :description="$primaryButton['formDescription']"
                    :fields="$primaryButton['formFields']"
                    :key="'about-cta-form-submit-'.$page->id"
                />

                <x-slot:footer>
                    <button type="button" x-on:click="$dispatch('close-modal', { name: 'about-cta-form-{{ $primaryButton['id'] }}' })" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-50">
                        إغلاق
                    </button>
                </x-slot:footer>
            </x-tenant-theme::modal>
        @endif

        @if ($primaryButton['isBooking'])
            <x-tenant-theme::modal wire:key="about-cta-booking-modal" name="about-cta-booking-{{ $primaryButton['id'] }}" maxWidth="md">
                <x-slot:title>{{ $primaryButton['label'] }}</x-slot:title>

                <livewire:tenant.bookings.cta-submit
                    :link-id="0"
                    :branch-ids="$primaryButton['bookingBranchIds']"
                    :calendar-ids="$primaryButton['bookingCalendarIds']"
                    :allow-client-choice="$primaryButton['bookingAllowClientChoice']"
                    :duration-minutes="$primaryButton['bookingDurationMinutes']"
                    :key="'about-cta-booking-submit-'.$page->id"
                />

                <x-slot:footer>
                    <button type="button" x-on:click="$dispatch('close-modal', { name: 'about-cta-booking-{{ $primaryButton['id'] }}' })" class="rounded-xl border border-stone-200 px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-stone-50">
                        إغلاق
                    </button>
                </x-slot:footer>
            </x-tenant-theme::modal>
        @endif
    @endif
</x-tenant-theme::module-layout>
