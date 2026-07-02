<x-tenant::pages.layout>
    <x-tenant::breadcrumb :links="[['url' => null, 'title' => 'تحديثات المنصة']]" />
    <x-tenant::page-title title="تحديثات المنصة" desc="التغييرات، الإصلاحات، والتحسينات عبر الإصدارات المختلفة." />
    <section class="p-3">
        <main class=" ">
            
            <div class="space-y-24 mt-6">
                @foreach ($releases as $release)
                    <article id="{{ $release['id'] }}" class="scroll-mt-24">
                        <div class="mb-4 flex flex-wrap items-center gap-2 text-xs text-stone-500 border-b-2 border-dotted border-stone-300 pb-4">
                            <span class="rounded-md bg-stone-200/30 px-2 py-1 font-semibold text-stone-700">إصدار</span>
                            <span>•</span>
                            <span>{{ $release['date'] }}</span>
                        </div>

                        <div class="space-y-2 bg-stone-200/30 px-4 py-2 rounded-md">
                        <h2 class="text-2xl font-black text-stone-900 md:text-3xl">{{ $release['title'] }}</h2>
                        <p class="mt-2 max-w-4xl text-sm leading-8 text-stone-500">{{ $release['summary'] }}</p>
                        </div>

                        <div class="mt-8 space-y-8">
                            @foreach ($release['sections'] as $section)
                                <section class="space-y-4">
                                    <div class="inline-flex rounded-md bg-primary-100 px-2.5 py-1 text-xs font-semibold text-stone-700">
                                        {{ $section['label'] }}
                                    </div>

                                    <div class="space-y-4">
                                        @foreach ($section['items'] as $item)
                                            <div class="grid grid-cols-1 gap-3 md:grid-cols-[220px_minmax(0,1fr)] md:gap-5">
                                                <h3 class="text-sm font-semibold text-stone-800 Xbg-stone-100 px-2 py-1 rounded-md inline-block">{{ $item['title'] }}</h3>
                                                <p class="text-sm leading-7 text-stone-500 xbg-stone-100 px-2 py-1 rounded-md w-full">{{ $item['description'] }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </main>
    </section>
</x-tenant::pages.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, array{id: string, date: string, title: string, summary: string, sections: array<int, array{label: string, items: array<int, array{title: string, description: string}>}>}> */
    public array $releases = [
        [
            'id' => 'release-2026-05-14',
            'date' => '14 مايو 2026',
            'title' => 'مايو 2026',
            'summary' => 'صفحات هبوط جديدة، إعادة إرسال المنشورات، تحسينات كبيرة على التنقل، وتحديثات واضحة في خطط الاشتراك والفوترة.',
            'sections' => [
                [
                    'label' => 'جديد',
                    'items' => [
                        [
                            'title' => 'صفحات الهبوط',
                            'description' => 'أنشئ صفحات مستقلة عبر محرر بلوكات مرن بالسحب والإفلات، ثم انشرها على نطاقك مباشرة.',
                        ],
                        [
                            'title' => 'إعادة إرسال المنشورات',
                            'description' => 'يمكنك إعادة إرسال منشور منشور سابقًا للمشتركين الذين لم يفتحوه من أول مرة بضغطة واحدة. يمكنك إعادة إرسال منشور منشور سابقًا للمشتركين الذين لم يفتحوه من أول مرة بضغطة واحدة',
                        ],
                        [
                            'title' => 'AI Strategist في Insights',
                            'description' => 'أصبح المساعد التحليلي متاحًا داخل Insights مع سياق بياناتك لأسئلة نمو أدق.',
                        ],
                        [
                            'title' => 'إعادة تصميم الخطط والفوترة',
                            'description' => 'عرض أوضح للأسعار ودورات الفوترة قبل الترقية أو التخفيض أو تغيير نوع الدفع.',
                        ],
                        [
                            'title' => 'تكامل Google Analytics',
                            'description' => 'أضف معرف GA4 مرة واحدة من الإعدادات ليتم حقن التتبع تلقائيًا في الصفحات.',
                        ],
                    ],
                ],
                [
                    'label' => 'تحسين',
                    'items' => [
                        [
                            'title' => 'المكتبة هي الصفحة الافتراضية',
                            'description' => 'تم تحديث هيكلة المعلومات لتصبح المكتبة المدخل الرئيسي لكل المحتوى المنشور والمجدول.',
                        ],
                        [
                            'title' => 'استجابة أفضل للموبايل',
                            'description' => 'تحسين شامل للمسافات والمكونات على الشاشات الصغيرة لعرض أكثر اتزانًا.',
                        ],
                        [
                            'title' => 'تحسينات RTL',
                            'description' => 'تحسينات أدق في اتجاه اليمين إلى اليسار عبر المحرر والمعاينة وصفحات النشر.',
                        ],
                    ],
                ],
                [
                    'label' => 'إصلاح',
                    'items' => [
                        [
                            'title' => 'معالجة مشاكل Slug غير ASCII',
                            'description' => 'إصلاح توليد Slug تلقائيًا عند استخدام أحرف غير إنجليزية لتقليل أخطاء الروابط.',
                        ],
                        [
                            'title' => 'ثبات أكبر للأتمتة',
                            'description' => 'حل مشاكل متفرقة في تدفق الأتمتة وربط المشغلات لتحسين الاعتمادية العامة.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'release-2026-04-29',
            'date' => '29 أبريل 2026',
            'title' => 'أبريل 2026',
            'summary' => 'تحديثات على الهوية البصرية للمحتوى، تحسينات في الخطوط، وميزات جديدة للروابط والنماذج المضمّنة.',
            'sections' => [
                [
                    'label' => 'جديد',
                    'items' => [
                        [
                            'title' => 'Bio Hub',
                            'description' => 'صفحة واحدة لكل مساحة عمل تضم روابطك ومحتواك ونموذج الاشتراك في مكان واحد.',
                        ],
                        [
                            'title' => 'بطاقات اجتماعية مخصصة',
                            'description' => 'رفع صورة OG مخصصة للمدونة أو المنشورات لتجربة مشاركة أقوى.',
                        ],
                        [
                            'title' => 'خطوط مخصصة للمدونة',
                            'description' => 'إمكانية رفع الخطوط وتطبيقها مباشرة لتحسين الهوية البصرية للمحتوى.',
                        ],
                    ],
                ],
                [
                    'label' => 'تحسين',
                    'items' => [
                        [
                            'title' => 'Hero بتخصيص أعلى',
                            'description' => 'خيارات أوسع لبطل الصفحة مع التحكم بعرض القسم وإظهار دلائل الثقة.',
                        ],
                        [
                            'title' => 'تحسين تجربة بناء الصفحات',
                            'description' => 'تبسيط مسار العمل داخل المحرر لسرعة أعلى أثناء إنشاء صفحات المحتوى.',
                        ],
                    ],
                ],
                [
                    'label' => 'إصلاح',
                    'items' => [
                        [
                            'title' => 'استقرار معاينة المدونة',
                            'description' => 'إصلاح مشاكل المزامنة في معاينة المدونة أثناء التبديل بين الأوضاع المختلفة.',
                        ],
                    ],
                ],
            ],
        ],
        [
            'id' => 'release-2026-06-01',
            'date' => '1 يونيو 2026',
            'title' => 'يونيو 2026',
            'summary' => 'إطلاق تحسينات على بنية التحديثات وتجربة العرض لتسهيل متابعة كل جديد حسب التصنيف.',
            'sections' => [
                [
                    'label' => 'جديد',
                    'items' => [
                        [
                            'title' => 'عرض التحديثات حسب التاريخ',
                            'description' => 'أصبح بإمكانك متابعة الإصدارات في أقسام واضحة لكل شهر/تاريخ بدل السرد المختلط.',
                        ],
                    ],
                ],
                [
                    'label' => 'تحسين',
                    'items' => [
                        [
                            'title' => 'تنظيم أوضح للمحتوى',
                            'description' => 'ترتيب العناصر داخل كل إصدار بطريقة تجعل قراءة العناوين والأوصاف أسرع وأكثر وضوحًا.',
                        ],
                    ],
                ],
                [
                    'label' => 'إصلاح',
                    'items' => [
                        [
                            'title' => 'تحسين التناسق بين الأقسام',
                            'description' => 'إصلاح تفاوتات بسيطة في المسافات بين البطاقات والعناوين في العرض العام.',
                        ],
                    ],
                ],
            ],
        ],
    ];
};
?>
