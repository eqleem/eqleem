<ui:container title="{{ __('Orders') }} / عملية #{{ $payment->id }}"
    backRoute="{{ route('admin.orders.home', ['tab' => 'payments']) }}">

    @php
        $paidAt = $payment->resolvedGatewayCreatedAt() ?? $payment->created_at;
        $gatewayRows = $payment->gatewayDetailRows();
        $refunded = $payment->resolvedRefunded();
        $fee = $payment->resolvedFee();
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- الشريط الجانبي --}}
        <div class="space-y-6 lg:order-1">

            {{-- ملخص العملية --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="coin" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">ملخص العملية</h2>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">المبلغ</span>
                        <span class="font-medium text-gray-800" dir="ltr">
                            {{ money_format($payment->amount, currency: $payment->currency) }}
                        </span>
                    </div>
                    @if ($fee)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">رسوم البوابة</span>
                            <span class="font-medium text-gray-800" dir="ltr">
                                {{ money_format($fee, currency: $payment->currency) }}
                            </span>
                        </div>
                    @endif
                    @if ($refunded !== null && $refunded > 0)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">المبلغ المسترجع</span>
                            <span class="font-medium text-red-600" dir="ltr">
                                −{{ money_format($refunded, currency: $payment->currency) }}
                            </span>
                        </div>
                    @endif

                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800">الإجمالي</span>
                            <span class="text-xl font-bold text-primary-700" dir="ltr">
                                {{ money_format($payment->amount, currency: $payment->currency) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">التحصيل</span>
                            <span class="font-medium {{ $payment->resolvedCaptured() ? 'text-emerald-700' : 'text-gray-800' }}">
                                {{ $payment->capturedLabel() }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">العملة</span>
                            <span class="font-medium text-gray-800" dir="ltr">{{ $payment->currency }}</span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- الدافع --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="user" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">الدافع</h2>
                </div>
                <div class="p-5">
                    @if ($payment->client)
                        <div class="flex items-center gap-3">
                            <img src="{{ $payment->client->avatar }}" alt="{{ $payment->client->name }}"
                                class="h-12 w-12 shrink-0 rounded-full bg-gray-100 object-cover">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900">{{ $payment->client->name }}</p>
                                @if ($payment->client->email)
                                    <p class="truncate text-sm text-gray-500">{{ $payment->client->email }}</p>
                                @endif
                                @if ($payment->client->phone)
                                    <p class="text-sm text-gray-500" dir="ltr">{{ $payment->client->phone }}</p>
                                @endif
                            </div>
                        </div>

                        <ui:button
                            href="{{ route('admin.clients.detail', ['id' => $payment->client->uuid]) }}"
                            label="عرض ملف العميل"
                            variant="outline"
                            class="mt-4 w-full"
                            wire:navigate
                        />
                    @elseif ($payment->user)
                        <div class="flex items-center gap-3">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100 text-lg font-semibold text-gray-600">
                                {{ mb_substr($payment->user->name, 0, 1) }}
                            </div>
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900">{{ $payment->user->name }}</p>
                                @if ($payment->user->email)
                                    <p class="truncate text-sm text-gray-500">{{ $payment->user->email }}</p>
                                @endif
                                <p class="text-xs text-gray-400">مستخدم</p>
                            </div>
                        </div>
                    @elseif ($payment->resolvedSourceName())
                        <div class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                <ui:icon name="user" class="h-6 w-6" />
                            </div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">{{ $payment->resolvedSourceName() }}</p>
                            <p class="mt-1 text-xs text-gray-400">حامل البطاقة</p>
                        </div>
                    @else
                        <div class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                <ui:icon name="user" class="h-6 w-6" />
                            </div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">—</p>
                            <p class="mt-1 text-xs text-gray-400">لا توجد بيانات دافع</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- معلومات تقنية --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="info-circle" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">معلومات تقنية</h2>
                </div>
                <div class="space-y-3 p-5 text-sm">
                    <div>
                        <p class="text-xs text-gray-400">المعرّف</p>
                        <p class="mt-0.5 break-all font-mono text-xs text-gray-600" dir="ltr">{{ $payment->uuid }}</p>
                    </div>
                    @if ($payment->payment_id)
                        <div>
                            <p class="text-xs text-gray-400">رقم العملية</p>
                            <p class="mt-0.5 break-all font-mono text-xs text-gray-600" dir="ltr">{{ $payment->payment_id }}</p>
                        </div>
                    @endif
                    @if ($gatewayId = $payment->resolvedGatewayId())
                        <div>
                            <p class="text-xs text-gray-400">معرّف البوابة</p>
                            <p class="mt-0.5 break-all font-mono text-xs text-gray-600" dir="ltr">{{ $gatewayId }}</p>
                        </div>
                    @endif
                    @if ($ip = $payment->resolvedIp())
                        <div>
                            <p class="text-xs text-gray-400">عنوان IP</p>
                            <p class="mt-0.5 font-mono text-xs text-gray-600" dir="ltr">{{ $ip }}</p>
                        </div>
                    @endif
                    @if ($payment->reason)
                        <div>
                            <p class="text-xs text-gray-400">السبب</p>
                            <p class="mt-0.5 break-all text-xs text-gray-600" dir="ltr">{{ $payment->reason }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-400">تاريخ الإنشاء</p>
                        <p class="mt-0.5 text-xs font-medium text-gray-800">
                            {{ $payment->created_at->translatedFormat('d M Y h:i A') }}
                        </p>
                    </div>
                    @if ($payment->updated_at->ne($payment->created_at))
                        <div>
                            <p class="text-xs text-gray-400">آخر تحديث</p>
                            <p class="mt-0.5 text-xs font-medium text-gray-800">
                                {{ $payment->updated_at->translatedFormat('d M Y h:i A') }}
                            </p>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        {{-- المحتوى الرئيسي --}}
        <div class="space-y-6 lg:order-2 lg:col-span-2">

            {{-- تفاصيل الدفع --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="receipt" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">تفاصيل الدفع</h2>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">رقم العملية</dt>
                            <dd class="text-sm font-semibold text-gray-900">#{{ $payment->id }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">الحالة</dt>
                            <dd>
                                <ui:badge color="{{ $payment->statusBadgeColor() }}" size="sm">
                                    {{ $payment->statusLabel() }}
                                </ui:badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">نوع العملية</dt>
                            <dd class="text-sm text-gray-800">{{ $payment->reasonLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">التصنيف</dt>
                            <dd class="text-sm text-gray-800">{{ $payment->typeLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">تاريخ العملية</dt>
                            <dd class="text-sm text-gray-800">
                                {{ $paidAt->translatedFormat('d M Y') }}
                                <span class="text-gray-400" dir="ltr">{{ $paidAt->translatedFormat('h:i A') }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">بوابة الدفع</dt>
                            <dd class="flex items-center gap-1.5 text-sm text-gray-800">
                                <ui:icon name="building-bank" class="h-4 w-4 text-gray-400" />
                                {{ $payment->gatewayLabel() }}
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">طريقة الدفع</dt>
                            <dd class="flex items-center gap-1.5 text-sm text-gray-800">
                                <ui:icon name="credit-card" class="h-4 w-4 text-gray-400" />
                                {{ $payment->sourceTypeLabel() }}
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">التحصيل</dt>
                            <dd>
                                <ui:badge color="{{ $payment->resolvedCaptured() ? 'green' : 'gray' }}" size="sm">
                                    {{ $payment->capturedLabel() }}
                                </ui:badge>
                            </dd>
                        </div>
                        @if ($refundedAt = $payment->resolvedRefundedAt())
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">تاريخ الاسترجاع</dt>
                                <dd class="text-sm text-red-600">
                                    {{ $refundedAt->translatedFormat('d M Y') }}
                                    <span dir="ltr">{{ $refundedAt->translatedFormat('h:i A') }}</span>
                                </dd>
                            </div>
                        @endif
                        @if ($payment->payment_id)
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">معرّف البوابة</dt>
                                <dd class="font-mono text-sm text-gray-800" dir="ltr">{{ $payment->payment_id }}</dd>
                            </div>
                        @endif
                        @if ($sourceName = $payment->resolvedSourceName())
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">اسم حامل البطاقة</dt>
                                <dd class="text-sm text-gray-800">{{ $sourceName }}</dd>
                            </div>
                        @endif
                        @if ($payment->cardDisplay())
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">البطاقة</dt>
                                <dd class="font-mono text-sm text-gray-800" dir="ltr">{{ $payment->cardDisplay() }}</dd>
                            </div>
                        @endif
                        @if ($payment->resolvedSourceExpiryMonth() && $payment->resolvedSourceExpiryYear())
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">انتهاء البطاقة</dt>
                                <dd class="font-mono text-sm text-gray-800" dir="ltr">
                                    {{ str_pad((string) $payment->resolvedSourceExpiryMonth(), 2, '0', STR_PAD_LEFT) }}/{{ $payment->resolvedSourceExpiryYear() }}
                                </dd>
                            </div>
                        @endif
                        @if ($sourceNumber = $payment->resolvedSourceNumber())
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">رقم المصدر</dt>
                                <dd class="font-mono text-sm text-gray-800" dir="ltr">{{ $sourceNumber }}</dd>
                            </div>
                        @endif
                        @if ($sourceMessage = $payment->resolvedSourceMessage())
                            <div class="sm:col-span-2">
                                <dt class="mb-1 text-xs text-gray-400">رسالة البوابة</dt>
                                <dd class="text-sm text-gray-800">{{ $sourceMessage }}</dd>
                            </div>
                        @endif
                        @if ($referenceNumber = $payment->resolvedSourceReferenceNumber())
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">رقم المرجع</dt>
                                <dd class="font-mono text-sm text-gray-800" dir="ltr">{{ $referenceNumber }}</dd>
                            </div>
                        @endif
                        @if ($invoiceId = $payment->resolvedInvoiceId())
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">رقم الفاتورة</dt>
                                <dd class="text-sm text-gray-800">{{ $invoiceId }}</dd>
                            </div>
                        @endif
                        @if ($payment->resolvedDescription())
                            <div class="sm:col-span-2">
                                <dt class="mb-1 text-xs text-gray-400">الوصف</dt>
                                <dd class="text-sm text-gray-800">{{ $payment->resolvedDescription() }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>

            {{-- بيانات البوابة --}}
            @if ($gatewayRows !== [])
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="building-bank" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">بيانات البوابة</h2>
                    </div>
                    <div class="p-5">
                        <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                            @foreach ($gatewayRows as $row)
                                <div>
                                    <dt class="mb-1 text-xs text-gray-400">{{ $row['label'] }}</dt>
                                    <dd @class([
                                        'text-sm text-gray-800',
                                        'font-mono' => $row['mono'] ?? false,
                                        'font-medium' => ! ($row['mono'] ?? false),
                                    ]) @if ($row['dir'] ?? null) dir="{{ $row['dir'] }}" @endif>
                                        {{ $row['value'] }}
                                    </dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                </section>
            @endif

            {{-- الباقة / المنتج --}}
            @if ($payment->purchased)
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="package" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">الباقة / المنتج</h2>
                    </div>
                    <div class="p-5">
                        @if ($payment->purchased instanceof \App\Models\Plan)
                            <div class="flex items-center justify-between gap-4 rounded-lg bg-gray-50 p-4">
                                <div>
                                    <p class="font-semibold text-gray-800">{{ $payment->purchased->label ?? $payment->purchased->name }}</p>
                                    <p class="mt-1 text-sm text-gray-500">{{ $payment->purchased->billingLabel() }}</p>
                                </div>
                                <p class="text-base font-bold text-gray-900" dir="ltr">
                                    {{ $payment->purchased->formattedPrice() }} {{ $payment->currency }}
                                </p>
                            </div>
                        @else
                            <p class="text-sm text-gray-800">{{ class_basename($payment->purchased_type) }} #{{ $payment->purchased_id }}</p>
                        @endif
                    </div>
                </section>
            @endif

            {{-- الطلب المرتبط --}}
            @if ($payment->order)
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="package" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">الطلب المرتبط</h2>
                    </div>
                    <div class="p-5">
                        <a href="{{ route('admin.orders.detail', ['id' => $payment->order->uuid]) }}" wire:navigate
                            class="flex items-center justify-between gap-4 rounded-lg bg-gray-50 p-4 transition hover:bg-gray-100">
                            <div>
                                <p class="font-semibold text-gray-800">طلب #{{ $payment->order->number ?? $payment->order->id }}</p>
                                <p class="mt-1 text-sm text-gray-500">{{ $payment->order->statusLabel() }}</p>
                            </div>
                            <div class="text-end">
                                <p class="font-bold text-gray-900" dir="ltr">{{ $payment->order->formattedGrandTotal() }}</p>
                                <ui:icon name="external-link" class="!mt-2 !h-4 !w-4 text-primary-500" />
                            </div>
                        </a>
                    </div>
                </section>
            @endif

            {{-- ملاحظات --}}
            @if (filled($payment->notes))
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="note" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">ملاحظات</h2>
                    </div>
                    <div class="p-5">
                        <p class="rounded-lg bg-gray-50 p-4 text-sm leading-relaxed text-gray-700">{{ $payment->notes }}</p>
                    </div>
                </section>
            @endif
        </div>
    </div>

</ui:container>

<?php

use App\Models\Payment;

new class extends \Livewire\Component {
    public Payment $payment;

    public function mount(string $uuid): void
    {
        $query = Payment::query()
            ->with(['user', 'client', 'order', 'purchased']);

        if ($tenantId = currentTenantId()) {
            $query->where('tenant_id', $tenantId);
        }

        $this->payment = $query->where('uuid', $uuid)->firstOrFail();
    }

    public function rendering($view): void
    {
        $view->title(__('Orders').' / عملية #'.$this->payment->id)->layout('admin::layout');
    }
}; ?>
