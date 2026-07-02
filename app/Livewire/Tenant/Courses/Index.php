<?php

namespace App\Livewire\Tenant\Courses;

use Livewire\Component;

class Index extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $courses = [];

    public function mount(): void
    {
        $this->courses = [
            [
                'slug' => 'parquet-masterclass',
                'title' => 'احتراف تركيب الباركيه',
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=700&auto=format&fit=crop',
                'hours' => 18,
                'price' => 349,
                'level' => 'متوسط',
                'category' => 'أعمال الباركيه',
            ],
            [
                'slug' => 'marble-alternative-system',
                'title' => 'بديل الرخام من الصفر',
                'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=1200&auto=format&fit=crop',
                'hours' => 14,
                'price' => 279,
                'level' => 'مبتدئ',
                'category' => 'بديل الرخام',
            ],
            [
                'slug' => 'wood-panels-workshop',
                'title' => 'ورشة بديل الخشب والشيبورد',
                'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1200&auto=format&fit=crop',
                'hours' => 22,
                'price' => 420,
                'level' => 'متقدم',
                'category' => 'بديل الخشب',
            ],
            [
                'slug' => 'interior-finishing-business',
                'title' => 'إدارة مشاريع التشطيب للعملاء',
                'image' => 'https://images.unsplash.com/photo-1464890100898-a385f744067f?q=80&w=1200&auto=format&fit=crop',
                'hours' => 10,
                'price' => 199,
                'level' => 'متوسط',
                'category' => 'إدارة المشاريع',
            ],
        ];
    }

    public function render()
    {
        return tenantView('courses.index')->title('الدورات');
    }
}
