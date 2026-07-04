<div class="divide-y divide-gray-200 divide-dotted">

    <div class="bg-gray-100 p-3 flex items-center gap-x-4 w-full">
        <div class="ps-1" x-cloak>
            <div class="flex items-center">
                <ui:check-all />
            </div>
        </div>
        <div class="flex-grow">
            <div class="relative text-sm text-gray-800 col-span-3">
                <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                    <ui:icon name="search" class="text-gray-400" />
                </div>

                <input wire:model.live="search" type="text" placeholder="ابحث .."
                    class="block w-full rounded-lg py-1.5 ps-10 text-gray-800 ring-0 ring-inset border-transparent border ring-gray-200 placeholder:text-gray-400 focus:border focus:outline-none focus:border-primary-500 sm:text-sm sm:leading-6">
            </div>
        </div>
        <div>
            <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-order' })" label="إضافة طلب"
                icon="square-rounded-plus" />
        </div>

        <ui:modal title="إضافة طلب جديد" size="4xl" name="add-order" :escape="false">
            <livewire:admin::orders.add-order />
        </ui:modal>
    </div>

    <div class="relative last-child:rounded-b-2xl pb-4X p-1">

        @if ($results->count() == 0)
            <ui:empty subtitle="سيتم عرض الطلبات هنا بعد إنشائها أو استلامها من المتجر.">
                لا توجد طلبات.
                <x-slot:icon>
                    <ui:icon name="message-2" class="!w-12 !h-12 text-gray-400 p-0.5" />
                </x-slot:icon>
            </ui:empty>
        @else
            <div class="">
                @foreach ($results as $item)
                    @php
                        $issuedAt = $item->issued_at ?? $item->created_at;
                        $paymentBadgeColor = match ($item->payment_status) {
                            'paid' => 'gray',
                            'unpaid' => 'yellow',
                            'partial' => 'yellow',
                            default => 'gray',
                        };
                        $paymentBadgeLabel = match ($item->payment_status) {
                            'paid' => 'حالة الدفع: مدفوع',
                            'unpaid' => 'حالة الدفع: لم تتم',
                            'partial' => 'حالة الدفع: مدفوع جزئياً',
                            'refunded' => 'حالة الدفع: مسترجع',
                            default => 'حالة الدفع: '.$item->paymentStatusLabel(),
                        };
                    @endphp
                    <div wire:key="{{ $item->id }}"
                        class="flex items-center gap-x-4 w-full hover:bg-gray-50 last:rounded-b-2xl px-4 sm:px-6 py-3">
                        <div class="shrink-0">
                            <input wire:model="selectedIds" value="{{ $item->id }}" type="checkbox"
                                class="rounded-xl border-gray-300 shadow-sm w-4 h-4">
                        </div>

                        <a href="{{ route('admin.orders.detail', ['id' => $item->uuid]) }}" wire:navigate
                            class="flex min-w-0 flex-1 items-start gap-x-3">
                            <div
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-green-500">
                                <ui:icon name="message-2" class="h-5 w-5" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <h2 class="text-sm font-bold text-gray-800">
                                    #{{ $item->number ?? $item->id }}
                                </h2>

                                <div class="mt-1.5 flex flex-wrap items-center gap-x-1.5">
                                    {{-- <ui:badge color="green" size="sm" class="inline-flex items-center gap-1">
                                        <ui:icon name="folder" class="!h-3.5 !w-3.5" />
                                        الطلبات
                                    </ui:badge> --}}

                                    <ui:badge color="gray" size="sm">
                                        {{ $issuedAt->locale(app()->getLocale())->diffForHumans() }}
                                    </ui:badge>

                                    <ui:badge color="{{ $item->statusBadgeColor() }}" size="sm">
                                        {{ $item->statusLabel() }}
                                    </ui:badge>

                                    <ui:badge color="{{ $paymentBadgeColor }}" size="sm">
                                        {{ $paymentBadgeLabel }}
                                    </ui:badge>

                                    <ui:badge color="gray" size="sm" dir="ltr">
                                        {{ money_format($item->grand_total, currency: $item->currency_code) }}
                                    </ui:badge>

                                    <ui:badge color="blue" size="sm">
                                        {{ $item->client?->name ?? \App\Models\Order::walkingClientLabel() }}
                                    </ui:badge>
                                </div>
                            </div>
                        </a>

                        <div class="shrink-0 pe-1">
                            <div x-data="{ dropdownMenu: false }">
                                <div class="relative" @click.outside="dropdownMenu=false" x-cloak>
                                    <button @click="dropdownMenu = ! dropdownMenu" type="button"
                                        class="hover:bg-gray-200 p-1 rounded-lg inline-block" aria-expanded="false"
                                        aria-haspopup="true">
                                        <ui:icon name="dots" class="text-gray-400" />
                                    </button>

                                    <div x-show="dropdownMenu"
                                        class="absolute z-50 mt-2 bg-white border shadow-sm rounded-lg text-gray-800 text-sm flex p-1 ltr:right-0 rtl:left-0 w-48 flex-col gap-y-px"
                                        role="menu" aria-orientation="vertical" tabindex="-1"
                                        x-transition.scale.origin.top>
                                        <a href="{{ route('admin.orders.detail', ['id' => $item->uuid]) }}"
                                            wire:navigate @click="dropdownMenu = false"
                                            class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2">
                                            {{ __('Edit') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div wire:loading wire:target="search, nextPage, previousPage"
            class="absolute inset-0 bg-white opacity-50">
        </div>

        <div wire:loading.flex wire:target="search, nextPage, previousPage"
            class="flex justify-center items-center absolute inset-0">
            <ui:icon name="loader-3" class="animate-spin  text-gray-300 w-10 h-10" />
        </div>
    </div>

    @if ($results->total() > $results->perPage())
        <div class=" p-4 px-6 bg-gray-50 rounded-b-2xl flex justify-between items-center">
            <div class="text-gray-500 text-sm">
                النتائج : <b>{{ \Illuminate\Support\Number::format($results->total()) }}</b>
            </div>
            {{ $results->links('ui::components.pagination') }}
        </div>
    @endif
</div>

<?php

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\WithPagination;

new class extends Livewire\Component {
    use WithPagination;

    public $search;

    public $selectedIds = [];

    public $allIdsOnPage = [];

    public function placeholder()
    {
        return loadingIcon();
    }

    #[On('updateOrderList')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    protected function applySearch($query)
    {
        if ($this->search === '' || $this->search === null) {
            return $query;
        }

        $search = '%'.$this->search.'%';

        return $query->where(function ($query) use ($search) {
            $query->where('number', 'like', $search)
                ->orWhereHas('client', function ($query) use ($search) {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                });
        });
    }

    public function with(): array
    {
        $query = Order::query()
            ->with('client')
            ->leftJoinSub(
                DB::table('order_items')
                    ->select('order_id', DB::raw('COUNT(*) as items_count'))
                    ->groupBy('order_id'),
                'order_items_count',
                'orders.id',
                '=',
                'order_items_count.order_id'
            )
            ->select('orders.*', DB::raw('COALESCE(order_items_count.items_count, 0) as items_count'))
            ->orderBy('orders.id', 'desc');

        if ($tenantId = currentTenantId()) {
            $query->where('orders.tenant_id', $tenantId);
        }

        $query = $this->applySearch($query);

        $results = $query->paginate(20);

        $this->allIdsOnPage = $results->map(fn ($item) => (string) $item->id)->toArray();

        return [
            'results' => $results,
        ];
    }
}; ?>
