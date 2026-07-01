<?php

use Livewire\Component;

new class extends Component
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
};
?>

<x-tenant::courses.layout>

<div class="flex items-center justify-between mb-5 px-2">
    <a href="{{route('tenant.courses.index')}}" wire:navigate class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition rotate-180">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="arrow-left" aria-hidden="true" class="lucide lucide-arrow-left w-5 h-5 text-stone-700 "><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
    </a>
    <div class="flex items-center gap-2">
      <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="share" aria-hidden="true" class="lucide lucide-share w-5 h-5 text-stone-700 "><path d="M12 2v13"></path><path d="m16 6-4-4-4 4"></path><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path></svg>
      </button>  
      <button class="w-10 h-10 rounded-full flex items-center justify-center bg-stone-100 hover:bg-stone-200 transition">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" data-lucide="heart" aria-hidden="true" class="lucide lucide-heart h-4 w-4"><path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path></svg> 
    </div>
    </button>
  </div>
  
    <section class="px-3 mb-8 w-full">

 


        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
            <div class="space-y-4">
                <div class="aspect-video bg-stone-100 rounded-2xl overflow-hidden">
                    <img src="{{ $course['image'] }}" alt="{{ $course['title'] }}" class="w-full h-full object-cover">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs text-stone-500 mb-1">مقدم الدورة</p>
                        <p class="text-sm font-semibold text-stone-900">{{ $course['instructor'] }}</p>
                    </div>
                    <div class="rounded-2xl border border-stone-200 bg-stone-50 p-4">
                        <p class="text-xs text-stone-500 mb-1">عدد الساعات</p>
                        <p class="text-sm font-semibold text-stone-900">{{ $course['hours'] }} ساعة تدريبية</p>
                    </div>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between gap-3 mb-4">
                    <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-stone-900">{{ $course['title'] }}</h1>
                    <span class="text-2xl font-bold text-primary-600">{{ $course['price'] }} ر.س</span>
                </div>

                <p class="text-stone-600 leading-8">{{ $course['description'] }}</p>

                <div class="mt-6 flex flex-wrap items-center gap-3">
                    <button class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-6 py-3 text-sm font-bold text-white hover:bg-primary-700 transition">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="text-xl"></iconify-icon>
                        الالتحاق بالكورس
                    </button>
                    <a href="{{ route('tenant.courses.index') }}" wire:navigate class="inline-flex items-center gap-2 rounded-xl border border-stone-300 bg-white px-5 py-3 text-sm font-semibold text-stone-700 hover:bg-stone-50">
                        <iconify-icon icon="solar:arrow-right-linear" class="text-xl"></iconify-icon>
                        الرجوع للدورات
                    </a>
                </div>

                <div class="mt-6 rounded-2xl border border-green-100 bg-green-50 p-4 text-sm text-green-800">
                    بعض الدروس مجانية ويمكن تشغيلها مباشرة بدون شراء الدورة كاملة.
                </div>
            </div>
        </div>
    </section>

    <section class="px-3 pb-8">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-bold text-stone-900">محتوى الكورس</h2>
            <span class="text-sm text-stone-500">الدروس مقسمة حسب الأقسام</span>
        </div>

        <div class="space-y-4">
            @foreach($course['sections'] as $section)
                <div wire:key="section-{{ $course['slug'] }}-{{ $section['id'] }}" class="rounded-2xl border border-stone-200 bg-white p-4 md:p-5">
                    <h3 class="text-base md:text-lg font-bold text-stone-900 mb-4">{{ $section['title'] }}</h3>

                    <div class="space-y-3">
                        @foreach($section['lessons'] as $lesson)
                            <div wire:key="lesson-{{ $course['slug'] }}-{{ $lesson['id'] }}" class="flex flex-col md:flex-row md:items-center gap-3 rounded-xl border border-stone-100 bg-stone-50 p-3">
                                <img src="{{ $lesson['image'] }}" alt="{{ $lesson['title'] }}" class="w-full md:w-44 h-28 object-cover rounded-lg">

                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        <h4 class="font-semibold text-stone-900">{{ $lesson['title'] }}</h4>
                                        @if($lesson['free'])
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-1 text-[11px] font-semibold text-green-700">مجاني</span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-primary-100 px-2 py-1 text-[11px] font-semibold text-primary-700">ضمن الدورة</span>
                                        @endif
                                    </div>

                                    <p class="text-sm text-stone-500 inline-flex items-center gap-1">
                                        <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-base"></iconify-icon>
                                        {{ $lesson['minutes'] }} دقيقة
                                    </p>
                                </div>

                                <button class="inline-flex items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $lesson['free'] ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-stone-200 text-stone-500 cursor-not-allowed' }}">
                                    <iconify-icon icon="solar:play-circle-bold-duotone" class="text-lg"></iconify-icon>
                                    {{ $lesson['free'] ? 'تشغيل' : 'مقفل' }}
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</x-tenant::courses.layout>
