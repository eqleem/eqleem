<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Resume extends Component
{
    public string $fullName = 'أحمد محمد';

    public string $jobTitle = 'مهندس تشطيبات وديكور داخلي';

    public string $location = 'الرياض، المملكة العربية السعودية';

    public string $email = 'ahmad@example.com';

    public string $phone = '+966 55 555 5555';

    public string $phoneDial = '+966555555555';

    public string $education = 'بكالوريوس هندسة مدنية - جامعة الملك سعود';

    public string $educationPeriod = '2014 - 2018';

    public string $summary = 'متخصص في إدارة وتنفيذ مشاريع التشطيبات الداخلية السكنية والتجارية، مع خبرة عملية في التخطيط، ضبط الجودة، إدارة الفرق والموردين، وتقديم حلول تصميمية عملية تراعي الميزانية والجدول الزمني.';

    /** @var array<int, string> */
    public array $skills = [
        'إدارة مشاريع التشطيبات',
        'الديكور الداخلي',
        'قراءة المخططات',
        'ضبط الجودة',
        'إدارة فرق التنفيذ',
        'التسعير والمناقصات',
        'AutoCAD',
        'SketchUp',
    ];

    /** @var array<int, array<string, string>> */
    public array $experiences = [
        [
            'title' => 'مشرف تشطيبات أول',
            'company' => 'شركة أبعاد البيت',
            'period' => '2022 - الآن',
            'description' => 'قيادة تنفيذ أكثر من 35 مشروعًا سكنيًا وتجاريًا، تحسين دورة التسليم بنسبة 22%، وإدارة فرق التنفيذ بما يضمن الالتزام بالمواصفات والجداول الزمنية.',
        ],
        [
            'title' => 'مهندس موقع',
            'company' => 'شركة دار الإتقان للمقاولات',
            'period' => '2019 - 2022',
            'description' => 'الإشراف اليومي على الأعمال المدنية والمعمارية، إعداد تقارير الإنجاز، والتنسيق مع الموردين لضمان توفر المواد بالجودة المطلوبة.',
        ],
    ];

    /** @var array<int, array<string, string>> */
    public array $projects = [
        [
            'name' => 'تشطيب فيلا سكنية - حي الياسمين',
            'description' => 'تنفيذ كامل لأعمال الأرضيات، الجدران، الإضاءة، وتفاصيل الديكور خلال 14 أسبوعًا.',
        ],
        [
            'name' => 'تطوير صالة عرض تجارية',
            'description' => 'إعادة تصميم وتنفيذ صالة عرض بمساحة 420 م2 مع رفع تجربة العملاء وجودة العرض.',
        ],
        [
            'name' => 'تجديد مكاتب إدارية',
            'description' => 'إدارة عملية تجديد مكاتب شركة تقنية مع الحفاظ على استمرارية العمل أثناء التنفيذ.',
        ],
        [
            'name' => 'مشروع وحدات ضيافة فندقية',
            'description' => 'تنفيذ حلول تشطيب موحدة لـ 24 وحدة ضيافة مع معايير جودة عالية وتسليم مرحلي.',
        ],
    ];

    public function render()
    {
        return tenantView('pages.resume')->title('السيرة الذاتية');
    }
}
