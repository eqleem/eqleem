<?php

namespace App\Livewire\Tenant\PropertiesRental;

use Livewire\Component;

class Detail extends Component
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

    public function render()
    {
        return tenantView('properties-rental.detail')->title('تفاصيل الوحدة');
    }
}
