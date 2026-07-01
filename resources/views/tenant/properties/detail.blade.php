<x-tenant::properties.layout>
    <x-tenant::breadcrumb :links="[
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
</x-tenant::properties.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<string, mixed> */
    public array $property = [];

    public function mount(string $slug): void
    {
        $properties = [
            'two-bedroom-apartment-aqiq' => [
                'name' => 'شقة غرفتين وصالة',
                'location' => 'الرياض - حي العقيق',
                'property_type' => 'شقة سكنية',
                'listing_type' => 'for-rent',
                'price' => 2300,
                'price_period' => 'monthly',
                'description' => 'شقة نظيفة بموقع مميز قريبة من الخدمات والطرق الرئيسية، مناسبة للعائلات الصغيرة، تشطيب حديث مع مطبخ راكب وموقف خاص.',
                'images' => [
                    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 2,
                'baths' => 2,
                'area' => 95,
                'age' => 'أقل من 5 سنوات',
            ],
            'luxury-villa-narjis' => [
                'name' => 'فيلا مودرن فاخرة',
                'location' => 'الرياض - حي النرجس',
                'property_type' => 'فيلا',
                'listing_type' => 'for-rent',
                'price' => 290200,
                'price_period' => 'yearly',
                'description' => 'فيلا واسعة بتصميم عصري، مدخل خاص، حديقة، ومسبح. مناسبة للإقامة العائلية الفاخرة في حي هادئ وخدمات متكاملة.',
                'images' => [
                    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 5,
                'baths' => 6,
                'area' => 420,
                'age' => 'جديدة',
            ],
            'apartment-sale-malqa' => [
                'name' => 'شقة تملك غرفتين',
                'location' => 'الرياض - حي الملقا',
                'property_type' => 'شقة سكنية',
                'listing_type' => 'for-sale',
                'price' => 850000,
                'price_period' => null,
                'description' => 'فرصة تملك شقة بمواصفات ممتازة في برج سكني حديث، قريبة من المدارس والمراكز التجارية، صك إلكتروني وجاهزة للتسليم.',
                'images' => [
                    'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 2,
                'baths' => 2,
                'area' => 110,
                'age' => 'أقل من 3 سنوات',
            ],
            'land-plot-yasmin' => [
                'name' => 'أرض سكنية شمال الرياض',
                'location' => 'الرياض - حي الياسمين',
                'property_type' => 'أرض',
                'listing_type' => 'for-sale',
                'price' => 1250000,
                'price_period' => null,
                'description' => 'أرض سكنية بموقع استراتيجي على شارعين، مناسبة لبناء فيلا أو عمارة سكنية، جميع الخدمات متوفرة والحي مطور.',
                'images' => [
                    'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1464207687429-7505649dae38?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 0,
                'baths' => 0,
                'area' => 625,
                'age' => '—',
            ],
            'studio-rent-falah' => [
                'name' => 'استديو للإيجار السنوي',
                'location' => 'الرياض - حي الفلاح',
                'property_type' => 'استديو',
                'listing_type' => 'for-rent',
                'price' => 1800,
                'price_period' => 'monthly',
                'description' => 'استديو مؤثث بالكامل مناسب للأفراد، قريب من المترو والخدمات، عقد إيجار سنوي مرن.',
                'images' => [
                    'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 1,
                'baths' => 1,
                'area' => 38,
                'age' => 'أقل من 10 سنوات',
            ],
            'villa-sale-sahafa' => [
                'name' => 'فيلا دورين مع ملحق',
                'location' => 'الرياض - حي الصحافة',
                'property_type' => 'فيلا',
                'listing_type' => 'for-sale',
                'price' => 3200000,
                'price_period' => null,
                'description' => 'فيلا فاخرة بدورين وملحق خارجي، تشطيبات راقية، مصعد داخلي، ومواقف متعددة. فرصة مميزة للسكن أو الاستثمار.',
                'images' => [
                    'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1613490493576-7fde63acd811?q=80&w=1200&auto=format&fit=crop',
                    'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?q=80&w=1200&auto=format&fit=crop',
                ],
                'beds' => 6,
                'baths' => 7,
                'area' => 520,
                'age' => 'أقل من 7 سنوات',
            ],
        ];

        $property = $properties[$slug] ?? reset($properties);
        $this->property = $this->withDetails($property);
    }

    /**
     * @param  array<string, mixed>  $property
     */
    public function priceLabel(array $property): string
    {
        if ($property['listing_type'] === 'for-sale') {
            return number_format((int) $property['price']).' ريال';
        }

        $period = match ($property['price_period']) {
            'monthly' => 'شهرياً',
            'yearly' => 'سنوياً',
            default => '',
        };

        return number_format((int) $property['price']).' ريال / '.$period;
    }

    /**
     * @param  array<string, mixed>  $property
     * @return array<string, mixed>
     */
    private function withDetails(array $property): array
    {
        $beds = (int) $property['beds'];
        $baths = (int) $property['baths'];

        return array_merge($property, [
            'address' => 'شارع الأمير محمد بن سعد بن عبدالعزيز، حي '.str($property['location'])->after(' - ')->value().'، الرياض',
            'map_embed' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3624.198!2d46.6753!3d24.7136!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjTCsDQyJzQ5LjAiTiA0NsKwNDAnMzEuMSJF!5e0!3m2!1sar!2ssa!4v1710000000000!5m2!1sar!2ssa',
            'nearby' => [
                'مدارس - 5 دقائق',
                'مراكز تسوق - 8 دقائق',
                'مستشفى - 10 دقائق',
                'طرق رئيسية - 3 دقائق',
            ],
            'agent' => [
                'name' => 'أحمد السبيعي',
                'company' => 'وسيط عقاري معتمد',
                'phone' => '0501234567',
                'phone_display' => '050 123 4567',
                'whatsapp' => '966501234567',
                'email' => 'ahmad@property.sa',
            ],
            'spec_sections' => [
                [
                    'icon' => 'solar:home-2-bold',
                    'title' => 'تفاصيل العقار',
                    'items' => array_filter([
                        'نوع العقار: '.$property['property_type'],
                        $beds > 0 ? 'عدد الغرف: '.$beds : null,
                        $baths > 0 ? 'عدد الحمامات: '.$baths : null,
                        'المساحة: '.$property['area'].' م²',
                        'عمر العقار: '.$property['age'],
                    ]),
                ],
                [
                    'icon' => 'solar:wi-fi-router-bold',
                    'title' => 'المرافق',
                    'items' => [
                        'موقف سيارة',
                        'تكييف مركزي',
                        'مصعد',
                        'إنترنت جاهز',
                    ],
                ],
                [
                    'icon' => 'solar:shield-check-bold',
                    'title' => 'مميزات إضافية',
                    'items' => [
                        $property['listing_type'] === 'for-rent' ? 'عقد إيجار موثق' : 'صك إلكتروني',
                        'قريب من الخدمات',
                        'حي هادئ وآمن',
                    ],
                ],
            ],
            'extra_info' => [
                ['label' => 'رقم الإعلان', 'value' => 'AQ-'.strtoupper(substr(md5($property['name']), 0, 5))],
                ['label' => 'تاريخ النشر', 'value' => 'منذ 3 أيام'],
                ['label' => 'الواجهة', 'value' => 'شمالية'],
                ['label' => 'حالة التشطيب', 'value' => 'مؤثث'],
                ['label' => 'نوع العقد', 'value' => $property['listing_type'] === 'for-rent' ? 'إيجار' : 'بيع'],
                ['label' => 'الدفع', 'value' => $property['listing_type'] === 'for-rent' ? 'دفعات دورية' : 'كاش أو تمويل'],
            ],
        ]);
    }
};
?>
