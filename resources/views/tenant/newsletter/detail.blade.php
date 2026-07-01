<x-tenant::newsletter.layout>
    <div class="mb-5 flex items-center justify-between px-2">
        <a href="{{ route('tenant.newsletter.index') }}" wire:navigate class="flex h-10 w-10 rotate-180 items-center justify-center rounded-full bg-stone-100 transition hover:bg-stone-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5 text-stone-700"><path d="m12 19-7-7 7-7"></path><path d="M19 12H5"></path></svg>
        </a>
    </div>

    <div class="mx-auto max-w-md text-center md:max-w-2xl">
        <h1 class="mb-4 text-2xl font-extrabold text-stone-800 md:text-3xl">
            {{ $article['title'] }}
        </h1>
        <h3 class="my-3 text-base leading-tight text-stone-500 md:text-xl">
            {{ $article['excerpt'] }}
        </h3>
        <p class="mt-4 text-sm text-stone-400 md:text-base">
            <span class="rounded-md bg-primary-50 px-2 py-1 text-sm text-primary-600">{{ $article['date'] }}</span>
        </p>
    </div>

    <section class="px-2 md:px-4">
        <img
            src="{{ $article['image'] }}"
            alt="{{ $article['title'] }}"
            class="mx-auto my-10 h-full w-full rounded-2xl object-cover"
        >

        <div class="space-y-4 p-3 leading-8 text-stone-700">
            <p>{{ $article['content'][0] }}</p>
            <p>{{ $article['content'][1] }}</p>
            <p>{{ $article['content'][2] }}</p>
        </div>
    </section>
</x-tenant::newsletter.layout>

<?php

use Livewire\Component;

new class extends Component
{
    public array $article = [];

    public function mount(string $slug): void
    {
        $articles = [
            'design-trends-summer-2026' => [
                'title' => 'اتجاهات التصميم لصيف 2026',
                'excerpt' => 'ألوان هادئة، خامات طبيعية، وحلول عملية للمساحات الصغيرة.',
                'date' => '20 يونيو 2026',
                'image' => 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'التوجه هذا الموسم يميل للألوان الترابية والمواد الطبيعية مثل الخشب والحجر الخفيف، لأنها تعطي إحساسا بالهدوء والاتساع داخل المنزل.',
                    'عند اختيار الأثاث، الأفضل المزج بين القطع البسيطة والخطوط النظيفة مع إضاءة دافئة. هذا الدمج يخلق مساحة مريحة وسهلة التحديث مستقبلا.',
                    'لو المساحة صغيرة، ركز على التخزين الذكي واستخدام المرايا والألوان الفاتحة حتى تحصل على مظهر عملي وأنيق بدون ازدحام بصري.',
                ],
            ],
            'marble-alternative-guide' => [
                'title' => 'دليل بديل الرخام: المميزات والاستخدامات',
                'excerpt' => 'كيف تختار البديل المناسب للجدران والمداخل حسب الميزانية.',
                'date' => '13 يونيو 2026',
                'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'بديل الرخام صار خيارا شائعا لأنه يعطي مظهرا فاخرا مع تكلفة أقل وسهولة أعلى في التركيب مقارنة بالرخام الطبيعي.',
                    'قبل الشراء، قارن بين السمك، مقاومة الرطوبة، وضمان الخامة. هذه النقاط تؤثر مباشرة على العمر الافتراضي وجودة النتيجة النهائية.',
                    'يفضل استخدامه في الجدران الداخلية والمداخل مع عناية دورية بسيطة للحفاظ على اللمعة والمظهر النظيف لأطول فترة ممكنة.',
                ],
            ],
            'weekly-secret-picks' => [
                'title' => 'اختيارات النشرة السرية لهذا الأسبوع',
                'excerpt' => '3 توصيات سريعة لتحسين شكل المنزل بدون تكلفة كبيرة.',
                'date' => '06 يونيو 2026',
                'image' => 'https://images.unsplash.com/photo-1493666438817-866a91353ca9?auto=format&fit=crop&w=1200&q=80',
                'content' => [
                    'ابدأ بتغيير الإضاءة البيضاء الحادة إلى إضاءة دافئة في مناطق الجلوس، هذا التعديل وحده يغيّر أجواء المكان بشكل واضح.',
                    'استخدم لوحة ألوان موحدة للإكسسوارات الصغيرة مثل الوسائد والسجاد حتى يبدو التصميم أكثر اتساقا واحترافية.',
                    'أخيرا، تخلص من الفوضى البصرية بوحدات تخزين بسيطة ومفتوحة، لأنها تمنحك شكل مرتب بدون الحاجة لتجديد كامل.',
                ],
            ],
        ];

        abort_unless(isset($articles[$slug]), 404);

        $this->article = $articles[$slug];
    }
};
?>
