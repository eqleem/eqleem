<x-tenant-theme::properties.layout>
    <x-tenant-theme::breadcrumb :links="[
        ['url' => route('tenant.properties.index'), 'title' => 'العقارات'],
        ['url' => null, 'title' => 'تفاصيل العقار'],
    ]" />

    <div class="mb-5 mt-3 flex items-center justify-between px-2">
        <a href="{{ route('tenant.properties.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
            <iconify-icon icon="solar:arrow-left-linear" class="text-xl text-stone-700"></iconify-icon>
        </a>
        <div class="flex items-center gap-2">
            <button class="flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
                <iconify-icon icon="solar:share-linear" class="text-xl text-stone-700"></iconify-icon>
            </button>
            <button class="flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
                <iconify-icon icon="solar:heart-linear" class="text-xl text-stone-700"></iconify-icon>
            </button>
        </div>
    </div>

    <section class="mb-8 w-full px-3">
        <div class="grid grid-cols-1 gap-12 md:grid-cols-2">
            <div>
                <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-stone-100">
                    <img id="mainPropertyImage" src="{{ $property['images'][0] }}" alt="{{ $property['name'] }}" class="h-full w-full object-cover">
                </div>
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach ($property['images'] as $image)
                        <button type="button" class="gallery-nav h-20 w-20 shrink-0 overflow-hidden rounded-lg border-2 border-transparent bg-stone-100 hover:border-stone-300">
                            <img src="{{ $image }}" alt="{{ $property['name'] }}" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="mb-2 flex flex-wrap items-center gap-2">
                            <span @class([
                                'inline-flex items-center rounded-md px-2.5 py-1 text-xs font-bold text-white',
                                'bg-primary-600' => $property['listing_type'] === 'for-rent',
                                'bg-amber-600' => $property['listing_type'] === 'for-sale',
                            ])>
                                {{ $property['listing_type'] === 'for-rent' ? 'للإيجار' : 'للبيع' }}
                            </span>
                            <span class="inline-flex items-center rounded-md bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-700">
                                {{ $property['property_type'] }}
                            </span>
                        </div>
                        <h1 class="mb-1 text-xl font-bold tracking-tight text-stone-900">{{ $property['name'] }}</h1>
                        <p class="text-sm text-stone-500">{{ $property['location'] }}</p>
                    </div>
                </div>

                <div class="mt-5 rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4">
                    <p class="text-2xl font-black text-primary-700">{{ $this->priceLabel($property) }}</p>
                    @if ($property['listing_type'] === 'for-rent' && $property['price_period'] === 'yearly')
                        <p class="mt-1 text-xs text-stone-500">يعادل تقريباً {{ number_format((int) round($property['price'] / 12)) }} ريال شهرياً</p>
                    @endif
                </div>

                <p class="mt-5 text-sm leading-7 text-stone-600">{{ $property['description'] }}</p>

                <div class="mt-5 grid grid-cols-2 gap-3 text-sm text-stone-700">
                    <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">المساحة: {{ $property['area'] }} م²</div>
                    @if ($property['beds'] > 0)
                        <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">الغرف: {{ $property['beds'] }}</div>
                    @endif
                    @if ($property['baths'] > 0)
                        <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">الحمامات: {{ $property['baths'] }}</div>
                    @endif
                    <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">عمر العقار: {{ $property['age'] }}</div>
                </div>

                <div class="mt-6 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
                    <div class="border-b border-stone-100 px-5 py-4">
                        <p class="text-sm font-bold text-stone-900">تواصل مع المسوق العقاري</p>
                        <p class="mt-1 text-xs text-stone-500">للاستفسار أو ترتيب معاينة العقار</p>
                    </div>

                    <div class="space-y-4 px-5 py-5">
                        <div class="flex items-center gap-3">
                            <div class="flex size-12 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                <iconify-icon icon="solar:user-rounded-bold" class="text-2xl"></iconify-icon>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-stone-900">{{ $property['agent']['name'] }}</p>
                                <p class="text-xs text-stone-500">{{ $property['agent']['company'] }}</p>
                            </div>
                        </div>

                        <a
                            href="tel:{{ $property['agent']['phone'] }}"
                            class="flex items-center justify-between rounded-xl border border-stone-200 bg-stone-50 px-4 py-3 transition hover:border-primary-200 hover:bg-primary-50/40"
                        >
                            <div class="flex items-center gap-3">
                                <iconify-icon icon="solar:phone-bold" class="text-xl text-primary-600"></iconify-icon>
                                <div>
                                    <p class="text-xs text-stone-500">رقم الجوال</p>
                                    <p class="text-sm font-bold text-stone-900" dir="ltr">{{ $property['agent']['phone_display'] }}</p>
                                </div>
                            </div>
                            <span class="text-xs font-semibold text-primary-600">اتصال</span>
                        </a>

                        <a
                            href="https://wa.me/{{ $property['agent']['whatsapp'] }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-emerald-500 text-sm font-bold text-white transition hover:bg-emerald-600"
                        >
                            <iconify-icon icon="mdi:whatsapp" class="text-xl"></iconify-icon>
                            <span>تواصل عبر واتساب</span>
                        </a>

                        <a
                            href="tel:{{ $property['agent']['phone'] }}"
                            class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-xl bg-primary-500 text-sm font-bold text-white transition hover:bg-primary-600"
                        >
                            <iconify-icon icon="solar:phone-calling-bold"></iconify-icon>
                            <span>اتصل الآن</span>
                        </a>

                        @if ($property['agent']['email'])
                            <a
                                href="mailto:{{ $property['agent']['email'] }}"
                                class="flex items-center justify-center gap-2 rounded-xl border border-stone-200 px-4 py-3 text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                            >
                                <iconify-icon icon="solar:letter-bold" class="text-lg text-primary-600"></iconify-icon>
                                <span>{{ $property['agent']['email'] }}</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-stone-200 pt-8" x-data="{ tab: 'specs' }">
            <div class="no-scrollbar mb-6 flex gap-2 overflow-x-auto rounded-2xl bg-stone-100 p-1.5 shadow-sm">
                <button
                    type="button"
                    x-on:click="tab = 'specs'"
                    x-bind:class="tab === 'specs' ? 'bg-white shadow-sm' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900'"
                    class="flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition"
                >
                    <iconify-icon icon="solar:info-circle-bold" class="text-lg text-primary-600"></iconify-icon>
                    المواصفات والميزات
                </button>
                <button
                    type="button"
                    x-on:click="tab = 'location'"
                    x-bind:class="tab === 'location' ? 'bg-white shadow-sm' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900'"
                    class="flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition"
                >
                    <iconify-icon icon="solar:map-point-bold" class="text-lg text-primary-600"></iconify-icon>
                    الموقع والخريطة
                </button>
                <button
                    type="button"
                    x-on:click="tab = 'info'"
                    x-bind:class="tab === 'info' ? 'bg-white shadow-sm' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900'"
                    class="flex shrink-0 items-center gap-2 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition"
                >
                    <iconify-icon icon="solar:document-text-bold" class="text-lg text-primary-600"></iconify-icon>
                    معلومات إضافية
                </button>
            </div>

            <div x-show="tab === 'specs'" x-cloak class="space-y-6">
                <h2 class="text-xl font-bold text-stone-900">المواصفات والميزات</h2>

                @foreach ($property['spec_sections'] as $section)
                    <div class="border-t border-stone-100 pt-5 first:border-t-0 first:pt-0">
                        <div class="mb-3 flex items-center gap-2">
                            <iconify-icon icon="{{ $section['icon'] }}" class="text-lg text-stone-400"></iconify-icon>
                            <h3 class="text-sm font-bold text-stone-900">{{ $section['title'] }}</h3>
                        </div>
                        <ul class="space-y-2 pr-1">
                            @foreach ($section['items'] as $item)
                                <li class="flex items-start gap-2 text-sm text-stone-600">
                                    <span class="mt-2 size-1.5 shrink-0 rounded-full bg-stone-300"></span>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>

            <div x-show="tab === 'location'" x-cloak class="space-y-5">
                <h2 class="text-xl font-bold text-stone-900">الموقع والخريطة</h2>

                <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                    <div class="mb-2 flex items-center gap-2">
                        <iconify-icon icon="solar:map-point-bold" class="text-lg text-primary-600"></iconify-icon>
                        <p class="text-sm font-bold text-stone-900">{{ $property['location'] }}</p>
                    </div>
                    <p class="text-sm leading-7 text-stone-600">{{ $property['address'] }}</p>
                </div>

                <div class="overflow-hidden rounded-2xl border border-stone-200 bg-stone-100">
                    <iframe
                        title="خريطة موقع العقار"
                        src="{{ $property['map_embed'] }}"
                        class="h-72 w-full border-0"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                    ></iframe>
                </div>

                <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                    @foreach ($property['nearby'] as $place)
                        <li class="flex items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700">
                            <iconify-icon icon="solar:map-arrow-right-bold" class="text-base text-primary-600"></iconify-icon>
                            <span>{{ $place }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div x-show="tab === 'info'" x-cloak class="space-y-5">
                <h2 class="text-xl font-bold text-stone-900">معلومات إضافية</h2>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach ($property['extra_info'] as $info)
                        <div class="rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                            <p class="text-xs text-stone-500">{{ $info['label'] }}</p>
                            <p class="mt-1 text-sm font-semibold text-stone-900">{{ $info['value'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-tenant-theme::properties.layout>