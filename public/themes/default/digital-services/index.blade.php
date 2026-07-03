<x-tenant-theme::digital-services.layout>
    <section class="px-1 mb-5 w-full flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 rounded-2xl p-1 whitespace-nowrap w-full">
            <a
                href="{{ route('tenant.digital-services.index') }}"
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
                    href="{{ route('tenant.digital-services.index', ['category' => $category->slug]) }}"
                    wire:click.prevent="$set('categorySlug', '{{ $category->slug }}')"
                    wire:key="digital-service-category-filter-{{ $category->id }}"
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
                    placeholder="ابحث في الخدمات..."
                    class="w-44 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 outline-none focus:border-stone-400 md:w-56"
                >
            </div>

            <button type="button" @click="open = !open" class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105" aria-label="البحث">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
            </button>
        </div>
    </section>

    <section class="p-1">
        @if ($services->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد خدمات رقمية حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر الخدمات المنشورة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="grid grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
                @foreach ($services as $service)
                    @php
                        $serviceCategories = $service->taxonomies;
                        $imageUrl = $service->getFirstMediaUrl('digital-service-media') ?: $service->avatar;
                        $subtitle = (string) data_get($service->data, 'subtitle', '');
                        $price = (int) data_get($service->data, 'price', 0);
                        $deliveryDays = (int) data_get($service->data, 'delivery_days', 0);
                    @endphp

                    <a
                        href="{{ route('tenant.digital-services.detail', $service->slug) }}"
                        wire:navigate
                        wire:key="digital-service-{{ $service->id }}"
                        class="group rounded-xl md:rounded-2xl overflow-hidden bg-white transition"
                    >
                        <div class="relative">
                            <img src="{{ $imageUrl }}" alt="{{ $service->title }}" class="w-full h-56 md:h-72 object-cover group-hover:scale-105 transition-all duration-500">

                            @if ($serviceCategories->isNotEmpty())
                                <div class="absolute bottom-2 start-2 flex flex-wrap gap-1">
                                    @foreach ($serviceCategories->take(2) as $category)
                                        <span class="rounded-md bg-black/30 px-2 py-0.5 text-[10px] font-medium text-white backdrop-blur-md">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="p-3 border border-neutral-200 border-t-0 rounded-b-xl md:rounded-b-2xl">
                            <h3 class="text-lg tracking-tight font-semibold font-geist truncate">{{ $service->title }}</h3>

                            @if ($subtitle !== '')
                                <p class="mt-0.5 text-xs text-neutral-600 font-geist line-clamp-2">{{ $subtitle }}</p>
                            @endif

                            <div class="mt-4 flex items-center justify-between gap-2">
                                <div class="text-xs text-stone-500">
                                    @if ($deliveryDays > 0)
                                        التسليم خلال {{ $deliveryDays }} {{ $deliveryDays === 1 ? 'يوم' : 'أيام' }}
                                    @endif
                                </div>

                                @if ($price > 0)
                                    <p class="text-xl font-bold font-geist" dir="ltr">{{ money_format($price) }}</p>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>
</x-tenant-theme::digital-services.layout>
