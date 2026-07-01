<x-tenant::properties.layout>
    <section class="mb-5 flex gap-3 px-1  flex-row  items-stretch">
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

    <section class="p-1">
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
</x-tenant::properties.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, string> */
    public array $propertyTypes = [];

    /** @var array<int, string> */
    public array $listingFilters = [];

    /** @var array<int, array<string, int|string>> */
    public array $properties = [];

    public function mount(): void
    {
        $this->propertyTypes = [
            'الكل',
            'شقة',
            'أرض',
            'عمارة',
            'مزرعة',
            'مكتب',
            'فيلا',
            'استديو',
        ];

        $this->listingFilters = [
            'الكل',
            'بيع',
            'آجار',
        ];

        $this->properties = [
            [
                'slug' => 'two-bedroom-apartment-aqiq',
                'name' => 'شقة غرفتين وصالة',
                'location' => 'الرياض - حي العقيق',
                'property_type' => 'شقة سكنية',
                'image' => 'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                'listing_type' => 'for-rent',
                'price' => 2300,
                'price_period' => 'monthly',
                'beds' => 2,
                'baths' => 2,
                'area' => 95,
            ],
            [
                'slug' => 'luxury-villa-narjis',
                'name' => 'فيلا مودرن فاخرة',
                'location' => 'الرياض - حي النرجس',
                'property_type' => 'فيلا',
                'image' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?q=80&w=1200&auto=format&fit=crop',
                'listing_type' => 'for-rent',
                'price' => 290200,
                'price_period' => 'yearly',
                'beds' => 5,
                'baths' => 6,
                'area' => 420,
            ],
            [
                'slug' => 'apartment-sale-malqa',
                'name' => 'شقة تملك غرفتين',
                'location' => 'الرياض - حي الملقا',
                'property_type' => 'شقة سكنية',
                'image' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                'listing_type' => 'for-sale',
                'price' => 850000,
                'price_period' => null,
                'beds' => 2,
                'baths' => 2,
                'area' => 110,
            ],
            [
                'slug' => 'land-plot-yasmin',
                'name' => 'أرض سكنية شمال الرياض',
                'location' => 'الرياض - حي الياسمين',
                'property_type' => 'أرض',
                'image' => 'https://images.unsplash.com/photo-1500382017468-9049fed747ef?q=80&w=1200&auto=format&fit=crop',
                'listing_type' => 'for-sale',
                'price' => 1250000,
                'price_period' => null,
                'beds' => 0,
                'baths' => 0,
                'area' => 625,
            ],
            [
                'slug' => 'studio-rent-falah',
                'name' => 'استديو للإيجار السنوي',
                'location' => 'الرياض - حي الفلاح',
                'property_type' => 'استديو',
                'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                'listing_type' => 'for-rent',
                'price' => 1800,
                'price_period' => 'monthly',
                'beds' => 1,
                'baths' => 1,
                'area' => 38,
            ],
            [
                'slug' => 'villa-sale-sahafa',
                'name' => 'فيلا دورين مع ملحق',
                'location' => 'الرياض - حي الصحافة',
                'property_type' => 'فيلا',
                'image' => 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?q=80&w=1200&auto=format&fit=crop',
                'listing_type' => 'for-sale',
                'price' => 3200000,
                'price_period' => null,
                'beds' => 6,
                'baths' => 7,
                'area' => 520,
            ],
        ];
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
};
?>
