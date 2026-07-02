<?php

namespace App\Livewire\Tenant\Menu;

use Livewire\Component;

class Index extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $meals = [];

    public function mount(): void
    {
        $this->meals = [
            [
                'id' => 'm1',
                'name' => 'وجبة دجاج مشوي',
                'category' => 'وجبات رئيسية',
                'price' => 32,
                'image' => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?q=80&w=1200&auto=format&fit=crop',
                'options' => ['بطاطس مقلية', 'سلطة خضراء', 'صوص الثوم', 'خبز إضافي'],
            ],
            [
                'id' => 'm2',
                'name' => 'برجر لحم أنجوس',
                'category' => 'وجبات رئيسية',
                'price' => 29,
                'image' => 'https://images.unsplash.com/photo-1550547660-d9450f859349?q=80&w=1200&auto=format&fit=crop',
                'options' => ['جبنة إضافية', 'بصل مكرمل', 'صوص باربكيو', 'مخلل'],
            ],
            [
                'id' => 'm3',
                'name' => 'طبق مشاوي مشكل',
                'category' => 'مشاوي',
                'price' => 48,
                'image' => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?q=80&w=1200&auto=format&fit=crop',
                'options' => ['رز بسمتي', 'خبز تنور', 'طحينة', 'حمص'],
            ],
            [
                'id' => 'm4',
                'name' => 'باستا ألفريدو',
                'category' => 'وجبات رئيسية',
                'price' => 34,
                'image' => 'https://images.unsplash.com/photo-1645112411341-6c4fd023714a?q=80&w=1200&auto=format&fit=crop',
                'options' => ['فطر', 'دجاج إضافي', 'جبنة بارميزان', 'صوص حار'],
            ],
            [
                'id' => 'm5',
                'name' => 'سلطة سيزر',
                'category' => 'مقبلات',
                'price' => 21,
                'image' => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?q=80&w=1200&auto=format&fit=crop',
                'options' => ['دجاج مشوي', 'خبز محمص', 'صوص إضافي', 'جبنة بارميزان'],
            ],
            [
                'id' => 'm6',
                'name' => 'عصير برتقال طازج',
                'category' => 'مشروبات',
                'price' => 14,
                'image' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?q=80&w=1200&auto=format&fit=crop',
                'options' => ['بدون سكر', 'ثلج إضافي', 'نعناع', 'كوب كبير'],
            ],
        ];
    }

    public function render()
    {
        return tenantView('menu.index')->title('قائمة الطعام');
    }
}
