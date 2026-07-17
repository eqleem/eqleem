<x-tenant-theme::properties.layout>
    <section class="flex gap-3 px-1  flex-row  items-stretch">
        <div class="no-scrollbar flex min-w-0 flex-1 items-center gap-2 overflow-x-auto whitespace-nowrap rounded-2xl bg-stone-200/40 p-1">
            @foreach ($propertyTypes as $type)
                <button @class([
                    'rounded-xl px-3 py-2.5 text-center text-sm font-medium transition',
                    'bg-white text-stone-900 shadow-sm' => $loop->first,
                    'text-stone-600 hover:bg-stone-50 hover:text-stone-900' => ! $loop->first,
                ])>
                    {{ $type }}
                </button>
            @endforeach
        </div>

        <div class="no-scrollbar flex shrink-0 items-center gap-2 overflow-x-auto whitespace-nowrap rounded-2xl bg-stone-200/40 p-1 lg:w-auto">
            @foreach ($listingFilters as $filter)
                <button @class([
                    'rounded-xl px-4 py-2.5 text-center text-sm font-medium transition',
                    'bg-white text-stone-900 shadow-sm' => $loop->first,
                    'text-stone-600 hover:bg-stone-50 hover:text-stone-900' => ! $loop->first,
                ])>
                    {{ $filter }}
                </button>
            @endforeach
        </div>
    </section>

    <section class="p-1 mt-5">
        <div class="grid grid-cols-2 gap-4 md:gap-6 xl:grid-cols-3">
            @foreach ($properties as $property)
                <a
                    href="{{ route('tenant.properties.detail', $property['slug']) }}"
                    wire:navigate
                    class="group overflow-hidden rounded-xl bg-white transition md:rounded-2xl"
                >
                    <div class="relative">
                        <img src="{{ $property['image'] }}" alt="{{ $property['name'] }}" class="h-56 w-full object-cover transition-all duration-500 group-hover:scale-105 md:h-72">
                        <span @class([
                            'absolute start-2 top-2 rounded-full px-2.5 py-1 text-[11px] font-bold text-white',
                            'bg-primary-600' => $property['listing_type'] === 'for-rent',
                            'bg-amber-600' => $property['listing_type'] === 'for-sale',
                        ])>
                            {{ $property['listing_type'] === 'for-rent' ? 'للإيجار' : 'للبيع' }}
                        </span>
                    </div>

                    <div class="rounded-b-xl border border-neutral-200 border-t-0 p-3 md:rounded-b-2xl">
                        <h3 class="truncate text-base font-semibold tracking-tight md:text-lg">{{ $property['name'] }}</h3>
                        <p class="mt-1 truncate text-xs text-neutral-600">{{ $property['location'] }}</p>

                        <div class="mt-3 flex items-center justify-between text-xs text-stone-600">
                            <span class="inline-flex items-center gap-1">
                                <iconify-icon icon="solar:bed-bold" class="text-base text-primary-600"></iconify-icon>
                                {{ $property['beds'] }} غرف
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <iconify-icon icon="solar:bath-bold" class="text-base text-primary-600"></iconify-icon>
                                {{ $property['baths'] }} حمام
                            </span>
                            <span class="inline-flex items-center gap-1">
                                <iconify-icon icon="solar:ruler-angular-bold" class="text-base text-primary-600"></iconify-icon>
                                {{ $property['area'] }} م²
                            </span>
                        </div>

                        <div class="mt-4 flex items-end justify-between gap-2">
                            <div class="min-w-0">
                                <p class="truncate text-lg font-black text-stone-900">{{ $this->priceLabel($property) }}</p>
                                <p class="mt-0.5 text-[11px] text-stone-500">{{ $property['property_type'] }}</p>
                            </div>
                            <span class="inline-flex shrink-0 items-center rounded-[9px] bg-primary-50 px-3 py-1.5 text-sm font-semibold text-primary-600">
                                التفاصيل
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </section>
</x-tenant-theme::properties.layout>