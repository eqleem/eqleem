<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ThemeSeeder extends Seeder
{
    /**
     * @var list<array{
     *     slug: string,
     *     name: string,
     *     label_ar: string,
     *     sort: int,
     *     active: bool,
     *     designer: string,
     *     price: int,
     *     version: string,
     *     description: string,
     *     features: list<string>
     * }>
     */
    private const THEMES = [
        [
            'slug' => 'default',
            'name' => 'إفتراضي',
            'label_ar' => 'إفتراضي',
            'sort' => 1,
            'active' => true,
            'designer' => 'فريق إقليم',
            'price' => 0,
            'version' => '2.1.0',
            'description' => 'قالب متكامل بتصميم عصري يلائم أغلب الأنشطة التجارية، مع توازن واضح بين المحتوى والصور ومساحات عرض المنتجات والخدمات.',
            'features' => [
                'تصميم متجاوب بالكامل للجوال والكمبيوتر',
                'ألوان وخطوط قابلة للتخصيص بسهولة',
                'أقسام جاهزة للمتجر والمدونة والخدمات',
                'معاينة سريعة قبل النشر',
                'أداء خفيف وتحميل سريع',
            ],
        ],
        [
            'slug' => 'minimal',
            'name' => 'بسيط',
            'label_ar' => 'بسيط',
            'sort' => 3,
            'active' => false,
            'designer' => 'استوديو بسيط',
            'price' => 99,
            'version' => '1.4.2',
            'description' => 'قالب بسيط بتركيز بصري مرتفع ومساحات بيضاء واسعة، مثالي للعلامات التي تريد حضوراً أنيقاً وواضحاً دون تشتيت.',
            'features' => [
                'مظهر نظيف وبسيط مع تركيز على المحتوى',
                'مساحات بيضاء مدروسة وهرمية واضحة',
                'مثالي للمعارض الشخصية والخدمات الاحترافية',
                'تخصيص الألوان والشعار بسرعة',
                'جاهز للعرض على الجوال أولاً',
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::THEMES as $theme) {
            Theme::query()->updateOrCreate(
                ['slug' => $theme['slug']],
                [
                    'uuid' => Str::uuid(),
                    'name' => $theme['name'],
                    'meta' => [
                        'label_ar' => $theme['label_ar'],
                        'preview' => 'assets/wjeez/themes/'.$theme['slug'].'.svg',
                        'gallery' => $this->galleryFor($theme['slug']),
                        'designer' => $theme['designer'],
                        'price' => $theme['price'],
                        'version' => $theme['version'],
                        'description' => $theme['description'],
                        'features' => $theme['features'],
                    ],
                    'type' => 'all',
                    'app' => 'all',
                    'active' => $theme['active'],
                    'public' => true,
                    'sort' => $theme['sort'],
                ],
            );
        }
    }

    /**
     * @return list<string>
     */
    private function galleryFor(string $slug): array
    {
        $shots = ['home', 'store', 'blog', 'services', 'contact'];

        return array_map(
            fn (string $shot): string => 'https://api.dicebear.com/10.x/stripes/svg?seed='.$slug.'-'.$shot.'&backgroundColor=f5f5f4,e7e5e4,d6d3d1',
            $shots,
        );
    }
}
