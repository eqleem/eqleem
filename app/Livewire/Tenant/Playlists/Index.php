<?php

namespace App\Livewire\Tenant\Playlists;

use Livewire\Component;

class Index extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $playlists = [];

    public function mount(): void
    {
        $this->playlists = [
            [
                'slug' => 'finishing-video',
                'type' => 'video',
                'name' => 'تشطيبات داخلية بالفيديو',
                'description' => 'سلسلة فيديو عملية من المعاينة حتى التسليم النهائي بخطوات مرتبة وواضحة.',
                'image' => 'https://images.unsplash.com/photo-1516321497487-e288fb19713f?q=80&w=1200&auto=format&fit=crop',
                'items_count' => 3,
            ],
            [
                'slug' => 'finishing-audio',
                'type' => 'audio',
                'name' => 'بودكاست نصائح التشطيبات',
                'description' => 'حلقات صوتية قصيرة تساعدك على اتخاذ قرارات أسرع وأدق أثناء التنفيذ.',
                'image' => 'https://images.unsplash.com/photo-1478737270239-2f02b77fc618?q=80&w=1200&auto=format&fit=crop',
                'items_count' => 3,
            ],
        ];
    }

    public function render()
    {
        return tenantView('playlists.index')->title('قوائم التشغيل');
    }
}
