<x-tenant::pages.layout>
 
        <x-tenant::breadcrumb :links="[['url' => route('tenant.store.index'), 'title' => 'المتجر'], ['url' => route('tenant.pages.cart'), 'title' => 'السلة'], ['url' => null, 'title' => 'إتمام الشراء']]" />
 
    <section class="grid grid-cols-1 gap-5 p-2 lg:grid-cols-3" dir="rtl">
        <div class="space-y-5 lg:col-span-2">
     
            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <h3 class="mb-4 text-lg font-bold text-stone-900">خيارات الشحن والتسليم</h3>
                <div class="space-y-3">
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                        <input type="radio" name="shipping_method" checked class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-stone-900">توصيل سريع (24-48 ساعة)</p>
                            <p class="text-xs text-stone-500">رسوم الشحن: 35 ر.س</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                        <input type="radio" name="shipping_method" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-stone-900">توصيل مجدول</p>
                            <p class="text-xs text-stone-500">اختر اليوم المناسب للاستلام.</p>
                        </div>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                        <input type="radio" name="shipping_method" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-stone-900">استلام من المعرض</p>
                            <p class="text-xs text-stone-500">بدون رسوم شحن.</p>
                        </div>
                    </label>
                </div>
            </div>

            <div class="rounded-2xl border border-stone-200 bg-white p-5">
                <h3 class="mb-4 text-lg font-bold text-stone-900">خيارات الدفع</h3>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                        <input type="radio" name="payment_method" checked class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                        <span class="text-sm font-medium text-stone-700">مدى / فيزا / ماستركارد</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                        <input type="radio" name="payment_method" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                        <span class="text-sm font-medium text-stone-700">Apple Pay</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                        <input type="radio" name="payment_method" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                        <span class="text-sm font-medium text-stone-700">تحويل بنكي</span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                        <input type="radio" name="payment_method" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                        <span class="text-sm font-medium text-stone-700">الدفع عند الاستلام (للمنتجات)</span>
                    </label>
                </div>
            </div>

        </div>

        <aside class="h-fit rounded-2xl border border-stone-200 bg-white p-5 lg:sticky lg:top-6">
            <h3 class="mb-4 text-lg font-bold text-stone-900">ملخص السلة</h3>

            <div class="space-y-3 border-b border-stone-100 pb-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-stone-500">عدد المنتجات</span>
                    <span class="font-semibold text-stone-900">3</span>
                </div>
          
                <div class="flex items-center justify-between text-sm">
                    <span class="text-stone-500">إجمالي المنتجات</span>
                    <span class="font-semibold text-stone-900">2,870 ر.س</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-stone-500">الشحن</span>
                    <span class="font-semibold text-stone-900">35 ر.س</span>
                </div>
              
            </div>

            <div class="my-4 flex items-center justify-between">
                <span class="text-base font-bold text-stone-900">الإجمالي المبدئي</span>
                <span class="text-base font-bold text-primary-700">2,905 ر.س</span>
            </div>

            <button
                type="button"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700"
            >
                <iconify-icon icon="hugeicons:checkmark-circle-02" class="text-xl"></iconify-icon>
                تأكيد وإتمام الطلب
            </button>

            <p class="mt-3 text-center text-xs text-stone-500">
                بإتمام الطلب أنت توافق على الشروط وسياسة الاسترجاع.
            </p>
        </aside>
    </section>
</x-tenant::pages.layout>

<?php

use Livewire\Component;

new class extends Component
{
    //
};
?>
