<?php

namespace App\Livewire\Tenant\Pages;

use Livewire\Component;

class Features extends Component
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

    public function render()
    {
        return tenantView('pages.features')->title('المزايا');
    }
}
