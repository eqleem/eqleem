<?php

namespace App\Livewire\Tenant\PropertiesRental;

use Livewire\Component;

class Index extends Component
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

    public function render()
    {
        return tenantView('properties-rental.index')->title('تأجير الوحدات');
    }
}
