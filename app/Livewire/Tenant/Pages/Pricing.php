<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Pricing extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $plans = [];

    public function mount(): void
    {
        $this->plans = [
            [
                'slug' => 'startup',
                'name' => 'البداية',
                'description' => 'مناسبة للمشاريع الصغيرة التي تريد الانطلاق بسرعة.',
                'monthly_price' => 'مجانا',
                'yearly_price' => 'مجانا',
                'is_custom' => false,
                'featured' => false,
                'cta' => 'ابدأ مجانا',
                'yearly_cta' => 'ابدأ مجانا',
                'features' => ['حتى 10 أعضاء', 'حساب مشرف واحد', 'مساحة تخزين 5 جيجابايت'],
            ],
            [
                'slug' => 'pro',
                'name' => 'الاحترافية',
                'description' => 'مناسبة للأعمال المتنامية التي تحتاج مزايا أوسع.',
                'monthly_price' => '$99',
                'yearly_price' => '$79',
                'is_custom' => false,
                'featured' => true,
                'cta' => 'ابدأ تجربة 30 يوما',
                'yearly_cta' => 'وفر مع الخطة السنوية',
                'features' => ['أعضاء غير محدودين', 'حسابات مشرف غير محدودة', 'مساحة 500 جيجابايت', 'دومين مخصص', 'وصول API'],
            ],

            [
                'slug' => 'enterprise',
                'name' => 'المؤسسات',
                'description' => 'حلول متقدمة للشركات الكبيرة واحتياجات التوسع.',
                'monthly_price' => '',
                'yearly_price' => '',
                'is_custom' => true,
                'featured' => false,
                'cta' => 'تواصل للمزيد',
                'yearly_cta' => 'تواصل للمزيد',
                'features' => ['أعضاء غير محدودين', 'حسابات مشرف غير محدودة', 'حتى 5 تيرابايت تخزين', 'دومين مخصص', 'وصول API', 'كل التكاملات', 'كل الويدجت', 'دعم محادثة مباشرة', 'استيراد جماعي'],
            ],
        ];
    }

    public function render()
    {
        return tenantView('pages.pricing')->title('الباقات والأسعار');
    }
}
