<x-tenant-theme::pages.layout>
    <section class="mb-6">
        <x-tenant-theme::breadcrumb :links="[['url' => null, 'title' => 'المزايا']]" />
    </section>

    <section class="space-y-6 p-2">
        @foreach ($features as $feature)
            <article wire:key="feature-{{ $feature['slug'] }}" class="rounded-2xl bg-white p-4 md:p-6">
                <div class="grid grid-cols-1 items-center gap-6 md:grid-cols-2">
                    <div
                        @class([
                            'order-1 md:order-1' => $loop->odd,
                            'order-1 md:order-2' => $loop->even,
                        ])
                    >
                        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-stone-50">
                            <img src="{{ $feature['image'] }}" alt="{{ $feature['title'] }}" class="h-full w-full object-cover">
                        </div>
                    </div>

                    <div
                        @class([
                            'order-2 md:order-2' => $loop->odd,
                            'order-2 md:order-1' => $loop->even,
                        ])
                    >
                        <span class="inline-flex rounded-full bg-primary-50 px-3 py-1 text-xs font-bold text-primary-700">{{ $feature['tag'] }}</span>
                        <h2 class="mt-3 text-2xl font-black leading-tight text-stone-900 md:text-4xl">{{ $feature['title'] }}</h2>
                        <p class="mt-4 text-sm leading-8 text-stone-600 md:text-base">{{ $feature['description'] }}</p>

                        <ul class="mt-4 space-y-2">
                            @foreach ($feature['points'] as $point)
                                <li class="flex items-center gap-2 text-sm text-stone-700">
                                    <iconify-icon icon="solar:check-circle-bold" class="text-lg text-primary-600"></iconify-icon>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6 border-t border-stone-200 pt-4">
                            <p class="text-sm italic leading-7 text-stone-600">"{{ $feature['quote'] }}"</p>
                            <p class="mt-2 text-sm font-bold text-stone-900">{{ $feature['author'] }}</p>
                            <p class="text-xs text-stone-500">{{ $feature['role'] }}</p>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
</x-tenant-theme::pages.layout>