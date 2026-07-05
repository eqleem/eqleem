<ui:container title="{{ __('Orders') }} / #{{ $order->number ?? $order->id }}"
    backRoute="{{ route('admin.orders.home') }}">

    @php
        $issuedAt = $order->issued_at ?? $order->created_at;
        $itemsCount = $items->count();
        $totalQty = $items->sum('qty');
        $shippingFee = $order->shippingFee();
        $trackingNumber = data_get($order->meta, 'tracking_number');
        $shippingAddress = data_get($order->meta, 'shipping_address');
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- الشريط الجانبي --}}
        <div class="space-y-6 lg:order-1">

            {{-- ملخص الطلب --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="receipt" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">ملخص الطلب</h2>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">المجموع الفرعي</span>
                        <span class="font-medium text-gray-800" >
                            {{ $order->formatMoney($order->subtotal) }}
                        </span>
                    </div>
                    @if ($shippingFee > 0)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">الشحن</span>
                            <span class="font-medium text-gray-800" >
                                {{ $order->formatMoney($shippingFee) }}
                            </span>
                        </div>
                    @endif
                    @if ($order->discount_total > 0)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">الخصومات</span>
                            <span class="font-medium text-red-600" >
                                −{{ $order->formatMoney($order->discount_total) }}
                            </span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">الضريبة</span>
                        <span class="font-medium text-gray-800" >
                            {{ $order->formatMoney($order->tax_total) }}
                        </span>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800">الإجمالي</span>
                            <span class="text-xl font-bold text-primary-700" >
                                {{ $order->formattedGrandTotal() }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">المدفوع</span>
                            <span class="font-medium text-emerald-700"  >
                                {{ $order->formatMoney($order->paid_total) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">المتبقي</span>
                            <span class="font-medium {{ $order->due_total > 0 ? 'text-amber-700' : 'text-gray-800' }}">
                                {{ $order->formatMoney($order->due_total) }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- العميل --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="user" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">العميل</h2>
                </div>
                <div class="p-5">
                    @if ($order->client)
                        <div class="flex items-center gap-3">
                            <img src="{{ $order->client->avatar }}" alt="{{ $order->client->name }}"
                                class="h-12 w-12 shrink-0 rounded-full bg-gray-100 object-cover">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900">{{ $order->client->name }}</p>
                                @if (data_get($order->client, 'email'))
                                    <p class="truncate text-sm text-gray-500">{{ $order->client->email }}</p>
                                @endif
                                @if (data_get($order->client, 'phone'))
                                    <p class="text-sm text-gray-500" dir="ltr">{{ $order->client->phone }}</p>
                                @endif
                            </div>
                        </div>

                        <ui:button
                            href="{{ route('admin.clients.detail', ['id' => $order->client->uuid]) }}"
                            label="عرض ملف العميل"
                            variant="outline"
                            class="mt-4 w-full"
                            wire:navigate
                        />
                    @else
                        <div class="flex flex-col items-center py-4 text-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                <ui:icon name="user" class="h-6 w-6" />
                            </div>
                            <p class="mt-3 text-sm font-semibold text-gray-700">{{ \App\Models\Order::walkingClientLabel() }}</p>
                            <p class="mt-1 text-xs text-gray-400">طلب بدون حساب عميل</p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ملاحظات --}}
            @if ($order->notes)
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="note" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">ملاحظات</h2>
                    </div>
                    <div class="p-5">
                        <p class="rounded-lg bg-gray-50 p-4 text-sm leading-relaxed text-gray-700">{{ $order->notes }}</p>
                    </div>
                </section>
            @endif
        </div>

        {{-- المحتوى الرئيسي --}}
        <div class="space-y-6 lg:order-2 lg:col-span-2">

            {{-- تفاصيل الطلب --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ui:icon name="package" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">تفاصيل الطلب</h2>
                    </div>
                    <ui:button
                        wire:click="openChangeStatusModal"
                        label="تغيير الحالة"
                        icon="refresh"
                        variant="outline"
                        class="!h-8 !px-3 !text-xs"
                    />
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">رقم الطلب</dt>
                            <dd class="text-sm font-semibold text-gray-900">#{{ $order->number ?? $order->id }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">حالة الطلب</dt>
                            <dd>
                                <ui:badge color="{{ $order->statusBadgeColor() }}" size="sm">
                                    {{ $order->statusLabel() }}
                                </ui:badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">تاريخ الطلب</dt>
                            <dd class="text-sm text-gray-800">
                                {{ $issuedAt->translatedFormat('d M Y') }}
                                <span class="text-gray-400" dir="ltr">{{ $issuedAt->translatedFormat('h:i A') }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">حالة الدفع</dt>
                            <dd>
                                <ui:badge color="{{ $order->paymentStatusBadgeColor() }}" size="sm">
                                    {{ $order->paymentStatusLabel() }}
                                </ui:badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">مصدر الطلب</dt>
                            <dd class="flex items-center gap-1.5 text-sm text-gray-800">
                                <ui:icon name="building-store" class="h-4 w-4 text-gray-400" />
                                {{ $order->channelLabel() }}
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">طريقة الدفع</dt>
                            <dd class="flex items-center gap-1.5 text-sm text-gray-800">
                                <ui:icon name="credit-card" class="h-4 w-4 text-gray-400" />
                                {{ $order->paymentMethodLabel() }}
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">طريقة الشحن</dt>
                            <dd class="text-sm text-gray-800">{{ $order->shippingMethodLabel() }}</dd>
                        </div>
                        @if ($trackingNumber)
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">رقم التتبع</dt>
                                <dd class="text-sm font-medium text-primary-600" dir="ltr">{{ $trackingNumber }}</dd>
                            </div>
                        @endif
                        @if ($shippingAddress)
                            <div class="sm:col-span-2">
                                <dt class="mb-1 text-xs text-gray-400">عنوان الشحن</dt>
                                <dd class="flex items-start gap-1.5 text-sm text-gray-800">
                                    <ui:icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0 text-gray-400" />
                                    {{ $shippingAddress }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>

            {{-- العناصر --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ui:icon name="shopping-cart" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">
                            العناصر ({{ $itemsCount }})
                        </h2>
                    </div>
                    @if ($totalQty > $itemsCount)
                        <span class="text-xs text-gray-400">{{ $totalQty }} وحدة</span>
                    @endif
                </div>

                @if ($items->isEmpty())
                    <div class="p-5">
                        <ui:empty subtitle="لم يُضف أي منتج أو خدمة لهذا الطلب بعد.">
                            لا توجد عناصر في هذا الطلب.
                            <x-slot:icon>
                                <ui:icon name="shopping-cart-off" class="!h-12 !w-12 p-0.5 text-gray-400" />
                            </x-slot:icon>
                        </ui:empty>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-400">
                                    <th class="px-5 py-3 text-start font-medium">المنتج</th>
                                    <th class="px-3 py-3 text-start font-medium">السعر</th>
                                    <th class="px-3 py-3 text-center font-medium">الكمية</th>
                                    <th class="px-5 py-3 text-end font-medium">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($items as $item)
                                    <tr wire:key="item-{{ $item->id }}" class="group">
                                        <td class="px-5 py-4">
                                            <div class="flex items-start gap-3">
                                                @if ($item->image_url)
                                                    <img src="{{ $item->image_url }}" alt="{{ $item->name }}"
                                                        class="h-10 w-10 shrink-0 rounded-lg bg-gray-100 object-cover">
                                                @else
                                                    <div
                                                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-400">
                                                        <ui:icon name="{{ $item->type_icon }}" class="h-5 w-5" />
                                                    </div>
                                                @endif
                                                <div class="min-w-0 space-y-1">
                                                    <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                                    <div class="flex flex-wrap items-center gap-1.5">
                                                        <ui:badge color="{{ $item->type_badge_color }}" size="sm">
                                                            {{ $item->type_label }}
                                                        </ui:badge>
                                                        @if ($item->is_booking && $item->booking_status_label)
                                                            <ui:badge color="{{ $item->booking_status_color }}" size="sm">
                                                                {{ $item->booking_status_label }}
                                                            </ui:badge>
                                                        @endif
                                                    </div>

                                                    @if ($item->is_booking)
                                                        @if ($item->booking_date_label)
                                                            <p class="text-xs text-gray-500">
                                                                <span class="text-gray-400">تاريخ الموعد</span>
                                                                {{ $item->booking_date_label }}
                                                            </p>
                                                            @if ($item->booking_time_label)
                                                                <p class="text-xs text-gray-500">
                                                                    <span class="text-gray-400">وقت الموعد</span>
                                                                    <span dir="ltr">{{ $item->booking_time_label }}</span>
                                                                </p>
                                                            @endif
                                                            @if ($item->booking_duration_label)
                                                                <p class="text-xs text-gray-500">{{ $item->booking_duration_label }}</p>
                                                            @endif
                                                        @else
                                                            <p class="text-xs text-amber-600">لا توجد بيانات حجز</p>
                                                        @endif
                                                        @if ($item->calendar_name)
                                                            <p class="text-xs text-gray-400">{{ $item->calendar_name }}</p>
                                                        @endif
                                                    @else
                                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 text-xs text-gray-500">
                                                            @if (in_array($item->type, ['product', 'digital_product', 'menu'], true) && $item->sku)
                                                                <span dir="ltr">SKU: {{ $item->sku }}</span>
                                                            @endif
                                                            @if ($item->type === 'course')
                                                                <span>المقاعد: {{ $item->qty }}</span>
                                                            @elseif ($item->type !== 'course')
                                                                <span>الكمية: {{ $item->qty }}</span>
                                                            @endif
                                                            @if ($item->type === 'digital_product')
                                                                <span>تسليم رقمي</span>
                                                            @elseif ($item->type === 'digital_service')
                                                                <span>خدمة رقمية</span>
                                                            @elseif ($item->type === 'course')
                                                                <span>تسجيل في الدورة</span>
                                                            @endif
                                                            @if (filled($item->description))
                                                                <span>{{ $item->description }}</span>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if ($item->discount_total > 0)
                                                        <p class="text-xs text-red-500">
                                                            خصم <span>{{ $order->formatMoney($item->discount_total) }}</span>
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 whitespace-nowrap text-gray-600">
                                            <span class="sr-only">{{ $item->is_booking ? 'سعر الحجز' : 'سعر الوحدة' }}</span>
                                            {{ $order->formatMoney($item->unit_price) }}
                                        </td>
                                        <td class="px-3 py-4 text-center text-gray-800">
                                            {{ $item->qty }}
                                        </td>
                                        <td class="px-5 py-4 text-end font-semibold text-gray-900 whitespace-nowrap">
                                            {{ $order->formatMoney($item->line_total) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t border-gray-100 px-5 py-4">
                        <div class="ms-auto max-w-xs space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">المجموع الفرعي</span>
                                <span class="text-gray-800">
                                    {{ $order->formatMoney($order->subtotal) }}
                                </span>
                            </div>
                            @if ($shippingFee > 0)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">الشحن</span>
                                    <span class="text-gray-800" >
                                        {{ $order->formatMoney($shippingFee) }}
                                    </span>
                                </div>
                            @endif
                            @if ($order->discount_total > 0)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-500">الخصومات</span>
                                    <span class="text-red-600">
                                        −{{ $order->formatMoney($order->discount_total) }}
                                    </span>
                                </div>
                            @endif
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-500">الضريبة</span>
                                <span class="text-gray-800">
                                    {{ $order->formatMoney($order->tax_total) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                                <span class="font-semibold text-gray-800">الإجمالي</span>
                                <span class="text-lg font-bold text-primary-700">
                                    {{ $order->formattedGrandTotal() }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            {{-- المدفوعات --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ui:icon name="coin" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">المدفوعات</h2>
                    </div>
                    @if ($order->due_total > 0)
                        <ui:button
                            wire:click="openAddPaymentModal"
                            label="إضافة دفعة"
                            icon="plus"
                            variant="outline"
                            class="!h-8 !px-3 !text-xs"
                        />
                    @endif
                </div>

                <div class="p-5">
                    @if ($this->orderPayments->isEmpty())
                        <ui:empty subtitle="سجّل دفعة لإنشاء فاتورة وربطها بهذا الطلب.">
                            لا توجد مدفوعات بعد.
                            <x-slot:icon>
                                <ui:icon name="coin" class="!h-12 !w-12 p-0.5 text-gray-400" />
                            </x-slot:icon>
                        </ui:empty>
                    @else
                        <div class="divide-y divide-gray-50">
                            @foreach ($this->orderPayments as $payment)
                                <div wire:key="payment-{{ $payment->id }}"
                                    class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('admin.orders.payments.detail', ['uuid' => $payment->uuid]) }}"
                                                wire:navigate
                                                class="text-sm font-semibold text-gray-800 transition hover:text-primary-600">
                                                {{ Order::paymentMethodOptions()[$payment->source_type] ?? $payment->sourceTypeLabel() }}
                                            </a>
                                            <ui:badge color="{{ $payment->statusBadgeColor() }}" size="sm">
                                                {{ $payment->statusLabel() }}
                                            </ui:badge>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ $payment->created_at->translatedFormat('d M Y h:i A') }}
                                            @if ($payment->resolvedDescription())
                                                · {{ $payment->resolvedDescription() }}
                                            @endif
                                            @if ($payment->invoice)
                                                · {{ $payment->invoice->s_number }}
                                            @endif
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-base font-bold text-gray-900 sm:text-end">
                                        {{ $payment->formattedAmount() }} {{ $payment->currency }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            {{-- سجل النشاط --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="history" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">سجل النشاط</h2>
                    @if ($this->activityTimeline->isNotEmpty())
                        <span class="ms-auto text-xs text-gray-400">
                            {{ $this->activityTimeline->count() }} {{ $this->activityTimeline->count() === 1 ? 'حدث' : 'أحداث' }}
                        </span>
                    @endif
                </div>

                <div class="p-5">
                    @if ($this->activityTimeline->isEmpty())
                        <ui:empty subtitle="ستظهر تغييرات الحالة والنشاطات هنا.">
                            لا يوجد سجل نشاط بعد.
                            <x-slot:icon>
                                <ui:icon name="history" class="!h-12 !w-12 p-0.5 text-gray-400" />
                            </x-slot:icon>
                        </ui:empty>
                    @else
                        <div class="space-y-0">
                            @foreach ($this->activityTimeline as $entry)
                                <div wire:key="activity-{{ $entry['key'] }}"
                                    class="relative flex gap-4 pb-6 last:pb-0">
                                    @if (! $loop->last)
                                        <span class="absolute top-8 bottom-0 w-px bg-gray-200"
                                            style="inset-inline-start: 0.875rem;"></span>
                                    @endif

                                    <div
                                        class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $entry['type'] === 'status' ? 'bg-primary-50 text-primary-600' : 'bg-gray-100 text-gray-500' }}">
                                        <ui:icon name="{{ $entry['type'] === 'status' ? 'refresh' : 'history' }}"
                                            class="h-3.5 w-3.5" />
                                    </div>

                                    <div class="min-w-0 flex-1 pt-0.5">
                                        <div class="flex flex-wrap items-start justify-between gap-2">
                                            <div class="min-w-0">
                                                <p class="text-sm text-gray-800">{{ $entry['title'] }}</p>
                                                @if ($entry['type'] === 'status')
                                                    <ui:badge color="{{ Order::statusBadgeColorFor($entry['status']) }}"
                                                        size="sm" class="mt-1">
                                                        {{ Order::statusLabelFor($entry['status']) }}
                                                    </ui:badge>
                                                @endif
                                                @if (filled($entry['reason'] ?? null))
                                                    <p class="mt-1 text-sm text-gray-500">{{ $entry['reason'] }}</p>
                                                @endif
                                                @if (filled($entry['details'] ?? null))
                                                    <p class="mt-1 text-sm text-gray-400">{{ $entry['details'] }}</p>
                                                @endif
                                                @if ($entry['causer'])
                                                    <p class="mt-1 text-xs text-gray-400">{{ $entry['causer']->name }}</p>
                                                @endif
                                            </div>
                                            <p class="shrink-0 text-xs text-gray-400">
                                                {{ $entry['date']->translatedFormat('d M Y') }}
                                                <span dir="ltr">{{ $entry['date']->translatedFormat('h:i A') }}</span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        </div>
    </div>

    <ui:modal title="تسجيل دفعة" size="lg" name="add-order-payment">
        <ui:form wire:submit="recordPayment" class="!p-5 !py-6">
            <div class="mb-4 rounded-xl bg-gray-50 px-4 py-3">
                <p class="text-xs text-gray-400">المبلغ المتبقي</p>
                <p class="mt-1 text-lg font-bold text-amber-700">
                    {{ $order->formatMoney($order->due_total) }}
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
                suffix="{{ money_symbol($order->currency_code) }}"
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
use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Order;
use Carbon\Carbon;
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

        $this->items = $this->loadOrderItems($this->order->id);

        $this->newStatus = $this->order->statusValue();
    }

    /**
     * @return Collection<int, object>
     */
    protected function loadOrderItems(int $orderId): Collection
    {
        $items = DB::table('order_items')
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->get();

        $metas = $items->mapWithKeys(function (object $item): array {
            $meta = is_string($item->meta ?? null)
                ? (json_decode($item->meta, true) ?: [])
                : (array) ($item->meta ?? []);

            return [$item->id => $meta];
        });

        $bookingIds = $metas->pluck('booking_id')->filter()->unique()->values();

        $bookings = $bookingIds->isEmpty()
            ? collect()
            : Booking::query()
                ->with('calendar')
                ->whereIn('id', $bookingIds)
                ->get()
                ->keyBy('id');

        $calendarIds = $metas->pluck('calendar_id')
            ->filter()
            ->unique()
            ->diff($bookings->pluck('calendar_id')->filter())
            ->values();

        $calendars = $calendarIds->isEmpty()
            ? collect()
            : Calendar::query()
                ->whereIn('id', $calendarIds)
                ->get()
                ->keyBy('id');

        return $items->map(function (object $item) use ($metas, $bookings, $calendars): object {
            $meta = $metas->get($item->id, []);
            $type = (string) ($meta['type'] ?? 'other');
            $isBooking = Order::isBookingItemType($type);
            $booking = filled($meta['booking_id'] ?? null)
                ? $bookings->get($meta['booking_id'])
                : null;

            $startAt = $booking?->start_at
                ?? (filled($meta['booking_start_at'] ?? null) ? Carbon::parse($meta['booking_start_at']) : null);
            $endAt = $booking?->end_at
                ?? (filled($meta['booking_end_at'] ?? null) ? Carbon::parse($meta['booking_end_at']) : null);

            $calendarId = $booking?->calendar_id ?? ($meta['calendar_id'] ?? null);
            $calendarName = $booking?->calendar?->name
                ?? ($calendarId ? $calendars->get($calendarId)?->name : null);

            $bookingStatus = $booking?->status;

            $item->type = $type;
            $item->type_label = Order::itemTypeOptions()[$type] ?? $type;
            $item->type_icon = Order::itemTypeIcons()[$type] ?? 'package';
            $item->type_badge_color = $this->itemTypeBadgeColor($type);
            $item->is_booking = $isBooking;
            $item->description = filled($meta['description'] ?? null) ? (string) $meta['description'] : null;
            $item->image_url = $meta['image_url'] ?? null;
            $item->booking_date_label = $startAt?->translatedFormat('l j F Y');
            $item->booking_time_label = $this->bookingTimeLabel($startAt, $endAt);
            $item->booking_duration_label = $this->bookingDurationLabel($startAt, $endAt);
            $item->calendar_name = $calendarName;
            $item->booking_status = $bookingStatus;
            $item->booking_status_label = $bookingStatus
                ? (Booking::statuses()[$bookingStatus] ?? $bookingStatus)
                : null;
            $item->booking_status_color = $this->bookingStatusBadgeColor($bookingStatus);

            return $item;
        });
    }

    protected function itemTypeBadgeColor(string $type): string
    {
        return match ($type) {
            'product' => 'blue',
            'digital_product' => 'purple',
            'course' => 'purple',
            'digital_service' => 'blue',
            'menu' => 'yellow',
            'service' => 'green',
            'unit_rental' => 'blue',
            default => 'gray',
        };
    }

    protected function bookingStatusBadgeColor(?string $status): string
    {
        return match ($status) {
            'pending' => 'yellow',
            'confirmed' => 'green',
            'cancelled' => 'red',
            'completed' => 'blue',
            default => 'gray',
        };
    }

    protected function bookingTimeLabel(?Carbon $startAt, ?Carbon $endAt): ?string
    {
        if (! $startAt) {
            return null;
        }

        if ($endAt && ! $startAt->isSameDay($endAt)) {
            return $startAt->translatedFormat('d M Y h:i A').' — '.$endAt->translatedFormat('d M Y h:i A');
        }

        if ($endAt) {
            return $startAt->translatedFormat('h:i A').' — '.$endAt->translatedFormat('h:i A');
        }

        return $startAt->translatedFormat('h:i A');
    }

    protected function bookingDurationLabel(?Carbon $startAt, ?Carbon $endAt): ?string
    {
        if (! $startAt || ! $endAt || $endAt->lte($startAt)) {
            return null;
        }

        $minutes = (int) $startAt->diffInMinutes($endAt);

        if ($minutes < 60) {
            return $minutes.' دقيقة';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours >= 24 && $remainingMinutes === 0 && $hours % 24 === 0) {
            $days = intdiv($hours, 24);

            return $days === 1 ? 'يوم واحد' : $days.' أيام';
        }

        if ($remainingMinutes === 0) {
            return $hours === 1 ? 'ساعة واحدة' : $hours.' ساعات';
        }

        return $hours.' س '.$remainingMinutes.' د';
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
