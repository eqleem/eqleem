<ui:container title="{{ __('Orders') }} / {{ $invoice->s_number }}"
    backRoute="{{ route('admin.orders.home', ['tab' => 'invoices']) }}">

    @php
        $order = $invoice->invoicable_type === \App\Models\Order::class ? $invoice->invoicable : null;
        $issuedAt = $invoice->issued_on ?? $invoice->created_at;
        $vatAmount = $invoice->total_after_vat - $invoice->total_before_vat;
        $dueAmount = $invoice->dueAmount();
    @endphp

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

        {{-- الشريط الجانبي --}}
        <div class="space-y-6 lg:order-1">

            {{-- ملخص الفاتورة --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="file-invoice" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">ملخص الفاتورة</h2>
                </div>
                <div class="space-y-3 p-5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">قبل الضريبة</span>
                        <span class="font-medium text-gray-800">
                            {{ money_format($invoice->total_before_vat, currency: $invoice->currency) }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500">الضريبة</span>
                        <span class="font-medium text-gray-800">
                            {{ money_format($vatAmount, currency: $invoice->currency) }}
                        </span>
                    </div>

                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-800">الإجمالي</span>
                            <span class="text-xl font-bold text-primary-700">
                                {{ money_format($invoice->total_after_vat, currency: $invoice->currency) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2 border-t border-gray-100 pt-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">المدفوع</span>
                            <span class="font-medium text-emerald-700">
                                {{ money_format($invoice->amount_paid, currency: $invoice->currency) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">المتبقي</span>
                            <span class="font-medium {{ $dueAmount > 0 ? 'text-amber-700' : 'text-gray-800' }}">
                                {{ money_format($dueAmount, currency: $invoice->currency) }}
                            </span>
                        </div>
                    </div>
                </div>
            </section>

            {{-- العميل --}}
            @if ($order?->client)
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="user" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">العميل</h2>
                    </div>
                    <div class="p-5">
                        <div class="flex items-center gap-3">
                            <img src="{{ $order->client->avatar }}" alt="{{ $order->client->name }}"
                                class="h-12 w-12 shrink-0 rounded-full bg-gray-100 object-cover">
                            <div class="min-w-0">
                                <p class="truncate font-semibold text-gray-900">{{ $order->client->name }}</p>
                                @if ($order->client->email)
                                    <p class="truncate text-sm text-gray-500">{{ $order->client->email }}</p>
                                @endif
                                @if ($order->client->phone)
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
                    </div>
                </section>
            @endif

            {{-- ملاحظات --}}
            @if ($invoice->note)
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="note" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">ملاحظات</h2>
                    </div>
                    <div class="p-5">
                        <p class="rounded-lg bg-gray-50 p-4 text-sm leading-relaxed text-gray-700">{{ $invoice->note }}</p>
                    </div>
                </section>
            @endif
        </div>

        {{-- المحتوى الرئيسي --}}
        <div class="space-y-6 lg:order-2 lg:col-span-2">

            {{-- تفاصيل الفاتورة --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <ui:icon name="file-invoice" class="h-5 w-5 text-primary-600" />
                    <h2 class="text-sm font-semibold text-gray-700">تفاصيل الفاتورة</h2>
                </div>
                <div class="p-5">
                    <dl class="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2">
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">رقم الفاتورة</dt>
                            <dd class="text-sm font-semibold text-gray-900" dir="ltr">{{ $invoice->s_number }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">الحالة</dt>
                            <dd>
                                <ui:badge color="{{ $invoice->statusBadgeColor() }}" size="sm">
                                    {{ $invoice->statusLabel() }}
                                </ui:badge>
                            </dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">النوع</dt>
                            <dd class="text-sm text-gray-800">{{ $invoice->typeLabel() }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">العملة</dt>
                            <dd class="text-sm text-gray-800" dir="ltr">{{ $invoice->currency }}</dd>
                        </div>
                        <div>
                            <dt class="mb-1 text-xs text-gray-400">تاريخ الإصدار</dt>
                            <dd class="text-sm text-gray-800">
                                {{ $issuedAt->translatedFormat('d M Y') }}
                                <span class="text-gray-400" dir="ltr">{{ $issuedAt->translatedFormat('h:i A') }}</span>
                            </dd>
                        </div>
                        @if ($invoice->paid_on)
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">تاريخ الدفع</dt>
                                <dd class="text-sm text-emerald-700">
                                    {{ $invoice->paid_on->translatedFormat('d M Y') }}
                                    <span class="text-emerald-500" dir="ltr">{{ $invoice->paid_on->translatedFormat('h:i A') }}</span>
                                </dd>
                            </div>
                        @endif
                        @if ($invoice->user)
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">أُنشئت بواسطة</dt>
                                <dd class="text-sm text-gray-800">{{ $invoice->user->name }}</dd>
                            </div>
                        @endif
                        @if ($order)
                            <div>
                                <dt class="mb-1 text-xs text-gray-400">الطلب المرتبط</dt>
                                <dd>
                                    <a href="{{ route('admin.orders.detail', ['id' => $order->uuid]) }}" wire:navigate
                                        class="text-sm font-medium text-primary-600 hover:text-primary-700">
                                        {{ $invoice->invoicableLabel() }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>

            {{-- بنود الفاتورة --}}
            <section class="overflow-hidden rounded-xl bg-white">
                <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50 px-5 py-4">
                    <div class="flex items-center gap-2">
                        <ui:icon name="list-details" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">
                            البنود ({{ $invoice->items->count() }})
                        </h2>
                    </div>
                </div>

                @if ($invoice->items->isEmpty())
                    <div class="p-5">
                        <ui:empty subtitle="لا توجد بنود مسجّلة على هذه الفاتورة.">
                            لا توجد بنود.
                            <x-slot:icon>
                                <ui:icon name="list-details" class="!h-12 !w-12 p-0.5 text-gray-400" />
                            </x-slot:icon>
                        </ui:empty>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-400">
                                    <th class="px-5 py-3 text-start font-medium">البند</th>
                                    <th class="px-3 py-3 text-center font-medium">الكمية</th>
                                    <th class="px-3 py-3 text-end font-medium">السعر</th>
                                    <th class="px-5 py-3 text-end font-medium">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach ($invoice->items as $item)
                                    <tr wire:key="invoice-item-{{ $item->id }}" class="group">
                                        <td class="px-5 py-4">
                                            <div class="min-w-0 space-y-1">
                                                <p class="font-medium text-gray-900">{{ $item->name }}</p>
                                                @if ($item->type && $item->type !== 'item')
                                                    <p class="text-xs text-gray-400">{{ $item->type }}</p>
                                                @endif
                                                @if ($item->note)
                                                    <p class="text-xs text-gray-500">{{ $item->note }}</p>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-3 py-4 text-center text-gray-800" dir="ltr">
                                            {{ $item->quantity }}
                                        </td>
                                        <td class="px-3 py-4 text-end whitespace-nowrap text-gray-600">
                                            {{ money_format($item->amount_after_vat, currency: $item->currency) }}
                                        </td>
                                        <td class="px-5 py-4 text-end font-semibold whitespace-nowrap text-gray-900">
                                            {{ money_format($item->total_after_vat, currency: $item->currency) }}
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
                                    {{ money_format($invoice->subtotal_after_vat, currency: $invoice->currency) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between border-t border-gray-100 pt-2">
                                <span class="font-semibold text-gray-800">الإجمالي شامل الضريبة</span>
                                <span class="text-lg font-bold text-primary-700">
                                    {{ money_format($invoice->total_after_vat, currency: $invoice->currency) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            {{-- المدفوعات --}}
            @if ($invoice->payments->isNotEmpty())
                <section class="overflow-hidden rounded-xl bg-white">
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-gray-50 px-5 py-4">
                        <ui:icon name="coin" class="h-5 w-5 text-primary-600" />
                        <h2 class="text-sm font-semibold text-gray-700">المدفوعات</h2>
                    </div>

                    <div class="p-5">
                        <div class="divide-y divide-gray-50">
                            @foreach ($invoice->payments as $payment)
                                <div wire:key="invoice-payment-{{ $payment->id }}"
                                    class="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between">
                                    <div class="min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('admin.orders.payments.detail', ['uuid' => $payment->uuid]) }}"
                                                wire:navigate
                                                class="text-sm font-semibold text-gray-800 transition hover:text-primary-600">
                                                دفعة #{{ $payment->id }}
                                            </a>
                                            <ui:badge color="{{ $payment->statusBadgeColor() }}" size="sm">
                                                {{ $payment->statusLabel() }}
                                            </ui:badge>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-400">
                                            {{ $payment->created_at->translatedFormat('d M Y h:i A') }}
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-base font-bold text-gray-900 sm:text-end">
                                        {{ money_format($payment->amount, currency: $payment->currency) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>
    </div>

</ui:container>

<?php

use App\Models\Invoice;

new class extends \Livewire\Component {
    public Invoice $invoice;

    public function mount(string $uuid): void
    {
        $query = Invoice::query()
            ->with(['user', 'items', 'payments', 'invoicable']);

        if ($tenantId = currentTenantId()) {
            $query->where('tenant_id', $tenantId);
        }

        $this->invoice = $query->where('uuid', $uuid)->firstOrFail();
    }

    public function rendering($view): void
    {
        $view->title(__('Orders').' / '.$this->invoice->s_number)->layout('admin::layout');
    }
}; ?>
