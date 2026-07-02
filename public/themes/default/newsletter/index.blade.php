<x-tenant-theme::newsletter.layout>
    <section class="mb-6 rounded-2xl border border-primary-100 bg-primary-50/40 p-4 md:p-6">
        <div class="mb-4">
            <p class="mb-1 text-sm font-semibold text-primary-600">النشرة البريدية</p>
            <h2 class="text-xl font-extrabold text-stone-900 md:text-2xl">اشترك ليصلك جديدنا أسبوعيا</h2>
            <p class="mt-2 text-sm text-stone-600 md:text-base">
                مقالات مختصرة وعملية في التشطيبات والديكور الداخلي. بدون إزعاج، فقط محتوى مفيد.
            </p>
        </div>

        <form class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-grow">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <iconify-icon icon="hugeicons:mail-02" class="text-2xl text-stone-400" stroke-width="1.5"></iconify-icon>
                </div>
                <input
                    type="email"
                    placeholder="your@email.com"
                    dir="ltr"
                    class="custom-input w-full rounded-xl border border-primary-100 bg-white py-3 pl-11 pr-4 text-base text-stone-700 placeholder-stone-400 outline-none focus:border-primary-300"
                    required
                >
            </div>
            <button
                type="button"
                class="flex items-center justify-center gap-2 rounded-xl bg-primary-500 px-6 py-3 text-base font-medium text-white transition hover:bg-primary-600"
            >
                <span>اشترك الآن</span>
            </button>
        </form>

        <p class="mt-3 text-xs text-stone-500">انضم إلى 2,400+ مهتم بالتشطيبات والديكور.</p>
    </section>

    <section class="mb-6 w-full">
        <nav class="flex items-center gap-3 overflow-x-auto no-scrollbar rounded-2xl bg-stone-200/40 p-1 whitespace-nowrap">
            <a
                href="{{ route('tenant.newsletter.index') }}"
                wire:navigate
                class="rounded-xl bg-white px-4 py-2.5 text-sm font-medium text-stone-900 shadow-sm"
            >
                الكل
            </a>
            <a
                href="{{ route('tenant.newsletter.index', ['newsletter' => 'saturday']) }}"
                wire:navigate
                class="rounded-xl px-4 py-2.5 text-sm font-medium text-stone-600 transition hover:bg-stone-50 hover:text-stone-900"
            >
                شرة السبت
            </a>
            <a
                href="{{ route('tenant.newsletter.index', ['newsletter' => 'secret']) }}"
                wire:navigate
                class="rounded-xl px-4 py-2.5 text-sm font-medium text-stone-600 transition hover:bg-stone-50 hover:text-stone-900"
            >
                النشرة السرية
            </a>
        </nav>
    </section>

    <section>
        <div class="space-y-5 md:space-y-6">
            <article class="rounded-2xl bg-stone-100/80 p-2 transition hover:bg-stone-200/50 md:p-4">
                <a href="{{ route('tenant.newsletter.detail', 'design-trends-summer-2026') }}" wire:navigate class="flex items-start gap-4 md:gap-6">
                    <img
                        src="https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?auto=format&fit=crop&w=400&q=80"
                        alt="اتجاهات التصميم لصيف 2026"
                        class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28"
                    >
                    <div class="flex-1">
                        <h3 class="mb-2 text-base font-extrabold leading-tight text-stone-900 md:text-xl">اتجاهات التصميم لصيف 2026</h3>
                        <p class="mb-3 text-sm text-stone-500 md:text-base">ألوان هادئة، خامات طبيعية، وحلول عملية للمساحات الصغيرة.</p>
                        <p class="flex flex-wrap items-center gap-2 text-sm text-stone-400 md:text-base">
                            <span class="text-base font-extrabold text-orange-600 md:text-lg">فريق أبعاد البيت</span>
                            <span>· 20 يونيو 2026</span>
                        </p>
                    </div>
                </a>
            </article>

            <article class="rounded-2xl bg-stone-100/80 p-2 transition hover:bg-stone-200/50 md:p-4">
                <a href="{{ route('tenant.newsletter.detail', 'marble-alternative-guide') }}" wire:navigate class="flex items-start gap-4 md:gap-6">
                    <img
                        src="https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?auto=format&fit=crop&w=400&q=80"
                        alt="دليل بديل الرخام"
                        class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28"
                    >
                    <div class="flex-1">
                        <h3 class="mb-2 text-base font-extrabold leading-tight text-stone-900 md:text-xl">دليل بديل الرخام: المميزات والاستخدامات</h3>
                        <p class="mb-3 text-sm text-stone-500 md:text-base">كيف تختار البديل المناسب للجدران والمداخل حسب الميزانية.</p>
                        <p class="flex flex-wrap items-center gap-2 text-sm text-stone-400 md:text-base">
                            <span class="text-base font-extrabold text-orange-600 md:text-lg">سارة العتيبي</span>
                            <span>· 13 يونيو 2026</span>
                        </p>
                    </div>
                </a>
            </article>

            <article class="rounded-2xl bg-stone-100/80 p-2 transition hover:bg-stone-200/50 md:p-4">
                <a href="{{ route('tenant.newsletter.detail', 'weekly-secret-picks') }}" wire:navigate class="flex items-start gap-4 md:gap-6">
                    <img
                        src="https://images.unsplash.com/photo-1493666438817-866a91353ca9?auto=format&fit=crop&w=400&q=80"
                        alt="اختيارات النشرة السرية"
                        class="h-20 w-20 shrink-0 rounded-2xl object-cover md:h-28 md:w-28"
                    >
                    <div class="flex-1">
                        <h3 class="mb-2 text-base font-extrabold leading-tight text-stone-900 md:text-xl">اختيارات النشرة السرية لهذا الأسبوع</h3>
                        <p class="mb-3 text-sm text-stone-500 md:text-base">3 توصيات سريعة لتحسين شكل المنزل بدون تكلفة كبيرة.</p>
                        <p class="flex flex-wrap items-center gap-2 text-sm text-stone-400 md:text-base">
                            <span class="text-base font-extrabold text-orange-600 md:text-lg">محمد السبيعي</span>
                            <span>· 06 يونيو 2026</span>
                        </p>
                    </div>
                </a>
            </article>
        </div>
    </section>
</x-tenant-theme::newsletter.layout>