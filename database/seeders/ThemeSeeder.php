<?php

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ThemeSeeder extends Seeder
{
    /**
     * @var list<array{slug: string, name: string, label_ar: string, sort: int, designer: string, price: int}>
     */
    private const THEMES = [
        ['slug' => 'default', 'name' => 'إفتراضي', 'label_ar' => 'إفتراضي', 'sort' => 1, 'designer' => 'فريق إقليم', 'price' => 0],
        ['slug' => 'minimal', 'name' => 'بسيط', 'label_ar' => 'بسيط', 'sort' => 3, 'designer' => 'استوديو بسيط', 'price' => 99],
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
                        'gallery' => [
                            'assets/wjeez/themes/'.$theme['slug'].'.svg',
                        ],
                        'designer' => $theme['designer'],
                        'price' => $theme['price'],
                    ],
                    'type' => 'all',
                    'app' => 'all',
                    'active' => true,
                    'public' => true,
                    'sort' => $theme['sort'],
                ],
            );
        }
    }
}
