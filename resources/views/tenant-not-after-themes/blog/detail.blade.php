<x-tenant::blog.layout>
    <article  class=" px-2 md:px-4">
        <div class="mb-8 flex items-center justify-between gap-3">
            <a
                href="{{ route('tenant.blog.index') }}"
                wire:navigate
                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200"
                aria-label="العودة إلى صفحة المدونة"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19 7-7-7-7"></path>
                    <path d="M5 12h14"></path>
                </svg>
            </a>

            <div class="flex items-center gap-2">
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200" aria-label="مشاركة المقال">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v13"></path>
                        <path d="m16 6-4-4-4 4"></path>
                        <path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"></path>
                    </svg>
                </button>
                <button class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-stone-100 text-stone-700 transition hover:bg-stone-200" aria-label="إضافة إلى المفضلة">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                    </svg>
                </button>
            </div>
        </div>

        <header class="space-y-6">
            {{-- <p class="text-sm font-medium text-stone-500">مدونة</p> --}}

            <h1 class="max-w-3xl text-3xl font-black leading-tight text-stone-900 md:text-5xl">
                ما هي وثائق الذكاء الاصطناعي ولماذا أصبحت مهمة اليوم؟
            </h1>

            <p class="max-w-3xl text-base leading-8 text-stone-600 md:text-xl md:leading-9">
                لم تعد توثيقات المنتجات شيئًا يُحدّث على فترات متباعدة؛ بل أصبحت جزءًا حيًا من المنتج نفسه. توثيق الذكاء الاصطناعي يساعد الفرق على إبقاء المحتوى دقيقًا، ومتسقًا مع الإصدارات السريعة، ويسهّل على أدوات مثل ChatGPT وClaude تقديم إجابات موثوقة للمستخدمين.
            </p>

            <div class="flex flex-wrap items-end justify-between gap-4 text-sm text-stone-500 md:text-base">
                <div class="flex flex-wrap items-center gap-6">
                    <div class="space-y-1">
                        <p class="text-xs font-semibold tracking-wide text-stone-400 md:text-sm">الكاتب</p>
                        <p class="font-semibold text-stone-800">روب رِدي</p>
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs font-semibold tracking-wide text-stone-400 md:text-sm">تاريخ النشر</p>
                        <p class="font-semibold text-stone-800">26 نوفمبر 2025</p>
                    </div>
                </div>

                <button class="inline-flex items-center gap-2 text-stone-400 transition hover:text-stone-600" aria-label="الإعجابات">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 9.5a5.5 5.5 0 0 1 9.591-3.676.56.56 0 0 0 .818 0A5.49 5.49 0 0 1 22 9.5c0 2.29-1.5 4-3 5.5l-5.492 5.313a2 2 0 0 1-3 .019L5 15c-1.5-1.5-3-3.2-3-5.5"></path>
                    </svg>
                    <span>0</span>
                </button>
            </div>
        </header>

        <div class="my-8 h-px w-full bg-stone-200"></div>

        <figure class="overflow-hidden rounded-3xl border border-stone-200/80 bg-stone-50">
            <img
                src="https://r2-bucket.thmanyah.com/cdn-cgi/image/width=1200/media/2026/158-20260616-V02.jpg"
                alt="ما هي وثائق الذكاء الاصطناعي ولماذا أصبحت مهمة اليوم؟"
                class="h-full w-full object-cover"
            >
        </figure>

        <section class="mt-10 space-y-6 text-lg leading-9 text-stone-700">
            <p>
                تغيّر عالم التوثيق كثيرًا في السنوات الأخيرة. سابقًا كانت معظم الفرق تكتب الدليل مرة واحدة ثم تتركه لفترة طويلة. اليوم أصبح التوثيق جزءًا من تجربة المنتج اليومية، لأنه يرشد المستخدمين، ويدعم المطورين، ويعلّم وكلاء الذكاء الاصطناعي كيفية التعامل الصحيح مع أدواتك.
            </p>

            <p>
                وهذا يعني أيضًا أن أي نقص أو غموض في المحتوى قد يؤدي إلى إجابات خاطئة من أنظمة الذكاء الاصطناعي ومنصات الدعم. النتيجة تكون إرباكًا للمستخدم النهائي، وضغطًا إضافيًا على فرق الدعم والهندسة التي تضطر لتصحيح المعلومات بعد انتشارها.
            </p>

            <p>
                وفي المقابل، تتسارع المنتجات اليوم بوتيرة أعلى من أي وقت مضى. تتغير الميزات بسرعة، وتتطور الواجهات البرمجية باستمرار، وتتبدل مسارات العمل مع كل إصدار. لذلك تصبح المحافظة على توثيق حي ومحدّث عاملًا أساسيًا لبناء الثقة وتقليل فجوة المعرفة بين ما يقدمه المنتج وما يفهمه المستخدم.
            </p>
        </section>
    </article>
</x-tenant::blog.layout>

<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>