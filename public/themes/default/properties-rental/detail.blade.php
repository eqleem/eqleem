<x-tenant-theme::properties-rental.layout>
    @push('scripts')
        @vite(['resources/js/property-booking.js'])
    @endpush

    <x-tenant-theme::breadcrumb :links="[
        ['url' => route('tenant.properties-rental.index'), 'title' => 'تأجير الوحدات'],
        ['url' => null, 'title' => 'تفاصيل الوحدة'],
    ]" />

    <div class="mb-5 mt-3 flex items-center justify-between px-2">
        <a href="{{ route('tenant.properties-rental.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
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
                        <button class="gallery-nav h-20 w-20 shrink-0 overflow-hidden rounded-lg border-2 border-transparent bg-stone-100 hover:border-stone-300">
                            <img src="{{ $image }}" alt="{{ $property['name'] }}" class="h-full w-full object-cover">
                        </button>
                    @endforeach
                </div>
            </div>

            <div>
                <div class="flex justify-between gap-3">
                    <div>
                        <h1 class="mb-1 text-xl font-bold tracking-tight text-stone-900">{{ $property['name'] }}</h1>
                        <p class="text-sm text-stone-500">{{ $property['location'] }}</p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-800">متاحة الآن</span>
                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700">
                                <iconify-icon icon="solar:star-bold"></iconify-icon>
                                {{ $property['rating'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <p class="mt-5 text-sm leading-7 text-stone-600">{{ $property['description'] }}</p>

                <div class="mt-5 grid grid-cols-2 gap-3 text-sm text-stone-700">
                    <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">المساحة: {{ $property['area'] }} م²</div>
                    <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">الأسرة: {{ $property['beds'] }}</div>
                    <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">الحمامات: {{ $property['baths'] }}</div>
                    <div class="rounded-xl border border-stone-200 bg-white px-3 py-2">الضيوف: {{ $property['guests'] }}</div>
                </div>

                <div
                    class="mt-6 rounded-2xl border border-stone-200 bg-white shadow-sm"
                    x-data="propertyBooking({
                        pricePerNight: {{ $property['price_per_night'] }},
                        checkIn: '{{ now()->toDateString() }}',
                        checkOut: '{{ now()->addDay()->toDateString() }}',
                    })"
                >
                    <div class="border-b border-stone-100 px-5 py-4">
                        <div class="flex items-end justify-between gap-3">
                            <div>
                                <p class="text-2xl font-black text-primary-700">
                                    <span x-text="formatMoney(pricePerNight)"></span>
                                    <span class="text-base font-bold">ريال / ليلة</span>
                                </p>
                                <p class="mt-1 text-xs text-stone-500">
                                    إجمالي <span x-text="nightsLabel"></span>
                                    <span x-text="formatMoney(total)"></span> ر.س
                                </p>
                            </div>
                            @if ($property['old_price_per_night'])
                                <p class="text-sm text-stone-400 line-through">{{ number_format($property['old_price_per_night']) }} ر.س</p>
                            @endif
                        </div>
                    </div>

                    <div class="space-y-5 px-5 py-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-stone-900" x-text="nightsLabel"></p>
                            <p class="text-xs text-stone-500">بإمكانك تعديل التاريخ</p>
                        </div>

                        <div class="relative">
                            <input type="text" x-ref="rangeInput" class="hidden" tabindex="-1" aria-hidden="true" readonly>

                            <button
                                type="button"
                                x-ref="dateTrigger"
                                x-on:click="openCalendar()"
                                class="w-full overflow-hidden rounded-xl border border-primary-200 text-start transition hover:border-primary-300 hover:bg-primary-50/30"
                            >
                                <div class="grid grid-cols-2 divide-x divide-x-reverse divide-primary-100">
                                    <div class="px-4 py-3">
                                        <span class="mb-1 block text-xs text-stone-500">تاريخ الوصول</span>
                                        <span class="block text-sm font-semibold text-stone-900" x-text="formatDate(checkIn)"></span>
                                    </div>
                                    <div class="px-4 py-3">
                                        <span class="mb-1 block text-xs text-stone-500">تاريخ المغادرة</span>
                                        <span class="block text-sm font-semibold text-stone-900" x-text="formatDate(checkOut)"></span>
                                    </div>
                                </div>
                            </button>

                            <div x-ref="calendarAnchor" class="flatpickr-anchor"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-4 border-y border-stone-100 py-4">
                            <div>
                                <p class="text-xs text-stone-500">وقت الوصول</p>
                                <p class="mt-1 text-sm font-semibold text-stone-900">{{ $property['check_in_time'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-stone-500">وقت المغادرة</p>
                                <p class="mt-1 text-sm font-semibold text-stone-900">{{ $property['check_out_time'] }}</p>
                            </div>
                        </div>

                        <form wire:submit.prevent>
                            <button
                                type="submit"
                                class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-primary-500 text-sm font-bold text-white transition hover:bg-primary-600"
                            >
                                اختر
                            </button>
                            <p class="mt-2 text-center text-xs text-stone-600">
                                ستدفع الآن <span class="font-bold text-stone-900" x-text="formatMoney(total)"></span> ريال
                            </p>
                        </form>

                        <div class="space-y-3 border-t border-stone-100 pt-4 text-sm">
                            <div class="flex items-center justify-between text-stone-600">
                                <span>
                                    <span x-text="nightsLabel"></span>
                                    × <span x-text="formatMoney(pricePerNight)"></span> ريال
                                </span>
                                <span class="font-semibold text-stone-900"><span x-text="formatMoney(subtotal)"></span> ريال</span>
                            </div>
                            <div class="flex items-center justify-between text-stone-600">
                                <span class="inline-flex items-center gap-1">
                                    رسوم الخدمة
                                    <iconify-icon icon="solar:info-circle-linear" class="text-base text-stone-400"></iconify-icon>
                                </span>
                                <span class="font-semibold text-stone-900">+<span x-text="formatMoney(serviceFee)"></span> ريال</span>
                            </div>
                            <div class="flex items-center justify-between text-stone-600">
                                <span>ضريبة القيمة المضافة</span>
                                <span class="font-semibold text-stone-900">+<span x-text="formatMoney(vat)"></span> ريال</span>
                            </div>
                            <div class="flex items-center justify-between border-t border-stone-100 pt-3">
                                <span class="font-bold text-stone-900">الإجمالي</span>
                                <span class="text-base font-black text-stone-900"><span x-text="formatMoney(total)"></span> ريال</span>
                            </div>
                        </div>

                        <div class="space-y-2 border-t border-stone-100 pt-4">
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-stone-200 bg-stone-50 px-3 py-3">
                                <p class="text-xs leading-5 text-stone-600">قسّمها على 4. بدون رسوم تأخير</p>
                                <span class="shrink-0 rounded-lg bg-emerald-500 px-2.5 py-1 text-xs font-black text-white">tabby</span>
                            </div>
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-stone-200 bg-stone-50 px-3 py-3">
                                <p class="text-xs leading-5 text-stone-600">قسّمها على 4. بدون رسوم تأخير</p>
                                <span class="shrink-0 rounded-lg bg-gradient-to-r from-rose-400 via-violet-500 to-sky-400 px-2.5 py-1 text-xs font-black text-white">tamara</span>
                            </div>
                        </div>
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
                    class="shrink-0 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition flex items-center gap-2"
                >
                    <iconify-icon icon="solar:info-circle-bold" class="text-lg text-primary-600"></iconify-icon>  
                    المواصفات والميزات
                </button>
                <button
                    type="button"
                    x-on:click="tab = 'reviews'"
                    x-bind:class="tab === 'reviews' ? 'bg-white shadow-sm' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900'"
                    class="shrink-0 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition flex items-center gap-2"
                >
                <iconify-icon icon="solar:star-bold" class="text-lg text-primary-600"></iconify-icon>   
                    تقييمات الضيوف
                </button>
                <button
                    type="button"
                    x-on:click="tab = 'location'"
                    x-bind:class="tab === 'location' ? 'bg-white shadow-sm' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900'"
                    class="shrink-0 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition flex items-center gap-2"
                >
                <iconify-icon icon="solar:map-point-bold" class="text-lg text-primary-600"></iconify-icon>      
                    الموقع والخريطة
                </button>
                <button
                    type="button"
                    x-on:click="tab = 'terms'"
                    x-bind:class="tab === 'terms' ? 'bg-white shadow-sm' : 'text-stone-600 hover:bg-stone-50 hover:text-stone-900'"
                    class="shrink-0 rounded-xl px-4 py-2.5 text-center text-sm font-semibold transition flex items-center gap-2"
                >
                <iconify-icon icon="solar:shield-check-bold" class="text-lg text-primary-600"></iconify-icon>               
                    شروط الحجز والإلغاء
                </button>
            </div>

            <div x-show="tab === 'specs'" x-cloak class="space-y-6">
                <h2 class="text-xl font-bold text-stone-900">المواصفات والميزات</h2>

                <div class="flex items-center gap-2 text-sm text-stone-700">
                    <iconify-icon icon="solar:shield-check-bold" class="text-lg text-stone-400"></iconify-icon>
                    <span>{{ $property['security_note'] }}</span>
                </div>

                @foreach ($property['spec_sections'] as $section)
                    <div class="border-t border-stone-100 pt-5">
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

            <div x-show="tab === 'reviews'" x-cloak class="space-y-6">
                <h2 class="text-xl font-bold text-stone-900">تقييمات الضيوف</h2>

                <div class="flex flex-wrap items-center gap-4">
                    <div class="inline-flex items-center gap-3 rounded-2xl bg-amber-50 px-4 py-3">
                        <span class="text-3xl font-black text-amber-600">{{ $property['reviews_summary']['score'] }}</span>
                        <div>
                            <p class="text-sm font-bold text-stone-900">{{ $property['reviews_summary']['label'] }}</p>
                            <p class="text-xs text-stone-500">({{ $property['reviews_summary']['count'] }} تقييم)</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    @foreach ($property['reviews_breakdown'] as $breakdown)
                        <div class="rounded-xl border border-stone-200 bg-stone-50 px-3 py-2.5">
                            <p class="text-xs text-stone-500">{{ $breakdown['label'] }}</p>
                            <div class="mt-1 flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-stone-900">{{ $breakdown['score'] }}</span>
                                <span class="text-xs text-stone-500">{{ $breakdown['status'] }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 gap-4 border-t border-stone-100 pt-6 md:grid-cols-2">
                    @foreach ($property['reviews'] as $review)
                        <article class="rounded-2xl border border-stone-100 bg-stone-50 p-4">
                            <div class="mb-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="flex size-9 items-center justify-center rounded-full bg-primary-100 text-primary-600">
                                        <iconify-icon icon="solar:user-bold"></iconify-icon>
                                    </div>
                                    <div>
                                        <p class="text-sm font-bold text-stone-900">{{ $review['name'] }}</p>
                                        <p class="text-xs text-stone-500">{{ $review['date'] }}</p>
                                    </div>
                                </div>
                                <div class="inline-flex items-center gap-1 text-xs font-bold text-amber-600">
                                    <iconify-icon icon="solar:star-bold"></iconify-icon>
                                    <span>{{ $review['score'] }}</span>
                                    <span class="font-medium text-stone-500">{{ $review['status'] }}</span>
                                </div>
                            </div>
                            <p class="text-sm leading-7 text-stone-600">{{ $review['comment'] }}</p>
                        </article>
                    @endforeach
                </div>
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
                        title="خريطة موقع الوحدة"
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

            <div x-show="tab === 'terms'" x-cloak class="space-y-5">
                <h2 class="text-xl font-bold text-stone-900">شروط الحجز والإلغاء</h2>

                @foreach ($property['terms'] as $term)
                    <div class="border-t border-stone-100 pt-4 first:border-t-0 first:pt-0">
                        <h3 class="mb-2 text-sm font-bold text-stone-900">{{ $term['title'] }}</h3>
                        <ul class="space-y-2">
                            @foreach ($term['items'] as $item)
                                <li class="flex items-start gap-2 text-sm text-stone-600">
                                    <iconify-icon icon="solar:check-circle-bold" class="mt-0.5 shrink-0 text-base text-primary-600"></iconify-icon>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-tenant-theme::properties-rental.layout>