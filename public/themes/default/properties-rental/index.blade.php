<x-tenant-theme::properties-rental.layout>
    <section class="mb-5 flex w-full items-center justify-between gap-3 px-1">
        <div class="no-scrollbar flex w-full items-center gap-3 overflow-x-auto whitespace-nowrap rounded-2xl bg-stone-200/40 p-1">
            <a
                href="{{ route('tenant.properties-rental.index') }}"
                wire:click.prevent="$set('categorySlug', null)"
                @class([
                    'p-3 text-center py-2.5 rounded-xl text-sm font-medium transition',
                    'bg-white text-stone-900 shadow-sm' => blank($categorySlug),
                    'hover:bg-stone-50 text-stone-600 hover:text-stone-900' => filled($categorySlug),
                ])
            >
                الكل
            </a>

            @foreach ($categories as $category)
                <a
                    href="{{ route('tenant.properties-rental.index', ['category' => $category->slug]) }}"
                    wire:click.prevent="$set('categorySlug', '{{ $category->slug }}')"
                    wire:key="unit-category-filter-{{ $category->id }}"
                    @class([
                        'p-3 text-center py-2.5 rounded-xl text-sm font-medium transition',
                        'bg-white text-stone-900 shadow-sm' => $categorySlug === $category->slug,
                        'hover:bg-stone-50 text-stone-600 hover:text-stone-900' => $categorySlug !== $category->slug,
                    ])
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3" x-data="{ open: false }">
            <div x-show="open" x-transition class="hidden sm:block">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="ابحث في الوحدات..."
                    class="w-44 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 outline-none focus:border-stone-400"
                >
            </div>

            <button type="button" @click="open = !open" class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition" aria-label="البحث">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
            </button>
        </div>
    </section>

    <section class="p-1">
        @if ($units->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد وحدات حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر الوحدات المنشورة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 md:gap-6 xl:grid-cols-3">
                @foreach ($units as $unit)
                    @php
                        $unitCategories = $unit->taxonomies;
                        $imageUrl = $unit->getFirstMediaUrl('unit-media') ?: $unit->avatar;
                        $subtitle = (string) data_get($unit->data, 'subtitle', '');
                        $price = (int) data_get($unit->data, 'price', 0);
                    @endphp

                    <a
                        href="{{ route('tenant.properties-rental.detail', $unit->slug) }}"
                        wire:navigate
                        wire:key="unit-{{ $unit->id }}"
                        class="group overflow-hidden rounded-xl bg-white transition md:rounded-2xl"
                    >
                        <div class="relative">
                            <img src="{{ $imageUrl }}" alt="{{ $unit->title }}" class="h-56 w-full object-cover transition-all duration-500 group-hover:scale-105 md:h-72">

                            @if ($unitCategories->isNotEmpty())
                                <div class="absolute bottom-2 start-2 flex flex-wrap gap-1">
                                    @foreach ($unitCategories->take(2) as $category)
                                        <span class="rounded-md bg-black/30 px-2 py-0.5 text-[10px] font-medium text-white backdrop-blur-md">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="rounded-b-xl border border-neutral-200 border-t-0 p-3 md:rounded-b-2xl">
                            <h3 class="truncate text-base font-semibold tracking-tight md:text-lg">{{ $unit->title }}</h3>

                            @if ($subtitle !== '')
                                <p class="mt-1 truncate text-xs text-neutral-600">{{ $subtitle }}</p>
                            @endif

                            <div class="mt-4 flex items-end justify-between">
                                <div>
                                    <p class="text-[11px] text-stone-500">السعر / ليلة</p>
                                    @if ($price > 0)
                                        <p class="text-lg font-black text-stone-900" dir="ltr">{{ money_format($price) }}</p>
                                    @else
                                        <p class="text-sm text-stone-400">—</p>
                                    @endif
                                </div>

                                <span class="inline-flex items-center rounded-[9px] bg-primary-50 px-3 py-1.5 text-sm font-semibold text-primary-600">
                                    التفاصيل والحجز
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::properties-rental.layout>
