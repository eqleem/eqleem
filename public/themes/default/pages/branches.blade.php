<x-tenant-theme::pages.layout>
 
    <section class="mb-6">
        <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => 'فروعنا']]" />
        <x-tenant-theme::page-title title="فروعنا" desc="اختر أقرب فرع لك وزره مباشرة" />
    </section>


    <section class="p-2 space-y-5">
        @foreach ($branches as $branch)
            <article class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <div class="h-64 lg:h-full">
                        <iframe
                            src="{{ $branch['map_embed_url'] }}"
                            class="h-full w-full border-0"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen
                            title="{{ $branch['name'] }}"
                        ></iframe>
                    </div>

                    <div class="space-y-4 p-5">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-xl font-bold text-stone-900">{{ $branch['name'] }}</h2>
                                <p class="mt-1 text-sm text-stone-600">{{ $branch['address'] }}</p>
                            </div>
                            <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-base"></iconify-icon>
                                مفتوح اليوم
                            </span>
                        </div>

                        <div class="rounded-xl bg-stone-50 p-4">
                            <p class="mb-2 text-xs font-semibold text-stone-500">أوقات العمل</p>
                            <ul class="space-y-1.5 text-sm text-stone-700">
                                @foreach ($branch['working_hours'] as $workingHour)
                                    <li wire:key="working-hour-{{ $branch['slug'] }}-{{ $loop->index }}" class="flex items-center justify-between gap-3">
                                        <span>{{ $workingHour['days'] }}</span>
                                        <span class="font-semibold">{{ $workingHour['time'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach ($branch['phones'] as $phone)
                                <a
                                    wire:key="branch-phone-{{ $branch['slug'] }}-{{ $loop->index }}"
                                    href="tel:{{ $phone['tel'] }}"
                                    class="inline-flex items-center justify-between rounded-xl border border-stone-200 bg-white px-4 py-3 text-sm font-medium text-stone-700 hover:border-primary-300 hover:bg-primary-50/30"
                                >
                                    <span>{{ $phone['label'] }}</span>
                                    <span dir="ltr" class="font-semibold">{{ $phone['display'] }}</span>
                                </a>
                            @endforeach
                        </div>

                        <a
                            href="{{ $branch['google_map_url'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
                        >
                            <iconify-icon icon="hugeicons:maps-square-02" class="text-xl"></iconify-icon>
                            التوجه إلى الموقع عبر Google Maps
                        </a>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
</x-tenant-theme::pages.layout>