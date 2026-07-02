<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Branches extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $branches = [];

    public function mount(): void
    {
        $this->branches = [
            [
                'slug' => 'riyadh-main',
                'name' => 'فرع الرياض - المروج',
                'address' => 'الرياض، حي المروج، طريق الأمير تركي بن عبدالعزيز الأول',
                'map_embed_url' => 'https://www.google.com/maps?q=24.774265,46.738586&z=14&output=embed',
                'google_map_url' => 'https://maps.google.com/?q=24.774265,46.738586',
                'working_hours' => [
                    ['days' => 'السبت - الخميس', 'time' => '9:00 ص - 11:00 م'],
                    ['days' => 'الجمعة', 'time' => '4:00 م - 11:30 م'],
                ],
                'phones' => [
                    ['label' => 'المبيعات', 'display' => '050 123 4567', 'tel' => '+966501234567'],
                    ['label' => 'خدمة العملاء', 'display' => '011 555 7788', 'tel' => '+966115557788'],
                ],
            ],
            [
                'slug' => 'jeddah-north',
                'name' => 'فرع جدة - أبحر الشمالية',
                'address' => 'جدة، أبحر الشمالية، طريق الأمير عبدالله الفيصل',
                'map_embed_url' => 'https://www.google.com/maps?q=21.543333,39.172779&z=14&output=embed',
                'google_map_url' => 'https://maps.google.com/?q=21.543333,39.172779',
                'working_hours' => [
                    ['days' => 'السبت - الخميس', 'time' => '10:00 ص - 10:30 م'],
                    ['days' => 'الجمعة', 'time' => '5:00 م - 11:00 م'],
                ],
                'phones' => [
                    ['label' => 'الاستقبال', 'display' => '053 765 4321', 'tel' => '+966537654321'],
                    ['label' => 'واتساب الفرع', 'display' => '054 987 1212', 'tel' => '+966549871212'],
                ],
            ],
            [
                'slug' => 'dammam-central',
                'name' => 'فرع الدمام - الشاطئ',
                'address' => 'الدمام، حي الشاطئ، طريق الخليج',
                'map_embed_url' => 'https://www.google.com/maps?q=26.420682,50.088795&z=14&output=embed',
                'google_map_url' => 'https://maps.google.com/?q=26.420682,50.088795',
                'working_hours' => [
                    ['days' => 'الأحد - الخميس', 'time' => '9:30 ص - 10:00 م'],
                    ['days' => 'السبت', 'time' => '11:00 ص - 9:30 م'],
                ],
                'phones' => [
                    ['label' => 'المعرض', 'display' => '055 112 3344', 'tel' => '+966551123344'],
                    ['label' => 'الدعم الفني', 'display' => '013 822 9090', 'tel' => '+966138229090'],
                ],
            ],
        ];
    }

    public function render()
    {
        return tenantView('pages.branches')->title('فروعنا');
    }
}
