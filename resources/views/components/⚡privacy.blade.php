<div>
    <x-site-shell title="سياسة الخصوصية" subtitle="نوضح هنا كيف نجمع بياناتك ونستخدمها ونحميها عند استخدام منصة {{ config('app.name') }}.">
        <article class="space-y-10 text-stone-600 leading-relaxed">
            <p class="text-sm text-stone-400">آخر تحديث: {{ now()->translatedFormat('d F Y') }}</p>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">1. المقدمة</h2>
                <p>
                    تحترم {{ config('app.name') }} خصوصيتك. تشرح هذه السياسة أنواع المعلومات التي قد نجمعها عند استخدامك للمنصة، وكيف نستخدمها، والخيارات المتاحة لك.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">2. البيانات التي نجمعها</h2>
                <p>قد نجمع أنواعًا من البيانات تشمل:</p>
                <ul class="list-disc pe-6 space-y-2">
                    <li>بيانات الحساب مثل الاسم والبريد الإلكتروني وبيانات تسجيل الدخول.</li>
                    <li>محتوى صفحتك ومنتجاتك وطلبات عملائك التي تدخلها في المنصة.</li>
                    <li>بيانات تقنية مثل عنوان IP ونوع المتصفح وسجلات الاستخدام لتحسين الأداء والأمان.</li>
                    <li>بيانات الدفع عبر مزوّدي الدفع المعتمدين، دون تخزين بيانات البطاقة الكاملة لدينا عند الإمكان.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">3. كيف نستخدم البيانات</h2>
                <p>نستخدم البيانات من أجل:</p>
                <ul class="list-disc pe-6 space-y-2">
                    <li>تقديم الخدمة وتشغيل صفحاتك ومعالجة الطلبات والاشتراكات.</li>
                    <li>التواصل معك بشأن الحساب والدعم والتحديثات المهمة.</li>
                    <li>تحسين المنصة ومنع الاحتيال وضمان الأمان.</li>
                    <li>الامتثال للمتطلبات النظامية عند الاقتضاء.</li>
                </ul>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">4. مشاركة البيانات</h2>
                <p>
                    لا نبيع بياناتك الشخصية. قد نشارك بيانات محدودة مع مزوّدي خدمات يساعدوننا في التشغيل (مثل الاستضافة، البريد، والمدفوعات)، وبما يلزم لتقديم الخدمة فقط، أو عند وجود التزام نظامي.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">5. ملفات تعريف الارتباط</h2>
                <p>
                    قد نستخدم ملفات تعريف الارتباط وتقنيات مشابهة لتشغيل الجلسات، وتذكر تفضيلاتك، وتحسين تجربة الاستخدام. يمكنك التحكم في ملفات تعريف الارتباط من إعدادات متصفحك.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">6. الاحتفاظ بالبيانات وأمانها</h2>
                <p>
                    نحتفظ بالبيانات للمدة اللازمة لتقديم الخدمة والوفاء بالالتزامات النظامية.
                    نتخذ إجراءات تقنية وتنظيمية معقولة لحماية بياناتك، مع العلم أنه لا توجد وسيلة نقل أو تخزين إلكتروني آمنة بنسبة كاملة.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">7. حقوقك</h2>
                <p>
                    وفق الأنظمة المعمول بها، قد يحق لك طلب الاطلاع على بياناتك أو تصحيحها أو حذفها أو تقييد معالجتها، ضمن الحدود النظامية ومتطلبات تشغيل الحساب.
                    للتقديم على طلب متعلق بخصوصيتك، تواصل معنا عبر صفحة الاتصال.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">8. تحديثات السياسة</h2>
                <p>
                    قد نحدّث سياسة الخصوصية من وقت لآخر. سننشر النسخة المحدّثة على هذه الصفحة مع تاريخ التحديث.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">9. التواصل</h2>
                <p>
                    لأي أسئلة حول الخصوصية، راسلنا عبر
                    <a href="{{ route('contact') }}" wire:navigate class="text-[#C94309] hover:underline">اتصل بنا</a>
                    أو على
                    <a href="mailto:{{ config('mail.from.address') }}" class="text-[#C94309] hover:underline" dir="ltr">{{ config('mail.from.address') }}</a>.
                </p>
            </section>
        </article>
    </x-site-shell>
</div>

<?php

new
#[\Livewire\Attributes\Title('سياسة الخصوصية')]
class extends \Livewire\Component {
};
?>
