<x-tenant::properties-rental.layout>
    <x-tenant::breadcrumb :links="[
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
</x-tenant::properties-rental.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<string, mixed> */
    public array $property = [];

    public function mount(string $slug): void
    {
        $properties = [
            'master-studio-hadi' => [
                'name' => 'استديو هادي بسرير ماستر',
                'location' => 'الرياض - حي العقيق',
                'description' => 'استديو حديث بتشطيب فاخر، سرير ماستر مريح، مكتب صغير للعمل، ودخول ذاتي كامل. مناسب لإقامة يومية أو أسبوعية.',
                'images' => [
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 1,
                'baths' => 1,
                'guests' => 2,
                'area' => 35,
                'rating' => '10.0',
                'price_per_night' => 285,
                'old_price_per_night' => 359,
            ],
            'side-session-studio' => [
                'name' => 'استديو راقٍ بجلسة جانبية',
                'location' => 'الرياض - حي اليرموك',
                'description' => 'وحدة هادئة بجلسة جانبية أنيقة، واي فاي عالي السرعة، وشاشة ذكية. مثالية للرحلات السريعة والعمل عن بعد.',
                'images' => [
                    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1616594039964-3f5f9f8d90f4?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 1,
                'baths' => 1,
                'guests' => 2,
                'area' => 30,
                'rating' => '9.8',
                'price_per_night' => 249,
                'old_price_per_night' => 315,
            ],
            'two-bedroom-lounge' => [
                'name' => 'شقة غرفتين وصالة',
                'location' => 'الرياض - حي الملقا',
                'description' => 'شقة عملية لعائلة صغيرة، صالة مريحة، مطبخ مجهز بالكامل، ومواقف قريبة. خيار ممتاز للإقامات المتوسطة والطويلة.',
                'images' => [
                    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 3,
                'baths' => 2,
                'guests' => 5,
                'area' => 90,
                'rating' => '9.7',
                'price_per_night' => 400,
                'old_price_per_night' => 504,
            ],
            'one-bedroom-lounge' => [
                'name' => 'شقة غرفة نوم وصالة',
                'location' => 'الرياض - حي النرجس',
                'description' => 'شقة راقية تناسب المسافرين والموظفين، صالة منفصلة، مطبخ خفيف، وتكييف ممتاز طوال اليوم.',
                'images' => [
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1616594039964-3f5f9f8d90f4?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 1,
                'baths' => 1,
                'guests' => 3,
                'area' => 60,
                'rating' => '10.0',
                'price_per_night' => 295,
                'old_price_per_night' => null,
            ],
            'premium-master-suite' => [
                'name' => 'جناح ماستر فاخر',
                'location' => 'الرياض - حي الفلاح',
                'description' => 'جناح راقٍ بتفاصيل فندقية، جلسة داخلية وإضاءة عصرية، مع خصوصية عالية تناسب المناسبات القصيرة.',
                'images' => [
                    'https://images.unsplash.com/photo-1616594039964-3f5f9f8d90f4?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 1,
                'baths' => 1,
                'guests' => 2,
                'area' => 50,
                'rating' => '9.9',
                'price_per_night' => 320,
                'old_price_per_night' => 355,
            ],
            'family-two-room-unit' => [
                'name' => 'وحدة عائلية غرفتين',
                'location' => 'الرياض - حي الصحافة',
                'description' => 'وحدة عائلية واسعة مع غرفتين وصالة، مناسبة للعائلات، وقريبة من الخدمات والمطاعم والطرق السريعة.',
                'images' => [
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1616594039964-3f5f9f8d90f4?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 2,
                'baths' => 2,
                'guests' => 6,
                'area' => 110,
                'rating' => '9.6',
                'price_per_night' => 360,
                'old_price_per_night' => 410,
            ],
        ];

        $property = $properties[$slug] ?? reset($properties);
        $this->property = $this->withTabDetails($property);
    }

    /**
     * @param  array<string, mixed>  $property
     * @return array<string, mixed>
     */
    private function withTabDetails(array $property): array
    {
        $guests = (int) $property['guests'];
        $baths = (int) $property['baths'];

        return array_merge($property, [
            'check_in_time' => '04:00 مساءً',
            'check_out_time' => '12:00 مساءً',
            'security_note' => 'لا يتطلب تأمين عند الوصول',
            'address' => 'شارع الأمير محمد بن سعد بن عبدالعزيز، حي '.str($property['location'])->after(' - ')->value().'، الرياض 13515',
            'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.198!2d46.6753!3d24.7136!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjTCsDQyJzQ5LjAiTiA0NsKwNDAnMzEuMSJF!5e0!3m2!1sar!2ssa!4v1710000000000!5m2!1sar!2ssa',
            'nearby' => [
                'مول قريب - 5 دقائق بالسيارة',
                'مطاعم ومقاهي - 3 دقائق',
                'محطة وقود - 2 دقيقة',
                'طريق رئيسي - 1 دقيقة',
            ],
            'spec_sections' => [
                [
                    'icon' => 'solar:sofa-3-bold',
                    'title' => 'المجالس والجلسات',
                    'items' => [
                        'مجلس رئيسي يسع لـ '.$guests.' أشخاص',
                        'جلسة جانبية مريحة',
                    ],
                ],
                [
                    'icon' => 'solar:wi-fi-router-bold',
                    'title' => 'المرافق',
                    'items' => [
                        'إنترنت عالي السرعة',
                        'إضاءة إضافية',
                        'تلفزيون ذكي',
                        'مصعد',
                        'دخول ذاتي',
                    ],
                ],
                [
                    'icon' => 'solar:bath-bold',
                    'title' => 'دورات المياه',
                    'items' => [
                        $baths === 1 ? 'دورة مياه واحدة' : $baths.' دورات مياه',
                    ],
                ],
                [
                    'icon' => 'solar:hand-soap-bold',
                    'title' => 'مرافق دورات المياه',
                    'items' => [
                        'مناديل',
                        'صابون',
                        'شامبو وجل استحمام',
                        'مجفف شعر',
                    ],
                ],
            ],
            'reviews_summary' => [
                'score' => $property['rating'],
                'label' => 'رائع',
                'count' => 7,
            ],
            'reviews_breakdown' => [
                ['label' => 'النظافة', 'score' => '9.9', 'status' => 'رائع'],
                ['label' => 'المضيف', 'score' => '10', 'status' => 'رائع'],
                ['label' => 'المعلومات', 'score' => '10', 'status' => 'رائع'],
                ['label' => 'المرافق', 'score' => '9.1', 'status' => 'رائع'],
            ],
            'reviews' => [
                [
                    'name' => 'رعد فق',
                    'date' => 'الجمعة، 12 يونيو',
                    'score' => '9.5',
                    'status' => 'رائع',
                    'comment' => 'الوحدة نظيفة جداً والمضيف متعاون، الدخول كان سهلاً والموقع ممتاز.',
                ],
                [
                    'name' => 'سارة العتيبي',
                    'date' => 'الأربعاء، 4 يونيو',
                    'score' => '10',
                    'status' => 'ممتاز',
                    'comment' => 'تجربة رائعة، كل شيء كما في الصور والوصف. أنصح بها بشدة.',
                ],
                [
                    'name' => 'محمد الحربي',
                    'date' => 'السبت، 31 مايو',
                    'score' => '9.8',
                    'status' => 'رائع',
                    'comment' => 'إقامة مريحة وهادئة، الواي فاي سريع والتكييف ممتاز.',
                ],
                [
                    'name' => 'نورة القحطاني',
                    'date' => 'الاثنين، 19 مايو',
                    'score' => '9.2',
                    'status' => 'رائع',
                    'comment' => 'الوحدة منظمة ومجهزة بشكل جيد، والحي هادئ وقريب من الخدمات.',
                ],
            ],
            'terms' => [
                [
                    'title' => 'شروط الحجز',
                    'items' => [
                        'تأكيد الحجز يتم بعد اختيار تاريخ الدخول والخروج.',
                        'وقت الدخول من الساعة 3:00 مساءً ووقت الخروج 12:00 ظهراً.',
                        'الحد الأقصى للضيوف حسب عدد الأسرار المسجّل في الوحدة.',
                        'يُمنع إقامة الحفلات أو الفعاليات دون موافقة مسبقة.',
                    ],
                ],
                [
                    'title' => 'سياسة الإلغاء',
                    'items' => [
                        'إلغاء مجاني حتى 24 ساعة قبل موعد الدخول.',
                        'في حال الإلغاء خلال 24 ساعة يتم خصم ليلة واحدة.',
                        'عدم الحضور (No Show) يؤدي لخصم كامل قيمة الليلة الأولى.',
                    ],
                ],
                [
                    'title' => 'قواعد الإقامة',
                    'items' => [
                        'الالتزام بقواعد الهدوء بعد الساعة 11:00 مساءً.',
                        'يمنع التدخين داخل الوحدة.',
                        'المحافظة على نظافة الوحدة وتسليمها بنفس الحالة.',
                    ],
                ],
            ],
        ]);
    }
};
?>
