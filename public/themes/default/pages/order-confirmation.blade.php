<x-tenant-theme::pages.layout>
    <x-tenant-theme::breadcrumb :links="[['url' => route('tenant.store.index'), 'title' => 'المتجر'], ['url' => route('tenant.pages.checkout'), 'title' => 'إتمام الشراء'], ['url' => null, 'title' => 'تأكيد الطلب']]" />

    <section class="p-2" dir="rtl">
        <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
            <div class="relative bg-gradient-to-br from-primary-600 via-primary-700 to-primary-800 px-6 py-10 text-center text-white">
                <div class="pointer-events-none absolute inset-0 opacity-20">
                    <div class="absolute -top-10 -right-10 h-40 w-40 rounded-full bg-white/30 blur-2xl"></div>
                    <div class="absolute -bottom-12 -left-8 h-48 w-48 rounded-full bg-white/20 blur-3xl"></div>
                </div>

                <div class="relative mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/30 backdrop-blur">
                    <iconify-icon icon="hugeicons:checkmark-circle-02" class="text-5xl text-white"></iconify-icon>
                </div>

                <h1 class="relative text-2xl font-bold sm:text-3xl">تم استلام طلبك بنجاح!</h1>
                <p class="relative mt-2 text-sm text-white/85 sm:text-base">شكراً لثقتك بنا. سنبدأ بمعالجة طلبك في أقرب وقت.</p>
            </div>

            <div class="grid grid-cols-1 gap-6 p-5 lg:grid-cols-3 lg:p-8">
                <div class="space-y-5 lg:col-span-2">
                    <div class="rounded-2xl border border-primary-100 bg-primary-50/60 p-5 text-center">
                        <p class="text-sm font-medium text-primary-700">رقم الطلب</p>
                        <p class="mt-2 text-3xl font-black tracking-wider text-primary-900" dir="ltr">#{{ $order->number }}</p>
                        <p class="mt-2 text-xs text-stone-500">{{ $order->issued_at?->translatedFormat('l، j F Y — h:i A') }}</p>
                    </div>

                    <div class="rounded-2xl border border-stone-200 p-5">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <h2 class="text-lg font-bold text-stone-900">تفاصيل الطلب</h2>
                            <span class="rounded-lg bg-stone-100 px-3 py-1 text-xs font-semibold text-stone-600">
                                {{ $itemCount }} {{ $itemCount === 1 ? 'عنصر' : 'عناصر' }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            @foreach ($items as $item)
                                <article wire:key="order-item-{{ $item->id }}" class="flex items-center gap-3 rounded-xl bg-stone-50 p-3">
                                    @if (filled($item->image_url))
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="h-16 w-16 shrink-0 rounded-lg object-cover">
                                    @else
                                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-lg bg-stone-200 text-stone-500">
                                            <iconify-icon icon="hugeicons:image-01" class="text-2xl"></iconify-icon>
                                        </div>
                                    @endif

                                    <div class="min-w-0 flex-1">
                                        <p class="truncate text-sm font-semibold text-stone-900">{{ $item->name }}</p>
                                        <p class="mt-1 text-xs text-stone-500">
                                            {{ $item->type_label }}
                                            @if (! ($item->is_booking ?? false))
                                                — الكمية: {{ $item->qty }}
                                            @endif
                                        </p>

                                        @if ($item->is_booking ?? false)
                                            <div class="mt-2 space-y-1 rounded-lg border border-primary-100 bg-primary-50/50 px-3 py-2 text-xs text-stone-600">
                                                <p class="font-semibold text-primary-800">تفاصيل الحجز</p>
                                                @if (filled($item->calendar_name))
                                                    <p>
                                                        <span class="text-stone-500">مقدم الخدمة:</span>
                                                        {{ $item->calendar_name }}
                                                    </p>
                                                @endif
                                                @if (filled($item->booking_date_label))
                                                    <p>
                                                        <span class="text-stone-500">التاريخ:</span>
                                                        {{ $item->booking_date_label }}
                                                    </p>
                                                @endif
                                                @if (filled($item->booking_time_label))
                                                    <p>
                                                        <span class="text-stone-500">الوقت:</span>
                                                        <span dir="ltr">{{ $item->booking_time_label }}</span>
                                                    </p>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <p class="shrink-0 text-sm font-bold text-stone-900" dir="ltr">
                                        {{ money_format($item->line_total, currency: $order->currency_code) }}
                                    </p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>

                <aside class="h-fit space-y-4 lg:sticky lg:top-6">
                    <div class="rounded-2xl border border-stone-200 p-5">
                        <h3 class="mb-4 text-base font-bold text-stone-900">ملخص الدفع</h3>

                        <div class="space-y-3 border-b border-stone-100 pb-4 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-stone-500">المجموع الفرعي</span>
                                <span class="font-semibold text-stone-900" dir="ltr">{{ money_format($order->subtotal, currency: $order->currency_code) }}</span>
                            </div>

                            @if ($shippingFee > 0)
                                <div class="flex items-center justify-between">
                                    <span class="text-stone-500">الشحن</span>
                                    <span class="font-semibold text-stone-900" dir="ltr">{{ money_format($shippingFee, currency: $order->currency_code) }}</span>
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-1">
                                <span class="font-bold text-stone-900">الإجمالي</span>
                                <span class="text-base font-bold text-primary-700" dir="ltr">{{ money_format($order->grand_total, currency: $order->currency_code) }}</span>
                            </div>
                        </div>

                        <dl class="mt-4 space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-stone-500">طريقة الشحن</dt>
                                <dd class="text-left font-semibold text-stone-800">{{ $order->shippingMethodLabel() }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-stone-500">طريقة الدفع</dt>
                                <dd class="text-left font-semibold text-stone-800">{{ $order->paymentMethodLabel() }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-3">
                                <dt class="text-stone-500">حالة الدفع</dt>
                                <dd>
                                    <span @class([
                                        'inline-flex rounded-lg px-2.5 py-1 text-xs font-semibold',
                                        'bg-emerald-50 text-emerald-700' => $order->payment_status === 'paid',
                                        'bg-amber-50 text-amber-700' => $order->payment_status === 'partial',
                                        'bg-red-50 text-red-700' => $order->payment_status === 'unpaid',
                                        'bg-stone-100 text-stone-600' => ! in_array($order->payment_status, ['paid', 'partial', 'unpaid'], true),
                                    ])>
                                        {{ $order->paymentStatusLabel() }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <a
                        href="{{ route('tenant.store.index') }}"
                        wire:navigate
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3.5 text-sm font-bold text-white transition hover:bg-primary-700"
                    >
                        <iconify-icon icon="hugeicons:shopping-bag-01" class="text-xl"></iconify-icon>
                        متابعة التسوق
                    </a>

                    <p class="text-center text-xs leading-relaxed text-stone-500">
                        احتفظ برقم الطلب للمتابعة. إذا كان لديك استفسار، تواصل معنا عبر صفحة التواصل.
                    </p>
                </aside>
            </div>
        </div>
    </section>
</x-tenant-theme::pages.layout>
