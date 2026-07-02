<x-tenant-theme::properties-rental.layout>
    <section class="mb-5 flex w-full items-center justify-between gap-3 px-1">
        <div class="no-scrollbar flex w-full items-center gap-3 overflow-x-auto whitespace-nowrap rounded-2xl bg-stone-200/40 p-1">
            <button class="rounded-xl bg-white p-3 py-2.5 text-center text-sm font-medium text-stone-900 shadow-sm">الكل</button>
            @foreach ($categories as $category)
                <button class="rounded-xl p-3 py-2.5 text-center text-sm font-medium text-stone-600 hover:bg-stone-50 hover:text-stone-900">
                    {{ $category }}
                </button>
            @endforeach
        </div>
    </section>

    <section class="p-1">
        <div class="grid grid-cols-2 gap-4 md:gap-6 xl:grid-cols-3">
            @foreach ($properties as $property)
                <a
                    href="{{ route('tenant.properties-rental.detail', $property['slug']) }}"
                    wire:navigate
                    class="group overflow-hidden rounded-xl bg-white transition md:rounded-2xl"
                >
                    <div class="relative">
                        <img src="{{ $property['image'] }}" alt="{{ $property['name'] }}" class="h-56 w-full object-cover transition-all duration-500 group-hover:scale-105 md:h-72">
                        <button aria-label="Favorite" class="absolute end-2 top-2 rounded-full bg-black/30 p-2 text-white backdrop-blur transition hover:text-rose-500">
                            <iconify-icon icon="solar:heart-linear" class="text-lg"></iconify-icon>
                        </button>
                        @if ($property['discount'] > 0)
                            <span class="absolute bottom-2 right-2 rounded-full bg-rose-500 px-2 py-1 text-[11px] font-bold text-white">
                                خصم {{ $property['discount'] }}%
                            </span>
                        @endif
                    </div>

                    <div class="rounded-b-xl border border-neutral-200 border-t-0 p-3 md:rounded-b-2xl">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="truncate text-base font-semibold tracking-tight md:text-lg">{{ $property['name'] }}</h3>
                            <div class="inline-flex items-center gap-1 rounded-md bg-stone-100 px-2 py-1 text-xs font-bold text-stone-700">
                                <iconify-icon icon="solar:star-bold" class="text-amber-500"></iconify-icon>
                                <span>{{ $property['rating'] }}</span>
                            </div>
                        </div>

                        <p class="mt-1 truncate text-xs text-neutral-600">{{ $property['location'] }}</p>

                        <div class="mt-3 flex items-center justify-between text-xs text-stone-600">
                            <span class="inline-flex items-center gap-1">
                                <iconify-icon icon="solar:bed-bold" class="text-base text-primary-600"></iconify-icon>
                                {{ $property['beds'] }} سرير
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <iconify-icon icon="solar:bath-bold" class="text-base text-primary-600"></iconify-icon>
                                {{ $property['baths'] }} حمام
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <iconify-icon icon="solar:home-bold" class="text-base text-primary-600"></iconify-icon>
                                {{ $property['area'] }} م²
                            </span>
                        </div>

                        <div class="mt-4 flex items-end justify-between">
                            <div>
                                <p class="text-[11px] text-stone-500">السعر / ليلة</p>
                                <p class="text-lg font-black text-stone-900">{{ number_format($property['price_per_night']) }} ر.س</p>
                            </div>
                            <span class="inline-flex items-center rounded-[9px] bg-primary-50 px-3 py-1.5 text-sm font-semibold text-primary-600">
                                التفاصيل والحجز
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</x-tenant-theme::properties-rental.layout>