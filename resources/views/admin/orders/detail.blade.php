<ui:container title="{{ __('Orders') }} / #{{ $order->number ?? $order->id }}"
    backRoute="{{ route('admin.orders.home') }}">

    <div class="space-y-6">
        <ui:box title="معلومات الطلب">
            <div class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2 p-5 xl:p-6">
                <div class="sm:col-span-1">
                    <dt class="text-sm text-gray-400">رقم الطلب</dt>
                    <dd class="mt-2 text-base font-bold text-gray-700">#{{ $order->number ?? $order->id }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm text-gray-400">حالة الطلب</dt>
                    <dd class="mt-2">
                        <ui:badge color="{{ $order->statusBadgeColor() }}">{{ $order->statusLabel() }}</ui:badge>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm text-gray-400">{{ __('Payment status') }}</dt>
                    <dd class="mt-2">
                        <ui:badge color="{{ $order->paymentStatusBadgeColor() }}">{{ $order->paymentStatusLabel() }}</ui:badge>
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm text-gray-400">تاريخ الطلب</dt>
                    <dd class="mt-2 text-base font-bold text-gray-700">
                        {{ ($order->issued_at ?? $order->created_at)->translatedFormat('d M Y - h:i A') }}
                    </dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm text-gray-400">طريقة الدفع</dt>
                    <dd class="mt-2 text-base font-bold text-gray-700">{{ $order->paymentMethodLabel() }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm text-gray-400">العملة</dt>
                    <dd class="mt-2 text-base font-bold text-gray-700">{{ $order->currency_code }}</dd>
                </div>
                <div class="sm:col-span-1">
                    <dt class="text-sm text-gray-400">الإجمالي</dt>
                    <dd class="mt-2 text-base font-bold text-gray-700">{{ $order->formattedGrandTotal() }}</dd>
                </div>
            </div>
        </ui:box>

        <ui:box title="معلومات العميل">
            <div class="grid grid-cols-1 gap-x-4 gap-y-8 sm:grid-cols-2 p-5 xl:p-6">
                @if ($order->client)
                    <div class="sm:col-span-1">
                        <dt class="text-sm text-gray-400">الاسم</dt>
                        <dd class="mt-2 text-base font-bold text-gray-700">
                            <a href="{{ route('admin.clients.detail', ['id' => $order->client->uuid]) }}"
                                wire:navigate class="text-primary-600 hover:text-primary-700">
                                {{ $order->client->name }}
                            </a>
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm text-gray-400">{{ __('Email') }}</dt>
                        <dd class="mt-2 text-base font-bold text-gray-700">
                            {{ data_get($order->client, 'email', '-') }}
                        </dd>
                    </div>
                    <div class="sm:col-span-1">
                        <dt class="text-sm text-gray-400">{{ __('Phone') }}</dt>
                        <dd class="mt-2 text-base font-bold text-gray-700 inline-block" dir="ltr">
                            {{ data_get($order->client, 'phone', '-') }}
                        </dd>
                    </div>
                @else
                    <div class="sm:col-span-2">
                        <p class="text-base text-gray-500">{{ __('Guest') }}</p>
                    </div>
                @endif
            </div>
        </ui:box>

        <ui:box title="عناصر الطلب">
            <div class="p-5 xl:p-6">
                @if ($items->isEmpty())
                    <p class="text-sm text-gray-500">لا توجد عناصر في هذا الطلب.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr class="text-sm text-gray-400">
                                    <th class="py-3 text-start font-normal">المنتج</th>
                                    <th class="py-3 text-center font-normal">الكمية</th>
                                    <th class="py-3 text-end font-normal">سعر الوحدة</th>
                                    <th class="py-3 text-end font-normal">الخصم</th>
                                    <th class="py-3 text-end font-normal">الإجمالي</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($items as $item)
                                    <tr wire:key="item-{{ $item->id }}" class="text-sm text-gray-700">
                                        <td class="py-3 font-semibold">{{ $item->name }}</td>
                                        <td class="py-3 text-center">{{ $item->qty }}</td>
                                        <td class="py-3 text-end" dir="ltr">
                                            {{ $order->formatAmount($item->unit_price) }} {{ $order->currency_code }}
                                        </td>
                                        <td class="py-3 text-end" dir="ltr">
                                            @if ($item->discount_total > 0)
                                                {{ $order->formatAmount($item->discount_total) }} {{ $order->currency_code }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-3 text-end font-bold" dir="ltr">
                                            {{ $order->formatAmount($item->line_total) }} {{ $order->currency_code }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </ui:box>

        <ui:box title="ملخص الطلب">
            <div class="p-5 xl:p-6 space-y-3 max-w-md ms-auto">
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>المجموع الفرعي</span>
                    <span class="font-semibold text-gray-800" dir="ltr">
                        {{ $order->formatAmount($order->subtotal) }} {{ $order->currency_code }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>الخصومات</span>
                    <span class="font-semibold text-gray-800" dir="ltr">
                        {{ $order->formatAmount($order->discount_total) }} {{ $order->currency_code }}
                    </span>
                </div>
                <div class="flex items-center justify-between text-sm text-gray-600">
                    <span>الضريبة</span>
                    <span class="font-semibold text-gray-800" dir="ltr">
                        {{ $order->formatAmount($order->tax_total) }} {{ $order->currency_code }}
                    </span>
                </div>
                <div class="border-t border-gray-100 pt-3 flex items-center justify-between text-base font-bold text-gray-800">
                    <span>الإجمالي النهائي</span>
                    <span dir="ltr">{{ $order->formattedGrandTotal() }}</span>
                </div>
            </div>
        </ui:box>
    </div>

</ui:container>

<?php

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

new class extends \Livewire\Component {
    public Order $order;

    /** @var \Illuminate\Support\Collection<int, object> */
    public Collection $items;

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
    }

    public function rendering($view): void
    {
        $view->title('#'.($this->order->number ?? $this->order->id))->layout('admin::layout');
    }
}; ?>
