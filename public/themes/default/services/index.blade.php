<x-tenant-theme::services.layout>
    <section class="px-1 mb-5 w-full flex items-center justify-between gap-3">
        <div class="flex items-center gap-3 overflow-x-auto no-scrollbar bg-stone-200/40 rounded-2xl p-1 whitespace-nowrap w-full">
            <a
                href="{{ route('tenant.services.index') }}"
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
                    href="{{ route('tenant.services.index', ['category' => $category->slug]) }}"
                    wire:click.prevent="$set('categorySlug', '{{ $category->slug }}')"
                    wire:key="service-category-filter-{{ $category->id }}"
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

            <button
                type="button"
                @click="open = !open"
                class="p-3 rounded-xl bg-stone-200/40 hover:bg-stone-200 flex items-center justify-center transition-all duration-200 hover:scale-105"
                aria-label="البحث"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="size-6 text-stone-700"><path d="m21 21-4.34-4.34"></path><circle cx="11" cy="11" r="8"></circle></svg>
            </button>
        </div>
    </section>

    <section class="p-1" x-data>
        @if ($services->isEmpty())
            <div class="rounded-2xl bg-stone-100/80 p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد خدمات حالياً</p>
                <p class="mt-2 text-sm text-stone-500">ستظهر الخدمات المنشورة هنا عند إضافتها من لوحة التحكم.</p>
            </div>
        @else
            <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                @foreach ($services as $service)
                    @php
                        $serviceCategories = $service->taxonomies;
                        $imageUrl = $service->getFirstMediaUrl('service-media') ?: $service->avatar;
                        $subtitle = (string) data_get($service->data, 'subtitle', '');
                        $price = (int) data_get($service->data, 'price', 0);
                    @endphp

                    <article wire:key="service-{{ $service->id }}" class="overflow-hidden rounded-2xl border border-stone-200 bg-white">
                        <a href="{{ route('tenant.services.detail', $service->slug) }}" wire:navigate class="block">
                            <img src="{{ $imageUrl }}" alt="{{ $service->title }}" class="h-56 w-full object-cover">
                        </a>

                        <div class="space-y-3 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <a href="{{ route('tenant.services.detail', $service->slug) }}" wire:navigate class="text-lg font-semibold text-stone-900 transition hover:text-primary-700">
                                        {{ $service->title }}
                                    </a>

                                    @if ($subtitle !== '')
                                        <p class="text-sm text-stone-500">{{ $subtitle }}</p>
                                    @endif

                                    @if ($serviceCategories->isNotEmpty())
                                        <p class="mt-1 text-xs text-primary-600">{{ $serviceCategories->pluck('name')->join('، ') }}</p>
                                    @endif
                                </div>

                                <span class="inline-flex items-center gap-1 rounded-lg bg-primary-50 px-3 py-1 text-sm font-semibold text-primary-700">
                                    @if ($price > 0)
                                        <span >{{ money_format($price) }}</span>
                                    @else
                                        حسب الطلب
                                    @endif
                                </span>
                            </div>

                            <button
                                type="button"
                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-50 px-4 py-2.5 text-sm font-semibold text-primary-700 hover:bg-primary-100"
                                x-on:click="$dispatch('set-booking-service', { service: @js($service->title) }); $dispatch('open-modal', { name: 'service-booking-modal' })"
                            >
                                <iconify-icon icon="hugeicons:calendar-03" class="text-xl"></iconify-icon>
                                حجز خدمة
                            </button>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <x-tenant-theme::modal name="service-booking-modal" maxWidth="lg">
        <x-slot:title>طلب حجز خدمة</x-slot:title>

        <form class="space-y-4" x-data="{ serviceName: '' }" x-on:set-booking-service.window="serviceName = $event.detail.service">
            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الخدمة</label>
                <input type="text" x-model="serviceName" readonly class="w-full rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm text-stone-700">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">الاسم</label>
                <input type="text" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none" placeholder="اكتب اسمك">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">رقم الجوال</label>
                <input type="tel" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none" placeholder="05xxxxxxxx" dir="ltr">
            </div>

            <div class="space-y-1">
                <label class="text-sm font-medium text-stone-700">تفاصيل إضافية</label>
                <textarea rows="4" class="w-full rounded-xl border border-stone-200 px-4 py-3 text-sm text-stone-700 focus:border-primary-300 focus:outline-none" placeholder="أدخل ملاحظاتك"></textarea>
            </div>

            <button type="button" class="w-full rounded-xl bg-primary-600 px-4 py-3 text-sm font-semibold text-white hover:bg-primary-700">
                إرسال طلب الحجز
            </button>
        </form>
    </x-tenant-theme::modal>
</x-tenant-theme::services.layout>
