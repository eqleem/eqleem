<?php

namespace App\Livewire\Tenant\Playlists;

use Illuminate\Support\Arr;
use Livewire\Component;

class Detail extends Component
{
    /** @var array<string, mixed> */
    public array $playlist = [];

    /** @var array<int, array<string, mixed>> */
    protected array $playlists = [
        [
            'slug' => 'finishing-video',
            'type' => 'video',
            'name' => 'تشطيبات داخلية بالفيديو',
            'description' => 'سلسلة فيديو عملية من المعاينة حتى التسليم النهائي بخطوات مرتبة وواضحة.',
            'image' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=1200&auto=format&fit=crop',
            'items' => [
                [
                    'id' => 'v1',
                    'name' => 'مقدمة المشروع وخطة التنفيذ',
                    'description' => 'نظرة شاملة على مراحل العمل وأدوات البداية.',
                    'image' => 'https://images.unsplash.com/photo-1529074963764-98f45c47344b?q=80&w=1200&auto=format&fit=crop',
                    'media' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/BigBuckBunny.mp4',
                ],
                [
                    'id' => 'v2',
                    'name' => 'تجهيز الأسطح قبل التشطيب',
                    'description' => 'أفضل الممارسات لتجهيز الجدران والأرضيات قبل التركيب.',
                    'image' => 'https://images.unsplash.com/photo-1505691938895-1758d7feb511?q=80&w=1200&auto=format&fit=crop',
                    'media' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ElephantsDream.mp4',
                ],
                [
                    'id' => 'v3',
                    'name' => 'خطوات الإنهاء وضبط الجودة',
                    'description' => 'قائمة تدقيق نهائية لضمان جودة التسليم.',
                    'image' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?q=80&w=1200&auto=format&fit=crop',
                    'media' => 'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerBlazes.mp4',
                ],
            ],
        ],
        [
            'slug' => 'finishing-audio',
            'type' => 'audio',
            'name' => 'بودكاست نصائح التشطيبات',
            'description' => 'حلقات صوتية قصيرة تساعدك على اتخاذ قرارات أسرع وأدق أثناء التنفيذ.',
            'image' => 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?q=80&w=1200&auto=format&fit=crop',
            'items' => [
                [
                    'id' => 'a1',
                    'name' => 'اختيار المواد المناسبة',
                    'description' => 'كيف توازن بين الجودة والتكلفة في المواد.',
                    'image' => 'https://images.unsplash.com/photo-1461749280684-dccba630e2f6?q=80&w=1200&auto=format&fit=crop',
                    'media' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-1.mp3',
                ],
                [
                    'id' => 'a2',
                    'name' => 'إدارة فريق التنفيذ',
                    'description' => 'تقنيات تنظيم الفريق وتوزيع المهام اليومية.',
                    'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
                    'media' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-2.mp3',
                ],
                [
                    'id' => 'a3',
                    'name' => 'تفادي الأخطاء الشائعة',
                    'description' => 'أخطاء متكررة وكيف تمنعها مبكرا قبل تضخم التكلفة.',
                    'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=1200&auto=format&fit=crop',
                    'media' => 'https://www.soundhelix.com/examples/mp3/SoundHelix-Song-3.mp3',
                ],
            ],
        ],
    ];

    public function mount(string $slug): void
    {
        $playlist = collect($this->playlists)->firstWhere('slug', $slug);

        abort_if($playlist === null, 404);

        $this->playlist = Arr::only($playlist, ['slug', 'type', 'name', 'description', 'image', 'items']);
    }

    public function render()
    {
        return tenantView('playlists.detail')->title('تفاصيل القائمة');
    }
}
