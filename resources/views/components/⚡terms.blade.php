<div>
    <x-site-shell title="الشروط والأحكام" subtitle="يرجى قراءة هذه الشروط بعناية قبل استخدام منصة {{ config('app.name') }}.">
        <article class="space-y-10 text-stone-600 leading-relaxed">
            <p class="text-sm text-stone-400">آخر تحديث: {{ now()->translatedFormat('d F Y') }}</p>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">1. القبول بالشروط</h2>
                <p>
                    باستخدامك لمنصة {{ config('app.name') }} أو إنشائك لحساب أو صفحة أعمال، فإنك توافق على الالتزام بهذه الشروط والأحكام.
                    إذا لم توافق على أي جزء منها، يرجى التوقف عن استخدام الخدمة.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">2. وصف الخدمة</h2>
                <p>
                    توفر {{ config('app.name') }} أدوات لإنشاء صفحات أعمال رقمية تتيح عرض المنتجات والخدمات، واستقبال الطلبات والحجوزات، والتواصل مع العملاء.
                    قد تتغير الميزات والباقات من وقت لآخر حسب تطوير المنصة.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">3. الحساب والمسؤولية</h2>
                <p>
                    أنت مسؤول عن الحفاظ على سرية بيانات الدخول الخاصة بك، وعن جميع الأنشطة التي تتم عبر حسابك.
                    يجب تقديم معلومات صحيحة ومحدثة، وعدم استخدام المنصة لأي غرض غير قانوني أو مخالف للأنظمة المعمول بها في المملكة العربية السعودية.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">4. المحتوى والملكية الفكرية</h2>
                <p>
                    تحتفظ بحقوق المحتوى الذي تنشره على صفحتك (نصوص، صور، منتجات، وغيرها)، وتمنح {{ config('app.name') }} ترخيصًا محدودًا لعرضه وتشغيله ضمن الخدمة.
                    جميع حقوق المنصة وتصميمها وعلاماتها التجارية مملوكة لـ {{ config('app.name') }} ولا يجوز نسخها أو إعادة استخدامها دون إذن.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">5. الاشتراكات والمدفوعات</h2>
                <p>
                    قد تتضمن بعض الميزات باقات مدفوعة. عند الاشتراك، توافق على دفع الرسوم الموضحة وفق دورة الفوترة المحددة.
                    قد تُطبَّق سياسات استرداد أو إلغاء وفق ما يُعلن عنه داخل المنصة أو عند إتمام الدفع.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">6. إيقاف الخدمة</h2>
                <p>
                    يحق لـ {{ config('app.name') }} تعليق أو إنهاء الوصول إلى الحساب عند مخالفة هذه الشروط، أو إساءة استخدام الخدمة، أو عند طلب ذلك بموجب الأنظمة.
                    يمكنك أيضًا التوقف عن استخدام الخدمة في أي وقت.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">7. إخلاء المسؤولية</h2>
                <p>
                    تُقدَّم الخدمة «كما هي». لا نضمن خلوها من الانقطاع أو الأخطاء، ولا نتحمل مسؤولية الأضرار غير المباشرة الناتجة عن استخدامك للمنصة أو اعتمادك على محتواها، ضمن الحدود التي يسمح بها النظام.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">8. التعديلات</h2>
                <p>
                    قد نحدّث هذه الشروط من حين لآخر. استمرارك في استخدام المنصة بعد نشر التعديلات يعني موافقتك على النسخة المحدّثة.
                </p>
            </section>

            <section class="space-y-3">
                <h2 class="text-xl tracking-tight text-[#111111]">9. التواصل</h2>
                <p>
                    لأي استفسار حول هذه الشروط، تواصل معنا عبر صفحة
                    <a href="{{ route('contact') }}" wire:navigate class="text-[#C94309] hover:underline">اتصل بنا</a>
                    أو عبر البريد
                    <a href="mailto:{{ config('mail.from.address') }}" class="text-[#C94309] hover:underline" dir="ltr">{{ config('mail.from.address') }}</a>.
                </p>
            </section>
        </article>
    </x-site-shell>
</div>

<?php

new
#[\Livewire\Attributes\Title('الشروط والأحكام')]
class extends \Livewire\Component {
};
?>
