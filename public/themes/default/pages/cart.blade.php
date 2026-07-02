<x-tenant-theme::pages.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.store.index'), 'title' => 'المتجر'], ['url' => null, 'title' => 'السلة']]" />
    <section class="space-y-5 p-2" dir="rtl">
        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-stone-900"> ملخص السلة</h3>
                <span class="rounded-lg bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">3 منتجات</span>
            </div>

            <div class="space-y-3">
                <article class="flex items-center gap-3 rounded-xl bg-stone-50 p-3">
                    <img src="https://images.unsplash.com/photo-1616594039964-3f3fbc5b5331?q=80&w=300&auto=format&fit=crop" alt="لوح بديل الرخام" class="h-16 w-16 rounded-lg object-cover">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-stone-900">لوح بديل الرخام الفاخر</p>
                        <p class="text-xs text-stone-500">الكمية: 2</p>
                    </div>
                    <span class="text-sm font-bold text-stone-900">1,280 ر.س</span>
                </article>

                <article class="flex items-center gap-3 rounded-xl bg-stone-50 p-3">
                    <img src="https://images.unsplash.com/photo-1600607687644-c94bf8a44d39?q=80&w=300&auto=format&fit=crop" alt="ألواح باركيه" class="h-16 w-16 rounded-lg object-cover">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-stone-900">ألواح باركيه مقاومة للرطوبة</p>
                        <p class="text-xs text-stone-500">الكمية: 1</p>
                    </div>
                    <span class="text-sm font-bold text-stone-900">940 ر.س</span>
                </article>

                <article class="flex items-center gap-3 rounded-xl bg-stone-50 p-3">
                    <img src="https://images.unsplash.com/photo-1615529162924-f8605388464d?q=80&w=300&auto=format&fit=crop" alt="بديل الخشب" class="h-16 w-16 rounded-lg object-cover">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-stone-900">ديكور بديل الخشب للجدار</p>
                        <p class="text-xs text-stone-500">الكمية: 1</p>
                    </div>
                    <span class="text-sm font-bold text-stone-900">650 ر.س</span>
                </article>
            </div>
        </div>
 
        <div class="rounded-2xl border border-stone-200 bg-white p-5">
            <div class="mb-4 flex items-center justify-between text-sm">
                <span class="text-stone-500">إجمالي المنتجات</span>
                <span class="font-semibold text-stone-900">2,870 ر.س</span>
            </div>
            <div class="mb-5 flex items-center justify-between text-sm">
                <span class="text-stone-500">الخدمات</span>
                <span class="font-semibold text-primary-700">حسب الطلب</span>
            </div>

            <a
                href="{{ route('tenant.pages.checkout') }}"
                wire:navigate
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
            >
                <iconify-icon icon="hugeicons:credit-card" class="text-xl"></iconify-icon>
                إتمام الشراء
            </a>
        </div>
    </section>
</x-tenant-theme::pages.layout>