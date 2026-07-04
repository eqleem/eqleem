<ui:container title="{{ __('Orders') }} / {{ $invoice->s_number }}"
    backRoute="{{ route('admin.orders.home', ['tab' => 'invoices']) }}">

    @php
        $order = $invoice->invoicable_type === \App\Models\Order::class ? $invoice->invoicable : null;
        $issuedAt = $invoice->issued_on ?? $invoice->created_at;
    @endphp

    <div class="space-y-6">

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
                                <ui:icon name="file-invoice" class="h-3.5 w-3.5" />
                                فاتورة
                            </span>
                            <ui:badge color="{{ $invoice->statusBadgeColor() }}" size="sm">
                                {{ $invoice->statusLabel() }}
                            </ui:badge>
                            <ui:badge color="gray" size="sm">{{ $invoice->typeLabel() }}</ui:badge>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">رقم الفاتورة</p>
                            <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl" dir="ltr">
                                {{ $invoice->s_number }}
                            </h1>
                            @if ($invoice->note)
                                <p class="mt-2 text-sm text-gray-600">{{ $invoice->note }}</p>
                            @endif
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
                            @if ($invoice->paid_on)
                                <span class="inline-flex items-center gap-1.5 text-emerald-700">
                                    <ui:icon name="checks" class="h-4 w-4" />
                                    دُفعت {{ $invoice->paid_on->translatedFormat('d M Y') }}
                                </span>
                            @endif
                            @if ($order)
                                <a href="{{ route('admin.orders.detail', ['id' => $order->uuid]) }}" wire:navigate
                                    class="inline-flex items-center gap-1.5 font-medium text-primary-600 hover:text-primary-700">
                                    <ui:icon name="external-link" class="h-4 w-4" />
                                    {{ $invoice->invoicableLabel() }}
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="shrink-0 rounded-2xl bg-white p-5 text-center sm:min-w-56 sm:text-end lg:text-end">
                        <p class="text-xs font-medium uppercase tracking-wider text-gray-400">إجمالي الفاتورة</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-primary-700" dir="ltr">
                            {{ money_format($invoice->total_after_vat, currency: $invoice->currency) }}
                        </p>
                        @if ($invoice->dueAmount() > 0)
                            <p class="mt-2 text-sm text-amber-600">
                                متبقي {{ money_format($invoice->dueAmount(), currency: $invoice->currency) }}
                            </p>
                        @else
                            <p class="mt-2 text-sm text-emerald-600">مدفوعة بالكامل</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                        <ui:icon name="coin" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">المبلغ المدفوع</p>
                        <p class="text-lg font-bold text-gray-800" dir="ltr">
                            {{ money_format($invoice->amount_paid, currency: $invoice->currency) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <ui:icon name="receipt" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">قبل الضريبة</p>
                        <p class="text-lg font-bold text-gray-800" dir="ltr">
                            {{ money_format($invoice->total_before_vat, currency: $invoice->currency) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                        <ui:icon name="percentage" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">الضريبة</p>
                        <p class="text-lg font-bold text-gray-800" dir="ltr">
                            {{ money_format($invoice->total_after_vat - $invoice->total_before_vat, currency: $invoice->currency) }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="rounded-xl bg-white p-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <ui:icon name="list-details" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400">البنود</p>
                        <p class="text-lg font-bold text-gray-800">{{ $invoice->items->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <div class="space-y-6 lg:col-span-2">

                <ui:box title="بنود الفاتورة" icon="list-details">
                    @if ($invoice->items->isEmpty())
                        <div class="p-4 sm:p-5">
                            <ui:empty subtitle="لا توجد بنود مسجّلة على هذه الفاتورة.">
                                لا توجد بنود.
                                <x-slot:icon>
                                    <ui:icon name="list-details" class="!h-12 !w-12 p-0.5 text-gray-400" />
                                </x-slot:icon>
                            </ui:empty>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-100 text-sm">
                                <thead class="bg-gray-50/80">
                                    <tr>
                                        <th class="px-4 py-3 text-start font-medium text-gray-500 sm:px-5">البند</th>
                                        <th class="px-4 py-3 text-center font-medium text-gray-500">الكمية</th>
                                        <th class="px-4 py-3 text-end font-medium text-gray-500">السعر</th>
                                        <th class="px-4 py-3 text-end font-medium text-gray-500 sm:px-5">الإجمالي</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach ($invoice->items as $item)
                                        <tr wire:key="invoice-item-{{ $item->id }}" class="hover:bg-gray-50/60">
                                            <td class="px-4 py-4 sm:px-5">
                                                <div class="font-medium text-gray-800">{{ $item->name }}</div>
                                                @if ($item->type && $item->type !== 'item')
                                                    <div class="mt-0.5 text-xs text-gray-400">{{ $item->type }}</div>
                                                @endif
                                                @if ($item->note)
                                                    <div class="mt-1 text-xs text-gray-500">{{ $item->note }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 text-center text-gray-700" dir="ltr">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-4 py-4 text-end text-gray-700" dir="ltr">
                                                {{ money_format($item->amount_after_vat, currency: $item->currency) }}
                                            </td>
                                            <td class="px-4 py-4 text-end font-semibold text-gray-800 sm:px-5" dir="ltr">
                                                {{ money_format($item->total_after_vat, currency: $item->currency) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-gray-50/80">
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-end text-gray-500 sm:px-5">المجموع الفرعي</td>
                                        <td class="px-4 py-3 text-end font-medium text-gray-800 sm:px-5" dir="ltr">
                                            {{ money_format($invoice->subtotal_after_vat, currency: $invoice->currency) }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="px-4 py-3 text-end text-gray-500 sm:px-5">الإجمالي شامل الضريبة</td>
                                        <td class="px-4 py-3 text-end text-lg font-bold text-primary-700 sm:px-5" dir="ltr">
                                            {{ money_format($invoice->total_after_vat, currency: $invoice->currency) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </ui:box>

                @if ($invoice->payments->isNotEmpty())
                    <ui:box title="عمليات الدفع المرتبطة" icon="coin">
                        <div class="divide-y divide-gray-100">
                            @foreach ($invoice->payments as $payment)
                                <div wire:key="invoice-payment-{{ $payment->id }}"
                                    class="flex flex-col gap-3 p-4 sm:flex-row sm:items-center sm:justify-between sm:p-5">
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
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500">
                                            {{ $payment->created_at->translatedFormat('d M Y h:i A') }}
                                        </p>
                                    </div>
                                    <p class="shrink-0 text-lg font-bold text-emerald-700" dir="ltr">
                                        {{ money_format($payment->amount, currency: $payment->currency) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </ui:box>
                @endif
            </div>

            <div class="space-y-6">

                <ui:box title="ملخص الفاتورة" icon="file-invoice">
                    <div class="p-4 sm:p-5">
                        <dl class="divide-y divide-gray-100 text-sm">
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-gray-500">الحالة</dt>
                                <dd>
                                    <ui:badge color="{{ $invoice->statusBadgeColor() }}" size="sm">
                                        {{ $invoice->statusLabel() }}
                                    </ui:badge>
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-gray-500">النوع</dt>
                                <dd class="font-medium text-gray-800">{{ $invoice->typeLabel() }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-gray-500">العملة</dt>
                                <dd class="font-medium text-gray-800" dir="ltr">{{ $invoice->currency }}</dd>
                            </div>
                            <div class="flex items-center justify-between gap-4 py-3">
                                <dt class="text-gray-500">تاريخ الإصدار</dt>
                                <dd class="font-medium text-gray-800">{{ $issuedAt->translatedFormat('d M Y h:i A') }}</dd>
                            </div>
                            @if ($invoice->user)
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <dt class="text-gray-500">أُنشئت بواسطة</dt>
                                    <dd class="font-medium text-gray-800">{{ $invoice->user->name }}</dd>
                                </div>
                            @endif
                            @if ($order)
                                <div class="flex items-start justify-between gap-4 py-3">
                                    <dt class="shrink-0 text-gray-500">الطلب</dt>
                                    <dd class="text-end">
                                        <a href="{{ route('admin.orders.detail', ['id' => $order->uuid]) }}" wire:navigate
                                            class="font-medium text-primary-600 hover:text-primary-700">
                                            {{ $invoice->invoicableLabel() }}
                                        </a>
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </ui:box>

                @if ($order?->client)
                    <ui:box title="العميل" icon="user">
                        <div class="p-4 sm:p-5">
                            <div class="space-y-2 text-sm">
                                <p class="font-semibold text-gray-800">{{ $order->client->name }}</p>
                                @if ($order->client->email)
                                    <p class="text-gray-500">{{ $order->client->email }}</p>
                                @endif
                                @if ($order->client->phone)
                                    <p class="text-gray-500" dir="ltr">{{ $order->client->phone }}</p>
                                @endif
                            </div>
                        </div>
                    </ui:box>
                @endif
            </div>
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
