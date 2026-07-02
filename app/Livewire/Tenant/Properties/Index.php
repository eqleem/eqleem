<?php

namespace App\Livewire\Tenant\Properties;

use Livewire\Component;

class Index extends Component
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

    public function render()
    {
        return tenantView('properties.index')->title('العقارات');
    }
}
