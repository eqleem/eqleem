<?php

namespace App\Livewire\Tenant\Properties;

use Livewire\Component;

class Detail extends Component
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

    public function render()
    {
        return tenantView('properties.detail')->title('تفاصيل العقار');
    }
}
