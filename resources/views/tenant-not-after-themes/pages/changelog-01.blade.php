<x-tenant::layout >
    <section class="bg-stone-50 px-3 py-4 md:rounded-3xl md:px-8 md:py-10">
        <div class="">
            <main class="space-y-10  ">
                <header class="space-y-3">
                    <h1 class=" text-2xl font-black text-stone-900 md:text-4xl"> تحديثات المنصة </h1>
                    <p class=" text-sm text-stone-500 md:text-base">التغييرات، الإصلاحات، والتحسينات عبر الإصدارات المختلفة.</p>
                </header>

                <div class="space-y-12">
                    @foreach ($updates as $update)
                        <article id="{{ $update['id'] }}" class="scroll-mt-24 border-b-2 border-b-stone-200/50 pb-10 last:border-b-0">
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-[170px_minmax(0,1fr)] md:gap-12">
                                <div class="md:pt-2">
                                    <div class="inline-flex rounded-lg bg-primary-100/70 px-3 py-1.5 text-sm font-bold text-stone-800">
                                        {{ $update['date'] }}
                                    </div>

                                    <ul class="mt-4 text-sm leading-6 text-stone-600 flex flex-wrap gap-1">
                                        @foreach ($update['highlights'] as $highlight)
                                            <li class="text-xs bg-stone-200/50 px-1.5 py-1 rounded-md">{{ $highlight }}</li>
                                        @endforeach
                                    </ul>

                                    {{-- <div class="mt-4 flex flex-wrap gap-2">
                                        @foreach ($update['labels'] as $label)
                                            <span class="rounded-md bg-stone-200/70 px-2 py-1 text-xs font-medium text-stone-700">{{ $label }}</span>
                                        @endforeach
                                    </div> --}}
                                </div>

                                <div class="space-y-10">
                                    @foreach ($update['changes'] as $change)
                                        <section class="space-y-1">
                                            <h2 class="text-lg font-black leading-tight text-stone-900">{{ $change['title'] }}</h2>
                                            <p class="text-base leading-8 text-stone-600">
                                                {{ $change['description'] }}
                                            </p>
                                        </section>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </main>

            {{-- <aside class="lg:col-span-3">
                <div class="space-y-6 lg:sticky lg:top-24">
                 
                    <div class="rounded-xl border border-stone-200 bg-white p-4">
                        <h3 class="mb-4  text-sm font-bold text-stone-900">في هذه الصفحة</h3>
                        <ul class="space-y-1 border-r border-stone-200 pr-3">
                            @foreach ($updates as $index => $update)
                                <li>
                                    <a
                                        href="#{{ $update['id'] }}"
                                        class="block rounded-md px-2 py-1.5 text-sm transition {{ $index === 0 ? 'font-bold text-stone-900' : 'text-stone-500 hover:bg-stone-100 hover:text-stone-800' }}"
                                    >
                                        {{ $update['date'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </aside> --}}
        </div>
    </section>
</x-tenant::layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, array{id: string, date: string, labels: array<int, string>, highlights: array<int, string>, changes: array<int, array{title: string, description: string}>}> */
    public array $updates = [
        [
            'id' => 'update-2026-06-01',
            'date' => '2026-06-01',
            'labels' => ['ميزة', 'تحسين', 'إصلاح'],
            'highlights' => [
                'إرفاق الملفات',
                'محرر جديد',
                'مكونات مخصصة',
                'تحسين البحث',
                'تحسين الجداول',
                'إصلاحات',
            ],
            'changes' => [
                [
                    'title' => 'إرفاق الملفات داخل المحادثة',
                    'description' => 'يدعم الوكيل الآن إرفاق ملفات PDF وملفات الكود والمستندات والجداول مباشرة داخل المحادثة، مما يمنحه سياقًا أعمق عند إنشاء المحتوى أو تحديثه.',
                ],
                [
                    'title' => 'تخصيص CSS بشكل مرن',
                    'description' => 'يمكنك إضافة ملفات CSS مخصصة للتحكم البصري على مستوى المكونات، أو ربط ملفات خارجية لتعديل العناصر الثابتة بسهولة أكبر.',
                ],
                [
                    'title' => 'مكوّن Board لتنظيم الخطط',
                    'description' => 'تم إضافة مكوّن Board الجديد لعرض الخطط ومراحل العمل بأسلوب الأعمدة والبطاقات، ما يسهّل قراءة المحتوى المعقد بسرعة.',
                ],
            ],
        ],
        [
            'id' => 'update-2026-05-25',
            'date' => '2026-05-25',
            'labels' => ['تحسين'],
            'highlights' => ['تحسينات الأداء', 'تحديث الواجهة'],
            'changes' => [
                [
                    'title' => 'تحسين سرعة تحميل الصفحات',
                    'description' => 'خفضنا زمن التحميل الأولي للصفحات الثقيلة، وحسّنّا استجابة الواجهة أثناء التنقل بين الأقسام في الأجهزة المكتبية والهواتف.',
                ],
            ],
        ],
        [
            'id' => 'update-2026-05-18',
            'date' => '2026-05-18',
            'labels' => ['إصلاح'],
            'highlights' => ['إصلاحات حرجة', 'استقرار'],
            'changes' => [
                [
                    'title' => 'معالجة مشاكل التمرير والتنقل',
                    'description' => 'تم إصلاح عدة حالات متعلقة بالتمرير في الصفحات الطويلة وتحسين التزامن بين الروابط الداخلية والأقسام.',
                ],
            ],
        ],
        [
            'id' => 'update-2026-05-11',
            'date' => '2026-05-11',
            'labels' => ['تحسين'],
            'highlights' => ['واجهة المستخدم', 'قابلية القراءة'],
            'changes' => [
                [
                    'title' => 'تحسين قابلية القراءة في الواجهة',
                    'description' => 'أعدنا ضبط المسافات والأحجام الطباعية في الأقسام الطويلة لتكون القراءة أكثر راحة واتساقًا في الشاشات المختلفة.',
                ],
            ],
        ],
        [
            'id' => 'update-2026-04-10',
            'date' => '2026-04-10',
            'labels' => ['ميزة'],
            'highlights' => ['تجربة تحرير'],
            'changes' => [
                [
                    'title' => 'تحديث أدوات الكتابة والتحرير',
                    'description' => 'أطلقنا تحديثات على محرر المحتوى لدعم تدفق عمل أسرع مع تحسينات على الإدراج والتعديل داخل الصفحات الطويلة.',
                ],
            ],
        ],
    ];
};
?>
