<ui:container title="{{ __('Orders') }} / عملية #{{ $payment->id }}"
    backRoute="{{ route('admin.orders.home', ['tab' => 'payments']) }}">

    @php
        $paidAt = $payment->resolvedGatewayCreatedAt() ?? $payment->created_at;
        $gatewayRows = $payment->gatewayDetailRows();
    @endphp

    <div class="space-y-6">

        {{-- بطاقة رأسية --}}
        <section
            class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-50 via-white to-primary-50">
            <div class="pointer-events-none absolute inset-0 opacity-40"
                style="background-image: radial-gradient(circle at 1px 1px, #e5e7eb 1px, transparent 0); background-size: 24px 24px;">
            </div>
            <div class="pointer-events-none absolute -top-24 -left-24 h-64 w-64 rounded-full bg-primary-100/40 blur-3xl">
            </div>
            <div class="pointer-events-none absolute -bottom-20 -right-16 h-56 w-56 rounded-full bg-gray-100/80 blur-3xl">
            </div>

            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="min-w-0 space-y-4">
                        <div class="flex flex-wrap items-center gap-2">
                            <span
                                class="inline-flex items-center gap-1.5 rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700 ring-1 ring-primary-100">
                                <ui:icon name="receipt" class="h-3.5 w-3.5" />
                                عملية دفع
                            </span>
                            <ui:badge color="{{ $payment->statusBadgeColor() }}" size="sm">
                                {{ $payment->statusLabel() }}
                            </ui:badge>
                            @if ($payment->gateway)
                                <ui:badge color="gray" size="sm">{{ $payment->gatewayLabel() }}</ui:badge>
                            @endif
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">{{ $payment->reasonLabel() }}</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                                #{{ $payment->id }}
                            </h1>
                            @if ($payment->resolvedDescription())
                                <p class="mt-2 text-sm text-gray-600">{{ $payment->resolvedDescription() }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-600">
                            <span class="inline-flex items-center gap-1.5">
                                <ui:icon name="calendar" class="h-4 w-4 text-gray-400" />
                                {{ $paidAt->translatedFormat('d M Y') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5" dir="ltr">
                                <ui:icon name="clock" class="h-4 w-4 text-gray-400" />
                                {{ $paidAt->translatedFormat('h:i A') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <ui:icon name="credit-card" class="h-4 w-4 text-gray-400" />
                                {{ $payment->sourceTypeLabel() }}
                            </span>
                            @if ($payment->payment_id)
                                <span class="inline-flex items-center gap-1.5 font-mono text-xs" dir="ltr">
                                    <ui:icon name="hash" class="h-4 w-4 text-gray-400" />
                                    {{ $payment->payment_id }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 rounded-2xl bg-white p-5 text-center sm:min-w-52 sm:text-end lg:text-end">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">المبلغ</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-primary-700" dir="ltr">
                            {{ money_format($payment->amount, currency: $payment->currency) }}
                        </p>
                        @if ($payment->cardDisplay())
                            <p class="mt-2 font-mono text-xs text-gray-500" dir="ltr">{{ $payment->cardDisplay() }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- مؤشرات سريعة --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <ui:icon name="coin" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">العملة</p>
                        <p class="text-lg font-bold text-gray-800" dir="ltr">{{ $payment->currency }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <ui:icon name="building-bank" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">بوابة الدفع</p>
                        <p class="truncate text-sm font-bold text-gray-800">{{ $payment->gatewayLabel() }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <ui:icon name="credit-card" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">طريقة الدفع</p>
                        <p class="truncate text-sm font-bold text-gray-800">{{ $payment->sourceTypeLabel() }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <ui:icon name="calendar-event" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">تاريخ العملية</p>
                        <p class="text-sm font-bold text-gray-800">{{ $paidAt->translatedFormat('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-6 lg:col-span-2">

                <ui:box title="تفاصيل الدفع" icon="receipt">
                    <div class="p-4 sm:p-5">
                        <dl class="divide-y divide-gray-100">
                            <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                <dt class="text-gray-500">نوع العملية</dt>
                                <dd class="font-medium text-gray-800">{{ $payment->reasonLabel() }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                <dt class="text-gray-500">التصنيف</dt>
                                <dd class="font-medium text-gray-800">{{ $payment->typeLabel() }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                <dt class="text-gray-500">الحالة</dt>
                                <dd>
                                    <ui:badge color="{{ $payment->statusBadgeColor() }}" size="sm">
                                        {{ $payment->statusLabel() }}
                                    </ui:badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                <dt class="text-gray-500">التحصيل</dt>
                                <dd>
                                    <ui:badge color="{{ $payment->resolvedCaptured() ? 'green' : 'gray' }}" size="sm">
                                        {{ $payment->capturedLabel() }}
                                    </ui:badge>
                                </dd>
                            </div>
                            @if ($refundedAt = $payment->resolvedRefundedAt())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">تاريخ الاسترجاع</dt>
                                    <dd class="font-medium text-red-600">{{ $refundedAt->translatedFormat('d M Y h:i A') }}</dd>
                                </div>
                            @endif
                            @if ($fee = $payment->resolvedFee())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">رسوم البوابة</dt>
                                    <dd class="font-medium text-gray-800" dir="ltr">{{ money_format($fee, currency: $payment->currency) }}</dd>
                                </div>
                            @endif
                            @if (($refunded = $payment->resolvedRefunded()) !== null && $refunded > 0)
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">المبلغ المسترجع</dt>
                                    <dd class="font-medium text-red-600" dir="ltr">{{ money_format($refunded, currency: $payment->currency) }}</dd>
                                </div>
                            @endif
                            @if ($sourceName = $payment->resolvedSourceName())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">اسم حامل البطاقة</dt>
                                    <dd class="font-medium text-gray-800">{{ $sourceName }}</dd>
                                </div>
                            @endif
                            @if ($payment->cardDisplay())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">البطاقة</dt>
                                    <dd class="font-mono font-medium text-gray-800" dir="ltr">{{ $payment->cardDisplay() }}</dd>
                                </div>
                            @endif
                            @if ($payment->resolvedSourceExpiryMonth() && $payment->resolvedSourceExpiryYear())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">انتهاء البطاقة</dt>
                                    <dd class="font-mono font-medium text-gray-800" dir="ltr">
                                        {{ str_pad((string) $payment->resolvedSourceExpiryMonth(), 2, '0', STR_PAD_LEFT) }}/{{ $payment->resolvedSourceExpiryYear() }}
                                    </dd>
                                </div>
                            @endif
                            @if ($sourceNumber = $payment->resolvedSourceNumber())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">رقم المصدر</dt>
                                    <dd class="font-mono text-sm text-gray-800" dir="ltr">{{ $sourceNumber }}</dd>
                                </div>
                            @endif
                            @if ($sourceMessage = $payment->resolvedSourceMessage())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">رسالة البوابة</dt>
                                    <dd class="font-medium text-gray-800">{{ $sourceMessage }}</dd>
                                </div>
                            @endif
                            @if ($referenceNumber = $payment->resolvedSourceReferenceNumber())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">رقم المرجع</dt>
                                    <dd class="font-mono text-sm text-gray-800" dir="ltr">{{ $referenceNumber }}</dd>
                                </div>
                            @endif
                            @if ($invoiceId = $payment->resolvedInvoiceId())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">رقم الفاتورة</dt>
                                    <dd class="font-medium text-gray-800">{{ $invoiceId }}</dd>
                                </div>
                            @endif
                            @if ($ip = $payment->resolvedIp())
                                <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                    <dt class="text-gray-500">عنوان IP</dt>
                                    <dd class="font-mono text-sm text-gray-800" dir="ltr">{{ $ip }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </ui:box>

                @if ($gatewayRows !== [])
                    <ui:box title="بيانات البوابة" icon="building-bank">
                        <div class="p-4 sm:p-5">
                            <dl class="divide-y divide-gray-100">
                                @foreach ($gatewayRows as $row)
                                    <div class="flex items-center justify-between gap-4 py-3 text-sm">
                                        <dt class="text-gray-500">{{ $row['label'] }}</dt>
                                        <dd @class([
                                            'font-medium text-gray-800',
                                            'font-mono' => $row['mono'] ?? false,
                                        ]) @if ($row['dir'] ?? null) dir="{{ $row['dir'] }}" @endif>
                                            {{ $row['value'] }}
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </ui:box>
                @endif

                @if ($payment->purchased)
                    <ui:box title="الباقة / المنتج" icon="package">
                        <div class="p-4 sm:p-5">
                            @if ($payment->purchased instanceof \App\Models\Plan)
                                <div class="flex items-center justify-between gap-4 rounded-xl bg-gray-50/60 p-4">
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
                    </ui:box>
                @endif

                @if ($payment->order)
                    <ui:box title="الطلب المرتبط" icon="message-2">
                        <div class="p-4 sm:p-5">
                            <a href="{{ route('admin.orders.detail', ['id' => $payment->order->uuid]) }}" wire:navigate
                                class="flex items-center justify-between gap-4 rounded-xl bg-gray-50/60 p-4 transition hover:bg-gray-100">
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
                    </ui:box>
                @endif

                @if (filled($payment->notes))
                    <ui:box title="ملاحظات" icon="note">
                        <div class="p-4 sm:p-5">
                            <div class="flex gap-3 rounded-xl bg-amber-50/80 p-4 ring-1 ring-amber-100">
                                <ui:icon name="note" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                                <p class="text-sm leading-relaxed text-gray-700">{{ $payment->notes }}</p>
                            </div>
                        </div>
                    </ui:box>
                @endif
            </div>

            <div class="space-y-6">

                {{-- الدافع --}}
                <ui:box title="معلومات الدافع" icon="user">
                    <div class="p-4 sm:p-5">
                        @if ($payment->client)
                            <div class="flex items-center gap-4">
                                <img src="{{ $payment->client->avatar }}" alt="{{ $payment->client->name }}"
                                    class="h-14 w-14 rounded-2xl bg-gray-100 object-cover ring-2 ring-white">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.clients.detail', ['id' => $payment->client->uuid]) }}"
                                        wire:navigate
                                        class="block truncate text-base font-bold text-gray-900 transition hover:text-primary-600">
                                        {{ $payment->client->name }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-gray-400">عميل</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                @if ($payment->client->email)
                                    <a href="mailto:{{ $payment->client->email }}"
                                        class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400">
                                            <ui:icon name="mail" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">{{ __('Email') }}</p>
                                            <p class="truncate text-sm font-medium text-gray-800">{{ $payment->client->email }}</p>
                                        </div>
                                    </a>
                                @endif
                                @if ($payment->client->phone)
                                    <a href="tel:{{ $payment->client->phone }}"
                                        class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400">
                                            <ui:icon name="phone" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">{{ __('Phone') }}</p>
                                            <p class="text-sm font-medium text-gray-800" dir="ltr">{{ $payment->client->phone }}</p>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @elseif ($payment->user)
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-lg font-bold text-gray-600 ring-2 ring-white">
                                    {{ mb_substr($payment->user->name, 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-base font-bold text-gray-900">{{ $payment->user->name }}</p>
                                    <p class="mt-0.5 text-xs text-gray-400">مستخدم</p>
                                </div>
                            </div>

                            @if ($payment->user->email)
                                <div class="mt-5">
                                    <a href="mailto:{{ $payment->user->email }}"
                                        class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100">
                                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400">
                                            <ui:icon name="mail" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">{{ __('Email') }}</p>
                                            <p class="truncate text-sm font-medium text-gray-800">{{ $payment->user->email }}</p>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        @elseif ($payment->resolvedSourceName())
                            <div class="flex flex-col items-center rounded-xl bg-gray-50 py-8 text-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-gray-300">
                                    <ui:icon name="user" class="h-7 w-7" />
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-700">{{ $payment->resolvedSourceName() }}</p>
                                <p class="mt-1 text-xs text-gray-400">حامل البطاقة</p>
                            </div>
                        @else
                            <div class="flex flex-col items-center rounded-xl bg-gray-50 py-8 text-center">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-gray-300">
                                    <ui:icon name="user" class="h-7 w-7" />
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-700">—</p>
                                <p class="mt-1 text-xs text-gray-400">لا توجد بيانات دافع</p>
                            </div>
                        @endif
                    </div>
                </ui:box>

                {{-- ملخص مالي --}}
                <ui:box title="ملخص العملية" icon="coin">
                    <div class="p-4 sm:p-5">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المبلغ</span>
                                <span class="font-medium text-gray-800" dir="ltr">
                                    {{ money_format($payment->amount, currency: $payment->currency) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">العملة</span>
                                <span class="font-medium text-gray-800" dir="ltr">{{ $payment->currency }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">بوابة الدفع</span>
                                <span class="font-medium text-gray-800">{{ $payment->gatewayLabel() }}</span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">التحصيل</span>
                                <span class="font-medium {{ $payment->resolvedCaptured() ? 'text-emerald-600' : 'text-gray-800' }}">
                                    {{ $payment->capturedLabel() }}
                                </span>
                            </div>
                            @if ($fee = $payment->resolvedFee())
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">رسوم البوابة</span>
                                    <span class="font-medium text-gray-800" dir="ltr">{{ money_format($fee, currency: $payment->currency) }}</span>
                                </div>
                            @endif

                            <div class="my-1 border-t border-dashed border-gray-200"></div>

                            <div class="flex items-center justify-between rounded-xl bg-gradient-to-l from-primary-50 to-slate-50 px-4 py-3.5">
                                <span class="text-sm font-semibold text-gray-800">الإجمالي</span>
                                <span class="text-lg font-bold text-primary-700" dir="ltr">
                                    {{ money_format($payment->amount, currency: $payment->currency) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </ui:box>

                {{-- معلومات تقنية --}}
                <ui:box title="معلومات تقنية" icon="info-circle">
                    <div class="p-4 sm:p-5">
                        <dl class="space-y-3 text-sm">
                            <div class="flex items-start justify-between gap-3">
                                <dt class="shrink-0 text-gray-500">المعرّف</dt>
                                <dd class="break-all text-end font-mono text-xs text-gray-600" dir="ltr">{{ $payment->uuid }}</dd>
                            </div>
                            @if ($payment->payment_id)
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="shrink-0 text-gray-500">رقم العملية</dt>
                                    <dd class="break-all text-end font-mono text-xs text-gray-600" dir="ltr">{{ $payment->payment_id }}</dd>
                                </div>
                            @endif
                            @if ($gatewayId = $payment->resolvedGatewayId())
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="shrink-0 text-gray-500">معرّف البوابة</dt>
                                    <dd class="break-all text-end font-mono text-xs text-gray-600" dir="ltr">{{ $gatewayId }}</dd>
                                </div>
                            @endif
                            @if ($ip = $payment->resolvedIp())
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-gray-500">IP</dt>
                                    <dd class="font-mono text-xs text-gray-600" dir="ltr">{{ $ip }}</dd>
                                </div>
                            @endif
                            @if ($payment->reason)
                                <div class="flex items-start justify-between gap-3">
                                    <dt class="shrink-0 text-gray-500">السبب</dt>
                                    <dd class="break-all text-end text-xs text-gray-600" dir="ltr">{{ $payment->reason }}</dd>
                                </div>
                            @endif
                            <div class="flex items-center justify-between gap-3">
                                <dt class="text-gray-500">تاريخ الإنشاء</dt>
                                <dd class="text-xs font-medium text-gray-800">{{ $payment->created_at->translatedFormat('d M Y h:i A') }}</dd>
                            </div>
                            @if ($payment->updated_at->ne($payment->created_at))
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-gray-500">آخر تحديث</dt>
                                    <dd class="text-xs font-medium text-gray-800">{{ $payment->updated_at->translatedFormat('d M Y h:i A') }}</dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </ui:box>
            </div>
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
