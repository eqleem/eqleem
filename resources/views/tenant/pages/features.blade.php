<x-tenant::pages.layout>
    <section class="mb-6">
        <x-tenant::breadcrumb :links="[['url' => null, 'title' => 'المزايا']]" />
    </section>

    <section class="space-y-6 p-2">
        @foreach ($features as $feature)
            <article wire:key="feature-{{ $feature['slug'] }}" class="rounded-2xl bg-white p-4 md:p-6">
                <div class="grid grid-cols-1 items-center gap-6 md:grid-cols-2">
                    <div
                        @class([
                            'order-1 md:order-1' => $loop->odd,
                            'order-1 md:order-2' => $loop->even,
                        ])
                    >
                        <div class="overflow-hidden rounded-2xl border border-stone-200 bg-stone-50">
                            <img src="{{ $feature['image'] }}" alt="{{ $feature['title'] }}" class="h-full w-full object-cover">
                        </div>
                    </div>

                    <div
                        @class([
                            'order-2 md:order-2' => $loop->odd,
                            'order-2 md:order-1' => $loop->even,
                        ])
                    >
                        <span class="inline-flex rounded-full bg-primary-50 px-3 py-1 text-xs font-bold text-primary-700">{{ $feature['tag'] }}</span>
                        <h2 class="mt-3 text-2xl font-black leading-tight text-stone-900 md:text-4xl">{{ $feature['title'] }}</h2>
                        <p class="mt-4 text-sm leading-8 text-stone-600 md:text-base">{{ $feature['description'] }}</p>

                        <ul class="mt-4 space-y-2">
                            @foreach ($feature['points'] as $point)
                                <li class="flex items-center gap-2 text-sm text-stone-700">
                                    <iconify-icon icon="solar:check-circle-bold" class="text-lg text-primary-600"></iconify-icon>
                                    <span>{{ $point }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <div class="mt-6 border-t border-stone-200 pt-4">
                            <p class="text-sm italic leading-7 text-stone-600">"{{ $feature['quote'] }}"</p>
                            <p class="mt-2 text-sm font-bold text-stone-900">{{ $feature['author'] }}</p>
                            <p class="text-xs text-stone-500">{{ $feature['role'] }}</p>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach
    </section>
</x-tenant::pages.layout>

<?php

use Livewire\Component;

new class extends Component
{
    /** @var array<int, array<string, mixed>> */
    public array $features = [];

    public function mount(): void
    {
        $this->features = [
            [
                'slug' => 'smart-workflow',
                'tag' => 'تدفق عمل ذكي',
                'title' => 'نظام منظم لإدارة مهام التنفيذ',
                'description' => 'رتب مراحل المشروع من المعاينة وحتى التسليم داخل لوحة واحدة تساعدك على متابعة كل خطوة بدون تعقيد.',
                'image' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=1200&auto=format&fit=crop',
                'points' => ['متابعة حالة كل مهمة', 'تنبيهات تلقائية عند التأخير', 'تقارير يومية سريعة'],
                'quote' => 'التجربة اختصرت علينا وقت الإدارة وخففت الأخطاء اليومية.',
                'author' => 'سالم الدوسري',
                'role' => 'مدير مشاريع تشطيبات',
            ],
            [
                'slug' => 'high-accuracy-reports',
                'tag' => 'دقة أعلى',
                'title' => 'تقارير تفصيلية لاتخاذ قرارات أسرع',
                'description' => 'احصل على بيانات واضحة عن التكاليف، نسب الإنجاز، واستهلاك المواد حتى تكون قراراتك مبنية على أرقام دقيقة.',
                'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?q=80&w=1200&auto=format&fit=crop',
                'points' => ['لوحات قياس لحظية', 'مقارنة بين الفروع', 'تحليل المصروفات والربحية'],
                'quote' => 'أصبحنا نعرف أين نهدر الميزانية وكيف نحسن الأداء بسرعة.',
                'author' => 'ريم الحربي',
                'role' => 'مسؤولة تشغيل',
            ],
            [
                'slug' => 'team-collaboration',
                'tag' => 'تعاون الفريق',
                'title' => 'تنسيق متكامل بين المكتب والموقع',
                'description' => 'تواصل داخلي أسهل بين فريق الإشراف والتنفيذ، مع تحديثات فورية لكل الملاحظات والتعديلات أثناء العمل.',
                'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1200&auto=format&fit=crop',
                'points' => ['تعليقات على المهام مباشرة', 'مشاركة ملفات وصور التنفيذ', 'سجل واضح للتعديلات'],
                'quote' => 'كل الفريق صار على نفس الصفحة، وهذا رفع جودة التسليم.',
                'author' => 'محمد القحطاني',
                'role' => 'مشرف موقع',
            ],
            [
                'slug' => 'secure-access',
                'tag' => 'أمان وصلاحيات',
                'title' => 'تحكم كامل بالصلاحيات وحماية البيانات',
                'description' => 'حدد لكل عضو في الفريق مستوى الوصول المناسب، مع حماية أفضل للبيانات الحساسة وسجل تدقيق كامل.',
                'image' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?q=80&w=1200&auto=format&fit=crop',
                'points' => ['صلاحيات مرنة حسب الدور', 'سجل عمليات تفصيلي', 'نسخ احتياطي منتظم وآمن'],
                'quote' => 'الأمان أصبح نقطة قوة حقيقية في عملياتنا اليومية.',
                'author' => 'نواف العتيبي',
                'role' => 'مسؤول تقنية المعلومات',
            ],
        ];
    }
};
?>
