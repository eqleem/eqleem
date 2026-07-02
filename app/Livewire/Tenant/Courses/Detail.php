<?php

namespace App\Livewire\Tenant\Courses;

use Livewire\Component;

class Detail extends Component
{
    /** @var array<string, mixed> */
    public array $course = [];

    public function mount(string $slug): void
    {
        $courses = [
            'parquet-masterclass' => [
                'slug' => 'parquet-masterclass',
                'title' => 'احتراف تركيب الباركيه',
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=700&auto=format&fit=crop',
                'instructor' => 'م. فهد العتيبي',
                'hours' => 18,
                'price' => 349,
                'description' => 'دورة عملية لتأسيسك في كل مراحل تركيب الباركيه: تجهيز الأرضية، اختيار السماكات، أدوات القص والتركيب، حلول الفواصل، والمعالجة النهائية لضمان جودة تدوم.',
                'sections' => [
                    [
                        'id' => 's1',
                        'title' => 'الأساسيات والتحضير',
                        'lessons' => [
                            ['id' => 'l1', 'title' => 'مقدمة الدورة وخارطة التعلم', 'minutes' => 12, 'image' => 'https://images.unsplash.com/photo-1434030216411-0b793f4b4173?q=80&w=700&auto=format&fit=crop', 'free' => true],
                            ['id' => 'l2', 'title' => 'أدوات الفني المحترف', 'minutes' => 18, 'image' => 'https://images.unsplash.com/photo-1616046229478-9901c5536a45?q=80&w=700&auto=format&fit=crop', 'free' => true],
                            ['id' => 'l3', 'title' => 'فحص الأرضية قبل البدء', 'minutes' => 16, 'image' => 'https://images.unsplash.com/photo-1621905251918-48416bd8575a?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                    [
                        'id' => 's2',
                        'title' => 'التطبيق العملي',
                        'lessons' => [
                            ['id' => 'l4', 'title' => 'تركيب أول صف بطريقة صحيحة', 'minutes' => 24, 'image' => 'https://images.unsplash.com/photo-1581578731548-c64695cc6952?q=80&w=700&auto=format&fit=crop', 'free' => false],
                            ['id' => 'l5', 'title' => 'حل الزوايا والمداخل', 'minutes' => 20, 'image' => 'https://images.unsplash.com/photo-1556912998-c57cc6b63cd7?q=80&w=700&auto=format&fit=crop', 'free' => false],
                            ['id' => 'l6', 'title' => 'تشطيبات الحواف والنعلات', 'minutes' => 14, 'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                ],
            ],
            'marble-alternative-system' => [
                'slug' => 'marble-alternative-system',
                'title' => 'بديل الرخام من الصفر',
                'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=1400&auto=format&fit=crop',
                'instructor' => 'م. لمى القحطاني',
                'hours' => 14,
                'price' => 279,
                'description' => 'تعلم اختيار خامات بديل الرخام، قواعد القص واللصق، توزيع الفواصل الجمالية، ومعالجة العيوب الشائعة للوصول لنتيجة فاخرة بتكلفة عملية.',
                'sections' => [
                    [
                        'id' => 's1',
                        'title' => 'الخامات والتحضير',
                        'lessons' => [
                            ['id' => 'l1', 'title' => 'أنواع ألواح بديل الرخام', 'minutes' => 15, 'image' => 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=700&auto=format&fit=crop', 'free' => true],
                            ['id' => 'l2', 'title' => 'القياس الصحيح قبل القص', 'minutes' => 17, 'image' => 'https://images.unsplash.com/photo-1595514535415-dae2c7f5e8ef?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                    [
                        'id' => 's2',
                        'title' => 'تنفيذ الجدران والزوايا',
                        'lessons' => [
                            ['id' => 'l3', 'title' => 'تجهيز السطح ومعالجة العيوب', 'minutes' => 19, 'image' => 'https://images.unsplash.com/photo-1617098474202-0d0d8b47c8d1?q=80&w=700&auto=format&fit=crop', 'free' => false],
                            ['id' => 'l4', 'title' => 'تركيب الزوايا والنهائيات', 'minutes' => 21, 'image' => 'https://images.unsplash.com/photo-1616046229478-9901c5536a45?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                ],
            ],
            'wood-panels-workshop' => [
                'slug' => 'wood-panels-workshop',
                'title' => 'ورشة بديل الخشب والشيبورد',
                'image' => 'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?q=80&w=1400&auto=format&fit=crop',
                'instructor' => 'م. سارة السالم',
                'hours' => 22,
                'price' => 420,
                'description' => 'برنامج شامل لتصميم وتنفيذ جدران بديل الخشب والشيبورد بلمسة احترافية، من التخطيط إلى التركيب النهائي مع نماذج تطبيقية في المساحات السكنية.',
                'sections' => [
                    [
                        'id' => 's1',
                        'title' => 'التخطيط والتصميم',
                        'lessons' => [
                            ['id' => 'l1', 'title' => 'قراءة مخطط الحائط', 'minutes' => 11, 'image' => 'https://images.unsplash.com/photo-1497366754035-f200968a6e72?q=80&w=700&auto=format&fit=crop', 'free' => true],
                            ['id' => 'l2', 'title' => 'توزيع الفواصل والمقاسات', 'minutes' => 20, 'image' => 'https://images.unsplash.com/photo-1484154218962-a197022b5858?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                    [
                        'id' => 's2',
                        'title' => 'التنفيذ النهائي',
                        'lessons' => [
                            ['id' => 'l3', 'title' => 'التركيب باستخدام الشرائح', 'minutes' => 26, 'image' => 'https://images.unsplash.com/photo-1615874959474-d609969a20ed?q=80&w=700&auto=format&fit=crop', 'free' => false],
                            ['id' => 'l4', 'title' => 'المعالجة واللمسات النهائية', 'minutes' => 23, 'image' => 'https://images.unsplash.com/photo-1616594039964-c39b7db8db9d?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                ],
            ],
            'interior-finishing-business' => [
                'slug' => 'interior-finishing-business',
                'title' => 'إدارة مشاريع التشطيب للعملاء',
                'image' => 'https://images.unsplash.com/photo-1464890100898-a385f744067f?q=80&w=1400&auto=format&fit=crop',
                'instructor' => 'م. نورة الحربي',
                'hours' => 10,
                'price' => 199,
                'description' => 'تعرّف على إدارة مشروع التشطيب من أول تواصل مع العميل حتى التسليم، مع نماذج تسعير وعقود ومتابعة تنفيذ تساعدك في رفع الربحية وتقليل الهدر.',
                'sections' => [
                    [
                        'id' => 's1',
                        'title' => 'إدارة العميل والتسعير',
                        'lessons' => [
                            ['id' => 'l1', 'title' => 'تحديد نطاق العمل باحتراف', 'minutes' => 13, 'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?q=80&w=700&auto=format&fit=crop', 'free' => true],
                            ['id' => 'l2', 'title' => 'تسعير البنود بدون خسارة', 'minutes' => 19, 'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                    [
                        'id' => 's2',
                        'title' => 'المتابعة والتسليم',
                        'lessons' => [
                            ['id' => 'l3', 'title' => 'متابعة الجدول الزمني', 'minutes' => 14, 'image' => 'https://images.unsplash.com/photo-1524758631624-e2822e304c36?q=80&w=700&auto=format&fit=crop', 'free' => false],
                            ['id' => 'l4', 'title' => 'قائمة فحص التسليم النهائي', 'minutes' => 16, 'image' => 'https://images.unsplash.com/photo-1517048676732-d65bc937f952?q=80&w=700&auto=format&fit=crop', 'free' => false],
                        ],
                    ],
                ],
            ],
        ];

        if (! array_key_exists($slug, $courses)) {
            abort(404);
        }

        $this->course = $courses[$slug];
    }

    public function render()
    {
        return tenantView('courses.detail')->title('تفاصيل الدورة');
    }
}
