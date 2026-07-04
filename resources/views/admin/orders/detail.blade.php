<ui:container title="{{ __('Orders') }} / #{{ $order->number ?? $order->id }}"
    backRoute="{{ route('admin.orders.home') }}">

    @php
        $issuedAt = $order->issued_at ?? $order->created_at;
        $itemsCount = $items->count();
        $totalQty = $items->sum('qty');
    @endphp

    <div class="space-y-6">

        {{-- بطاقة رأسية للطلب --}}
        <section
            class="relative overflow-hidden rounded-2xl  bg-gradient-to-br from-gray-50 via-white to-primary-50">
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
                                طلب
                            </span>
                            <ui:badge color="{{ $order->statusBadgeColor() }}" size="sm">
                                {{ $order->statusLabel() }}
                            </ui:badge>
                            <ui:badge color="{{ $order->paymentStatusBadgeColor() }}" size="sm">
                                {{ $order->paymentStatusLabel() }}
                            </ui:badge>
                            <ui:button
                                wire:click="openChangeStatusModal"
                                label="تغيير الحالة"
                                icon="refresh"
                                variant="outline"
                                class="!h-8 !px-3 !text-xs"
                            />
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">رقم الطلب</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">
                                #{{ $order->number ?? $order->id }}
                            </h1>
                        </div>

                        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-600">
                            <span class="inline-flex items-center gap-1.5">
                                <ui:icon name="calendar" class="h-4 w-4 text-gray-400" />
                                {{ $issuedAt->translatedFormat('d M Y') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5" dir="ltr">
                                <ui:icon name="clock" class="h-4 w-4 text-gray-400" />
                                {{ $issuedAt->translatedFormat('h:i A') }}
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <ui:icon name="credit-card" class="h-4 w-4 text-gray-400" />
                                {{ $order->paymentMethodLabel() }}
                            </span>
                        </div>
                    </div>

                    <div
                        class="shrink-0 rounded-2xl  bg-white p-5 text-center sm:min-w-52 sm:text-end lg:text-end">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">الإجمالي النهائي</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-primary-700" dir="ltr">
                            {{ $order->formattedGrandTotal() }}
                        </p>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ $itemsCount }} {{ $itemsCount === 1 ? 'عنصر' : 'عناصر' }}
                            @if ($totalQty > $itemsCount)
                                · {{ $totalQty }} وحدة
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- مؤشرات سريعة --}}
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl  bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <ui:icon name="package" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">العناصر</p>
                        <p class="text-lg font-bold text-gray-800">{{ $itemsCount }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl  bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <ui:icon name="coin" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">العملة</p>
                        <p class="text-lg font-bold text-gray-800" dir="ltr">{{ $order->currency_code }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl  bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <ui:icon name="credit-card" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">طريقة الدفع</p>
                        <p class="truncate text-sm font-bold text-gray-800">{{ $order->paymentMethodLabel() }}</p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl  bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <ui:icon name="calendar-event" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">تاريخ الإصدار</p>
                        <p class="text-sm font-bold text-gray-800">{{ $issuedAt->translatedFormat('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            {{-- العمود الرئيسي: عناصر الطلب --}}
            <div class="space-y-6 lg:col-span-2">

                <ui:box title="عناصر الطلب" class="">
                    <x-slot:rightAction>
                        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500">
                            {{ $itemsCount }} {{ $itemsCount === 1 ? 'عنصر' : 'عناصر' }}
                        </span>
                    </x-slot:rightAction>

                    <div class="p-4 sm:p-5">
                        @if ($items->isEmpty())
                            <ui:empty subtitle="لم يُضف أي منتج أو خدمة لهذا الطلب بعد.">
                                لا توجد عناصر في هذا الطلب.
                                <x-slot:icon>
                                    <ui:icon name="shopping-cart-off" class="!h-12 !w-12 p-0.5 text-gray-400" />
                                </x-slot:icon>
                            </ui:empty>
                        @else
                            <div class="space-y-3">
                                @foreach ($items as $item)
                                    <div wire:key="item-{{ $item->id }}"
                                        class="group flex flex-col gap-4 rounded-xl  bg-gray-50/60 p-4 transition hover:border-gray-200 hover:bg-white sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-gray-400 ring-1 ring-gray-100 transition group-hover:text-primary-500">
                                                <ui:icon name="package" class="h-5 w-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-gray-800">{{ $item->name }}</p>
                                                @if ($item->sku)
                                                    <p class="mt-0.5 text-xs text-gray-400" dir="ltr">
                                                        SKU: {{ $item->sku }}
                                                    </p>
                                                @endif
                                                <div class="mt-2 flex flex-wrap items-center gap-2">
                                                    <span
                                                        class="inline-flex items-center rounded-md bg-white px-2 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-gray-200">
                                                        الكمية: {{ $item->qty }}
                                                    </span>
                                                    @if ($item->discount_total > 0)
                                                        <span
                                                            class="inline-flex items-center rounded-md bg-red-50 px-2 py-0.5 text-xs font-medium text-red-600">
                                                            خصم
                                                            <span class="ms-1" dir="ltr">
                                                                {{ $order->formatAmount($item->discount_total) }}
                                                            </span>
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 items-center justify-between gap-6 sm:flex-col sm:items-end sm:justify-center sm:gap-1 sm:text-end">
                                            <div class="text-end">
                                                <p class="text-xs text-gray-400">سعر الوحدة</p>
                                                <p class="text-sm font-medium text-gray-600" dir="ltr">
                                                    {{ $order->formatAmount($item->unit_price) }}
                                                    {{ $order->currency_code }}
                                                </p>
                                            </div>
                                            <div class="text-end">
                                                <p class="text-xs text-gray-400">الإجمالي</p>
                                                <p class="text-base font-bold text-gray-900" dir="ltr">
                                                    {{ $order->formatAmount($item->line_total) }}
                                                    {{ $order->currency_code }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </ui:box>

                <ui:box title="المدفوعات" class="">
                    <x-slot:rightAction>
                        <div class="flex items-center gap-2">
                            @if ($order->due_total > 0)
                                <ui:button
                                    wire:click="openAddPaymentModal"
                                    label="تسجيل دفعة"
                                    icon="plus"
                                    variant="outline"
                                    class="!h-8 !px-3 !text-xs"
                                />
                            @endif
                            <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500">
                                {{ $this->orderPayments->count() }} {{ $this->orderPayments->count() === 1 ? 'دفعة' : 'دفعات' }}
                            </span>
                        </div>
                    </x-slot:rightAction>

                    <div class="p-4 sm:p-5">
                        @if ($this->orderPayments->isEmpty())
                            <ui:empty subtitle="سجّل دفعة لإنشاء فاتورة وربطها بهذا الطلب.">
                                لا توجد مدفوعات بعد.
                                <x-slot:icon>
                                    <ui:icon name="coin" class="!h-12 !w-12 p-0.5 text-gray-400" />
                                </x-slot:icon>
                            </ui:empty>
                        @else
                            <div class="space-y-3">
                                @foreach ($this->orderPayments as $payment)
                                    <div wire:key="payment-{{ $payment->id }}"
                                        class="flex flex-col gap-3 rounded-xl border border-gray-100 bg-gray-50/60 p-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div class="flex min-w-0 items-start gap-3">
                                            <div
                                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white text-emerald-500 ring-1 ring-gray-100">
                                                <ui:icon name="coin" class="h-5 w-5" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <a href="{{ route('admin.orders.payments.detail', ['uuid' => $payment->uuid]) }}"
                                                        wire:navigate
                                                        class="font-semibold text-gray-800 transition hover:text-primary-600">
                                                        دفعة #{{ $payment->id }}
                                                    </a>
                                                    <ui:badge color="{{ $payment->statusBadgeColor() }}" size="sm">
                                                        {{ $payment->statusLabel() }}
                                                    </ui:badge>
                                                    @if ($payment->invoice)
                                                        <span class="text-xs text-gray-400">{{ $payment->invoice->s_number }}</span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500">
                                                    {{ Order::paymentMethodOptions()[$payment->source_type] ?? $payment->sourceTypeLabel() }}
                                                    @if ($payment->resolvedDescription())
                                                        · {{ $payment->resolvedDescription() }}
                                                    @endif
                                                </p>
                                                <p class="mt-1 text-xs text-gray-400">
                                                    {{ $payment->created_at->translatedFormat('d M Y h:i A') }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="shrink-0 text-end">
                                            <p class="text-lg font-bold text-emerald-700" dir="ltr">
                                                {{ $payment->formattedAmount() }} {{ $payment->currency }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </ui:box>

                @if ($order->notes)
                    <ui:box title="ملاحظات" class="">
                        <div class="p-4 sm:p-5">
                            <div class="flex gap-3 rounded-xl bg-amber-50/80 p-4 ring-1 ring-amber-100">
                                <ui:icon name="note" class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" />
                                <p class="text-sm leading-relaxed text-gray-700">{{ $order->notes }}</p>
                            </div>
                        </div>
                    </ui:box>
                @endif

                <ui:box title="سجل النشاط" class="">
                    <x-slot:rightAction>
                        <span class="rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-500">
                            {{ $this->activityTimeline->count() }} {{ $this->activityTimeline->count() === 1 ? 'حدث' : 'أحداث' }}
                        </span>
                    </x-slot:rightAction>

                    <div class="p-4 sm:p-5">
                        @if ($this->activityTimeline->isEmpty())
                            <ui:empty subtitle="ستظهر تغييرات الحالة والنشاطات هنا.">
                                لا يوجد سجل نشاط بعد.
                                <x-slot:icon>
                                    <ui:icon name="history" class="!h-12 !w-12 p-0.5 text-gray-400" />
                                </x-slot:icon>
                            </ui:empty>
                        @else
                            <div class="relative space-y-0">
                                @foreach ($this->activityTimeline as $entry)
                                    <div wire:key="activity-{{ $entry['key'] }}"
                                        class="relative flex gap-4 pb-6 last:pb-0">
                                        @if (! $loop->last)
                                            <span
                                                class="absolute top-10 bottom-0 w-px bg-gray-200"
                                                style="inset-inline-start: 1.25rem;"></span>
                                        @endif

                                        <div
                                            class="relative z-10 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-4 ring-white {{ $entry['type'] === 'status' ? 'bg-primary-50 text-primary-600' : 'bg-gray-100 text-gray-500' }}">
                                            <ui:icon name="{{ $entry['type'] === 'status' ? 'refresh' : 'history' }}"
                                                class="h-5 w-5" />
                                        </div>

                                        <div class="min-w-0 flex-1 rounded-xl border border-gray-100 bg-gray-50/60 p-4">
                                            <div class="flex flex-wrap items-start justify-between gap-3">
                                                <div class="min-w-0 space-y-1">
                                                    <p class="text-sm font-semibold text-gray-800">
                                                        {{ $entry['title'] }}
                                                    </p>
                                                    @if ($entry['type'] === 'status')
                                                        <ui:badge color="{{ Order::statusBadgeColorFor($entry['status']) }}"
                                                            size="sm">
                                                            {{ Order::statusLabelFor($entry['status']) }}
                                                        </ui:badge>
                                                    @endif
                                                </div>
                                                <div class="shrink-0 text-end">
                                                    <p class="text-xs font-medium text-gray-500">
                                                        {{ $entry['date']->translatedFormat('d M Y') }}
                                                    </p>
                                                    <p class="text-[11px] text-gray-400" dir="ltr">
                                                        {{ $entry['date']->translatedFormat('h:i A') }}
                                                    </p>
                                                </div>
                                            </div>

                                            @if (filled($entry['reason'] ?? null))
                                                <p class="mt-3 rounded-lg bg-white px-3 py-2 text-sm leading-relaxed text-gray-600 ring-1 ring-gray-100">
                                                    {{ $entry['reason'] }}
                                                </p>
                                            @endif

                                            @if (filled($entry['details'] ?? null))
                                                <p class="mt-3 text-sm text-gray-500">
                                                    {{ $entry['details'] }}
                                                </p>
                                            @endif

                                            @if ($entry['causer'])
                                                <p class="mt-3 flex items-center gap-1.5 text-xs text-gray-400">
                                                    <ui:icon name="user" class="h-3.5 w-3.5" />
                                                    {{ $entry['causer']->name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </ui:box>
            </div>

            {{-- الشريط الجانبي --}}
            <div class="space-y-6">

                {{-- العميل --}}
                <ui:box title="معلومات العميل" class="">
                    <div class="p-4 sm:p-5">
                        @if ($order->client)
                            <div class="flex items-center gap-4">
                                <img src="{{ $order->client->avatar }}" alt="{{ $order->client->name }}"
                                    class="h-14 w-14 rounded-2xl bg-gray-100 object-cover ring-2 ring-white">
                                <div class="min-w-0">
                                    <a href="{{ route('admin.clients.detail', ['id' => $order->client->uuid]) }}"
                                        wire:navigate
                                        class="block truncate text-base font-bold text-gray-900 transition hover:text-primary-600">
                                        {{ $order->client->name }}
                                    </a>
                                    <p class="mt-0.5 text-xs text-gray-400">عميل مسجّل</p>
                                </div>
                            </div>

                            <div class="mt-5 space-y-3">
                                @if (data_get($order->client, 'email'))
                                    <a href="mailto:{{ $order->client->email }}"
                                        class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400">
                                            <ui:icon name="mail" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">{{ __('Email') }}</p>
                                            <p class="truncate text-sm font-medium text-gray-800">
                                                {{ $order->client->email }}
                                            </p>
                                        </div>
                                    </a>
                                @endif

                                @if (data_get($order->client, 'phone'))
                                    <a href="tel:{{ $order->client->phone }}"
                                        class="flex items-center gap-3 rounded-xl bg-gray-50 p-3 transition hover:bg-gray-100">
                                        <div
                                            class="flex h-9 w-9 items-center justify-center rounded-lg bg-white text-gray-400">
                                            <ui:icon name="phone" class="h-4 w-4" />
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs text-gray-400">{{ __('Phone') }}</p>
                                            <p class="text-sm font-medium text-gray-800" dir="ltr">
                                                {{ $order->client->phone }}
                                            </p>
                                        </div>
                                    </a>
                                @endif
                            </div>
                        @else
                            <div class="flex flex-col items-center rounded-xl bg-gray-50 py-8 text-center">
                                <div
                                    class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-gray-300">
                                    <ui:icon name="user" class="h-7 w-7" />
                                </div>
                                <p class="mt-3 text-sm font-semibold text-gray-700">{{ \App\Models\Order::walkingClientLabel() }}</p>
                                <p class="mt-1 text-xs text-gray-400">طلب بدون حساب عميل</p>
                            </div>
                        @endif
                    </div>
                </ui:box>

                {{-- ملخص مالي --}}
                <ui:box title="ملخص الطلب" class="">
                    <div class="p-4 sm:p-5">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المجموع الفرعي</span>
                                <span class="font-medium text-gray-800" dir="ltr">
                                    {{ $order->formatAmount($order->subtotal) }} {{ $order->currency_code }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">الخصومات</span>
                                <span class="font-medium {{ $order->discount_total > 0 ? 'text-red-600' : 'text-gray-800' }}"
                                    dir="ltr">
                                    @if ($order->discount_total > 0)
                                        −{{ $order->formatAmount($order->discount_total) }}
                                    @else
                                        {{ $order->formatAmount($order->discount_total) }}
                                    @endif
                                    {{ $order->currency_code }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">الضريبة</span>
                                <span class="font-medium text-gray-800" dir="ltr">
                                    {{ $order->formatAmount($order->tax_total) }} {{ $order->currency_code }}
                                </span>
                            </div>

                            <div class="my-1 border-t border-dashed border-gray-200"></div>

                            <div
                                class="flex items-center justify-between rounded-xl bg-gradient-to-l from-primary-50 to-slate-50 px-4 py-3.5">
                                <span class="text-sm font-semibold text-gray-800">الإجمالي النهائي</span>
                                <span class="text-lg font-bold text-primary-700" dir="ltr">
                                    {{ $order->formattedGrandTotal() }}
                                </span>
                            </div>

                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المدفوع</span>
                                <span class="font-medium text-emerald-700" dir="ltr">
                                    {{ $order->formatAmount($order->paid_total) }} {{ $order->currency_code }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المتبقي</span>
                                <span class="font-medium {{ $order->due_total > 0 ? 'text-amber-700' : 'text-gray-800' }}"
                                    dir="ltr">
                                    {{ $order->formatAmount($order->due_total) }} {{ $order->currency_code }}
                                </span>
                            </div>
                        </div>

                        <div class="mt-5 grid grid-cols-2 gap-2">
                            <div class="rounded-lg bg-gray-50 px-3 py-2.5 text-center">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">حالة الطلب</p>
                                <div class="mt-1">
                                    <ui:badge color="{{ $order->statusBadgeColor() }}" size="sm">
                                        {{ $order->statusLabel() }}
                                    </ui:badge>
                                </div>
                            </div>
                            <div class="rounded-lg bg-gray-50 px-3 py-2.5 text-center">
                                <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">الدفع</p>
                                <div class="mt-1">
                                    <ui:badge color="{{ $order->paymentStatusBadgeColor() }}" size="sm">
                                        {{ $order->paymentStatusLabel() }}
                                    </ui:badge>
                                </div>
                            </div>
                        </div>
                    </div>
                </ui:box>

            </div>
        </div>
    </div>

    <ui:modal title="تسجيل دفعة" size="lg" name="add-order-payment">
        <ui:form wire:submit="recordPayment" class="!p-5 !py-6">
            <div class="mb-4 rounded-xl bg-gray-50 px-4 py-3">
                <p class="text-xs text-gray-400">المبلغ المتبقي</p>
                <p class="mt-1 text-lg font-bold text-amber-700" dir="ltr">
                    {{ $order->formatAmount($order->due_total) }} {{ $order->currency_code }}
                </p>
            </div>

            <ui:input
                name="paymentAmount"
                label="المبلغ"
                type="number"
                step="0.01"
                min="0.01"
                :max="Order::fromMinor($order->due_total)"
                placeholder="0.00"
                dir="ltr"
                suffix="{{ $order->currency_code }}"
            />

            <ui:select
                name="paymentMethod"
                label="طريقة الدفع"
                :options="Order::paymentMethodOptions()"
            />

            <ui:textarea
                name="paymentNotes"
                label="ملاحظات"
                placeholder="ملاحظات اختيارية..."
                rows="3"
            />

            <x-slot:footer>
                <div class="flex items-center justify-end gap-2">
                    <ui:button
                        type="button"
                        label="إلغاء"
                        variant="ghost"
                        @click.prevent="$dispatch('closemodal', { modal: 'add-order-payment' })"
                    />
                    <ui:button target="recordPayment" label="تسجيل الدفعة" icon="check" />
                </div>
            </x-slot:footer>
        </ui:form>
    </ui:modal>

    <ui:modal title="تغيير حالة الطلب" size="lg" name="change-order-status">
        <ui:form wire:submit="updateStatus" class="!p-5 !py-6">
            <div class="mb-4 rounded-xl bg-gray-50 px-4 py-3">
                <p class="text-xs text-gray-400">الحالة الحالية</p>
                <div class="mt-1">
                    <ui:badge color="{{ $order->statusBadgeColor() }}" size="sm">
                        {{ $order->statusLabel() }}
                    </ui:badge>
                </div>
            </div>

            <ui:select
                name="newStatus"
                label="الحالة الجديدة"
                :options="Order::statusOptions()"
            />

            <ui:textarea
                name="statusReason"
                label="سبب التغيير"
                placeholder="اكتب سبب تغيير الحالة..."
                rows="4"
            />

            <x-slot:footer>
                <div class="flex items-center justify-end gap-2">
                    <ui:button
                        type="button"
                        label="إلغاء"
                        variant="ghost"
                        @click.prevent="$dispatch('closemodal', { modal: 'change-order-status' })"
                    />
                    <ui:button target="updateStatus" label="حفظ التغيير" icon="check" />
                </div>
            </x-slot:footer>
        </ui:form>
    </ui:modal>

</ui:container>

<?php

use App\Actions\RecordOrderPayment;
use App\Models\ActivityLog;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

new class extends \Livewire\Component {
    public Order $order;

    /** @var \Illuminate\Support\Collection<int, object> */
    public Collection $items;

    public string $newStatus = '';

    public string $statusReason = '';

    public string $paymentAmount = '';

    public string $paymentMethod = 'cash';

    public string $paymentNotes = '';

    public function mount(): void
    {
        $query = Order::query()->with('client');

        if ($tenantId = currentTenantId()) {
            $query->where('tenant_id', $tenantId);
        }

        $this->order = $query->whereUuid(request()->id)->firstOrFail();

        $this->items = DB::table('order_items')
            ->where('order_id', $this->order->id)
            ->orderBy('id')
            ->get();

        $this->newStatus = $this->order->statusValue();
    }

    #[Computed]
    public function orderPayments(): Collection
    {
        return $this->order->payments()
            ->with('invoice')
            ->latest('id')
            ->get();
    }

    #[Computed]
    public function activityTimeline(): Collection
    {
        $statusEntries = $this->order->statuses()
            ->latest('id')
            ->get()
            ->map(fn ($status): array => [
                'key' => 'status-'.$status->id,
                'type' => 'status',
                'title' => 'تغيير حالة الطلب',
                'status' => $status->name,
                'reason' => $status->reason,
                'details' => null,
                'causer' => null,
                'date' => $status->created_at,
            ]);

        $activityEntries = $this->order->activitiesAsSubject()
            ->with('causer')
            ->latest('id')
            ->get()
            ->map(function (ActivityLog $activity): array {
                return [
                    'key' => 'activity-'.$activity->id,
                    'type' => 'activity',
                    'title' => $this->activityTitle($activity),
                    'status' => null,
                    'reason' => null,
                    'details' => $this->activityDetails($activity),
                    'causer' => $activity->causer,
                    'date' => $activity->created_at,
                ];
            });

        return $statusEntries
            ->concat($activityEntries)
            ->sortByDesc(fn (array $entry) => $entry['date'])
            ->values();
    }

    public function openAddPaymentModal(): void
    {
        $this->paymentAmount = $this->order->due_total > 0
            ? (string) Order::fromMinor($this->order->due_total)
            : '';
        $this->paymentMethod = data_get($this->order->meta, 'payment_method', 'cash');
        $this->paymentNotes = '';
        $this->resetValidation();
        $this->dispatch('openmodal', modal: 'add-order-payment');
    }

    public function recordPayment(): void
    {
        $maxAmount = Order::fromMinor($this->order->due_total);

        $this->validate([
            'paymentAmount' => ['required', 'numeric', 'min:0.01', 'max:'.$maxAmount],
            'paymentMethod' => ['required', 'string', Rule::in(array_keys(Order::paymentMethodOptions()))],
            'paymentNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $amountMinor = Order::minorFromDecimal($this->paymentAmount);

        RecordOrderPayment::run(
            $this->order,
            $amountMinor,
            $this->paymentMethod,
            filled($this->paymentNotes) ? $this->paymentNotes : null,
        );

        $this->order->refresh();
        unset($this->orderPayments, $this->activityTimeline);
        $this->paymentNotes = '';
        $this->resetValidation();
        $this->dispatch('closemodal', modal: 'add-order-payment');
        $this->dispatch('notify', text: 'تم تسجيل الدفعة وإنشاء الفاتورة بنجاح.', type: 'success');
    }

    public function openChangeStatusModal(): void
    {
        $this->newStatus = $this->order->statusValue();
        $this->statusReason = '';
        $this->resetValidation();
        $this->dispatch('openmodal', modal: 'change-order-status');
    }

    public function updateStatus(): void
    {
        $this->validate([
            'newStatus' => ['required', 'string', Rule::in(array_keys(Order::statusOptions()))],
            'statusReason' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        if ($this->newStatus === $this->order->statusValue()) {
            $this->addError('newStatus', 'الحالة المختارة هي الحالة الحالية.');

            return;
        }

        DB::transaction(function (): void {
            $this->order->changeStatus($this->newStatus, $this->statusReason);
        });

        $this->order->refresh();
        unset($this->activityTimeline);
        $this->statusReason = '';
        $this->resetValidation();
        $this->dispatch('closemodal', modal: 'change-order-status');
        $this->dispatch('notify', text: 'تم تحديث حالة الطلب بنجاح.', type: 'success');
    }

    protected function activityTitle(ActivityLog $activity): string
    {
        return match ($activity->event) {
            'created' => 'إنشاء الطلب',
            'updated' => 'تحديث الطلب',
            'deleted' => 'حذف الطلب',
            'restored' => 'استعادة الطلب',
            default => $activity->description ?: 'نشاط على الطلب',
        };
    }

    protected function activityDetails(ActivityLog $activity): ?string
    {
        $changes = $activity->attribute_changes;

        if (! $changes instanceof Collection || $changes->isEmpty()) {
            return null;
        }

        $attributes = collect($changes->get('attributes', []))
            ->except(['updated_at'])
            ->map(function (mixed $value, string $key): string {
                if ($key === 'status') {
                    return 'الحالة: '.Order::statusLabelFor((string) $value);
                }

                return "{$key}: {$value}";
            })
            ->values();

        return $attributes->isEmpty() ? null : $attributes->implode(' · ');
    }

    public function rendering($view): void
    {
        $view->title('#'.($this->order->number ?? $this->order->id))->layout('admin::layout');
    }
}; ?>
