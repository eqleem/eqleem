<x-tenant::properties-rental.layout>
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
</x-tenant::properties-rental.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, string> */
    public array $categories = [];

    /** @var array<int, array<string, int|string>> */
    public array $properties = [];

    public function mount(): void
    {
        $this->categories = [
            'استديو',
            'شقة غرفة وصالة',
            'شقة غرفتين وصالة',
            'جناح فاخر',
            'وحدة عائلية',
        ];

        $this->properties = [
            [
                'slug' => 'master-studio-hadi',
                'name' => 'استديو هادي بسرير ماستر',
                'location' => 'الرياض - حي العقيق',
                'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                'beds' => 1,
                'baths' => 1,
                'area' => 35,
                'rating' => '10.0',
                'discount' => 25,
                'price_per_night' => 285,
            ],
            [
                'slug' => 'side-session-studio',
                'name' => 'استديو راقٍ بجلسة جانبية',
                'location' => 'الرياض - حي اليرموك',
                'image' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop',
                'beds' => 1,
                'baths' => 1,
                'area' => 30,
                'rating' => '9.8',
                'discount' => 15,
                'price_per_night' => 249,
            ],
            [
                'slug' => 'two-bedroom-lounge',
                'name' => 'شقة غرفتين وصالة',
                'location' => 'الرياض - حي الملقا',
                'image' => 'https://images.unsplash.com/photo-1493666438817-866a91353ca9?q=80&w=1200&auto=format&fit=crop',
                'beds' => 3,
                'baths' => 2,
                'area' => 90,
                'rating' => '9.7',
                'discount' => 8,
                'price_per_night' => 400,
            ],
            [
                'slug' => 'one-bedroom-lounge',
                'name' => 'شقة غرفة نوم وصالة',
                'location' => 'الرياض - حي النرجس',
                'image' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=1200&auto=format&fit=crop',
                'beds' => 1,
                'baths' => 1,
                'area' => 60,
                'rating' => '10.0',
                'discount' => 5,
                'price_per_night' => 295,
            ],
            [
                'slug' => 'premium-master-suite',
                'name' => 'جناح ماستر فاخر',
                'location' => 'الرياض - حي الفلاح',
                'image' => 'https://images.unsplash.com/photo-1616594039964-3f5f9f8d90f4?q=80&w=1200&auto=format&fit=crop',
                'beds' => 1,
                'baths' => 1,
                'area' => 50,
                'rating' => '9.9',
                'discount' => 10,
                'price_per_night' => 320,
            ],
            [
                'slug' => 'family-two-room-unit',
                'name' => 'وحدة عائلية غرفتين',
                'location' => 'الرياض - حي الصحافة',
                'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?q=80&w=1200&auto=format&fit=crop',
                'beds' => 2,
                'baths' => 2,
                'area' => 110,
                'rating' => '9.6',
                'discount' => 12,
                'price_per_night' => 360,
            ],
        ];
    }
};
?>
