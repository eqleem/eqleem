<div class="divide-y divide-gray-200 divide-dotted">

    <div class="bg-gray-100 p-3 flex items-center gap-x-7 w-full">
        <div class="flex-grow">
            <div class="relative text-sm text-gray-800 col-span-3">
                <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                    <ui:icon name="search" class="text-gray-400" />
                </div>

                <input wire:model.live="search" type="text" placeholder="ابحث برقم الفاتورة أو الطلب .."
                    class="block w-full rounded-lg py-1.5 ps-10 text-gray-800 ring-0 ring-inset border-transparent border ring-gray-200 placeholder:text-gray-400 focus:border focus:outline-none focus:border-primary-500 sm:text-sm sm:leading-6">
            </div>
        </div>
    </div>

    <div class="relative last-child:rounded-b-2xl pb-4X p-1">

        @if ($results->count() === 0)
            <ui:empty subtitle="ستظهر فواتير المتجر هنا بعد تسجيل أي دفعة أو إصدار فاتورة.">
                لا توجد فواتير.
                <x-slot:icon>
                    <ui:icon name="file-invoice" class="!w-12 !h-12 text-gray-400 p-0.5" />
                </x-slot:icon>
            </ui:empty>
        @else
            <div class="">
                @foreach ($results as $item)
                    <div wire:key="invoice-{{ $item->id }}"
                        class="flex items-center justify-between gap-x-4 w-full hover:bg-gray-50 last:rounded-b-2xl px-4 sm:px-6">
                        <div class="py-3 flex-1 min-w-0">
                            <a href="{{ route('admin.orders.invoices.detail', ['uuid' => $item->uuid]) }}" wire:navigate
                                class="block">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <h2 class="text-lg text-gray-700 font-semibold" dir="ltr">
                                        {{ $item->s_number }}
                                    </h2>
                                    <ui:badge color="{{ $item->statusBadgeColor() }}" size="sm">
                                        {{ $item->statusLabel() }}
                                    </ui:badge>
                                    <ui:badge color="gray" size="sm">
                                        {{ $item->typeLabel() }}
                                    </ui:badge>
                                </div>
                                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-500">
                                    @if ($label = $item->invoicableLabel())
                                        <span class="inline-flex items-center gap-x-1 bg-gray-100 p-1 px-2 rounded-md text-xs">
                                            {{ $label }}
                                        </span>
                                    @endif
                                    @if ($item->user?->name)
                                        <span class="truncate">{{ $item->user->name }}</span>
                                    @endif
                                    @if ($item->issued_on)
                                        <span class="text-xs text-gray-400">
                                            {{ $item->issued_on->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            </a>
                        </div>

                        <div class="hidden sm:flex items-center gap-x-6 text-sm text-gray-600 shrink-0">
                            <div class="text-end">
                                <div class="font-bold text-gray-800" dir="ltr">
                                    {{ money_format($item->total_after_vat, currency: $item->currency) }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    مدفوع {{ money_format($item->amount_paid, currency: $item->currency) }}
                                </div>
                            </div>
                            <div class="text-end min-w-24">
                                <div>{{ $item->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $item->created_at->translatedFormat('h:i A') }}
                                </div>
                            </div>
                        </div>

                        <div class="pe-2 shrink-0">
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
                                        <a href="{{ route('admin.orders.invoices.detail', ['uuid' => $item->uuid]) }}"
                                            wire:navigate
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
            <ui:icon name="loader-3" class="animate-spin text-gray-300 w-10 h-10" />
        </div>
    </div>

    @if ($results->total() > $results->perPage())
        <div class="p-4 px-6 bg-gray-50 rounded-b-2xl flex justify-between items-center">
            <div class="text-gray-500 text-sm">
                النتائج : <b>{{ \Illuminate\Support\Number::format($results->total()) }}</b>
            </div>
            {{ $results->links('ui::components.pagination') }}
        </div>
    @endif
</div>

<?php

use App\Models\Invoice;
use App\Models\Order;
use Livewire\WithPagination;

new class extends Livewire\Component {
    use WithPagination;

    public $search;

    public function placeholder()
    {
        return loadingIcon();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    protected function baseQuery()
    {
        $query = Invoice::query()
            ->with(['user', 'invoicable'])
            ->orderByDesc('id');

        if ($tenantId = currentTenantId()) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    protected function applySearch($query)
    {
        if ($this->search === '' || $this->search === null) {
            return $query;
        }

        $search = '%'.$this->search.'%';

        return $query->where(function ($query) use ($search): void {
            $query->where('number', 'like', $search)
                ->orWhere('note', 'like', $search)
                ->orWhereHas('user', function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search);
                })
                ->orWhere(function ($query) use ($search): void {
                    $query->where('invoicable_type', Order::class)
                        ->whereHasMorph('invoicable', [Order::class], function ($query) use ($search): void {
                            $query->where('number', 'like', $search)
                                ->orWhere('id', 'like', $search);
                        });
                });
        });
    }

    public function with(): array
    {
        $query = $this->applySearch($this->baseQuery());

        return [
            'results' => $query->paginate(20),
        ];
    }
}; ?>
