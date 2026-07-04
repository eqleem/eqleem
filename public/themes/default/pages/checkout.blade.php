<x-tenant-theme::pages.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.store.index'), 'title' => 'المتجر'], ['url' => route('tenant.pages.cart'), 'title' => 'السلة'], ['url' => null, 'title' => 'إتمام الشراء']]" />

    @if ($items->isEmpty())
        <section class="p-2" dir="rtl">
            <div class="rounded-2xl border border-stone-200 bg-white p-8 text-center">
                <p class="text-base font-semibold text-stone-700">لا توجد منتجات في السلة</p>
                <a href="{{ route('tenant.store.index') }}" wire:navigate class="mt-4 inline-flex rounded-xl bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-primary-700">
                    العودة للمتجر
                </a>
            </div>
        </section>
    @else
        <section class="grid grid-cols-1 gap-5 p-2 lg:grid-cols-3" dir="rtl">
            <div class="space-y-5 lg:col-span-2">
                <div class="rounded-2xl border border-stone-200 bg-white p-5">
                    <h3 class="mb-4 text-lg font-bold text-stone-900">بيانات العميل</h3>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="mb-1 block text-sm text-stone-600">الاسم</label>
                            <input wire:model="name" type="text" class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm outline-none focus:border-stone-400">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-stone-600">رقم الهاتف</label>
                            <input wire:model="phone" type="tel" dir="ltr" class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm outline-none focus:border-stone-400">
                            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm text-stone-600">البريد الإلكتروني (اختياري)</label>
                            <input wire:model="email" type="email" dir="ltr" class="w-full rounded-xl border border-stone-200 px-3 py-2.5 text-sm outline-none focus:border-stone-400">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-stone-200 bg-white p-5">
                    <h3 class="mb-4 text-lg font-bold text-stone-900">خيارات الشحن والتسليم</h3>
                    <div class="space-y-3">
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                            <input wire:model.live="shippingMethod" type="radio" value="express" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-stone-900">توصيل سريع (24-48 ساعة)</p>
                                <p class="text-xs text-stone-500">رسوم الشحن: {{ money_format(3500) }}</p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                            <input wire:model.live="shippingMethod" type="radio" value="scheduled" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-stone-900">توصيل مجدول</p>
                                <p class="text-xs text-stone-500">رسوم الشحن: {{ money_format(3500) }}</p>
                            </div>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                            <input wire:model.live="shippingMethod" type="radio" value="pickup" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
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
                            <input wire:model="paymentMethod" type="radio" value="card" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                            <span class="text-sm font-medium text-stone-700">مدى / فيزا / ماستركارد</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                            <input wire:model="paymentMethod" type="radio" value="apple_pay" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                            <span class="text-sm font-medium text-stone-700">Apple Pay</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                            <input wire:model="paymentMethod" type="radio" value="bank_transfer" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                            <span class="text-sm font-medium text-stone-700">تحويل بنكي</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-stone-200 p-3 hover:border-primary-300">
                            <input wire:model="paymentMethod" type="radio" value="cod" class="h-4 w-4 border-stone-300 text-primary-600 focus:ring-primary-400">
                            <span class="text-sm font-medium text-stone-700">الدفع عند الاستلام</span>
                        </label>
                    </div>
                </div>
            </div>

            <aside class="h-fit rounded-2xl border border-stone-200 bg-white p-5 lg:sticky lg:top-6">
                <h3 class="mb-4 text-lg font-bold text-stone-900">ملخص السلة</h3>

                <div class="mb-4 space-y-3 border-b border-stone-100 pb-4">
                    @foreach ($items as $item)
                        <div wire:key="checkout-item-{{ $item->id }}" class="flex items-center justify-between gap-2 text-sm">
                            <span class="truncate text-stone-600">{{ $item->title() }} × {{ $item->quantity }}</span>
                            <span class="shrink-0 font-semibold text-stone-900" dir="ltr">{{ money_format($item->lineTotal()) }}</span>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-3 border-b border-stone-100 pb-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone-500">عدد المنتجات</span>
                        <span class="font-semibold text-stone-900">{{ $itemCount }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone-500">إجمالي المنتجات</span>
                        <span class="font-semibold text-stone-900" dir="ltr">{{ money_format($subtotal) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-stone-500">الشحن</span>
                        <span class="font-semibold text-stone-900" dir="ltr">{{ money_format($shippingFee) }}</span>
                    </div>
                </div>

                <div class="my-4 flex items-center justify-between">
                    <span class="text-base font-bold text-stone-900">الإجمالي</span>
                    <span class="text-base font-bold text-primary-700" dir="ltr">{{ money_format($grandTotal) }}</span>
                </div>

                <button
                    type="button"
                    wire:click="placeOrder"
                    wire:loading.attr="disabled"
                    wire:target="placeOrder"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-primary-700 disabled:opacity-70"
                >
                    <iconify-icon icon="hugeicons:checkmark-circle-02" class="text-xl"></iconify-icon>
                    <span wire:loading.remove wire:target="placeOrder">تأكيد وإتمام الطلب</span>
                    <span wire:loading wire:target="placeOrder">جاري إنشاء الطلب...</span>
                </button>

                <p class="mt-3 text-center text-xs text-stone-500">
                    بإتمام الطلب أنت توافق على الشروط وسياسة الاسترجاع.
                </p>
            </aside>
        </section>
    @endif
</x-tenant-theme::pages.layout>
