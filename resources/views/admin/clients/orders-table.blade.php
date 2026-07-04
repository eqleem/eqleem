<div class="divide-y divide-gray-200 divide-dotted">

    <div class="bg-gray-100 p-3 flex items-center gap-x-7 w-full">
        <div class="flex-grow">
            <div class="relative text-sm text-gray-800 col-span-3">
                <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                    <ui:icon name="search" class="text-gray-400" />
                </div>

                <input wire:model.live="search" type="text" placeholder="ابحث .."
                    class="block w-full rounded-lg py-1.5 ps-10 text-gray-800 ring-0 ring-inset border-transparent border ring-gray-200 placeholder:text-gray-400 focus:border focus:outline-none focus:border-primary-500 sm:text-sm sm:leading-6">
            </div>
        </div>
    </div>

    <div class="relative last-child:rounded-b-2xl pb-4X p-1">

        @if ($results->count() == 0)
            <ui:empty subtitle="سيتم عرض طلبات هذا العميل هنا بعد إنشائها.">
                لا توجد طلبات.
                <x-slot:icon>
                    <ui:icon name="message-2" class="!w-12 !h-12 text-gray-400 p-0.5" />
                </x-slot:icon>
            </ui:empty>
        @else
            <div class="">
                @foreach ($results as $item)
                    <div wire:key="{{ $item->id }}"
                        class="flex items-center justify-between gap-x-4 w-full hover:bg-gray-50 last:rounded-b-2xl px-4 sm:px-6">
                        <div class="py-3 flex-1 min-w-0">
                            <a href="{{ route('admin.orders.detail', ['id' => $item->uuid]) }}" wire:navigate
                                class="block">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    <h2 class="text-lg text-gray-700 font-semibold">
                                        #{{ $item->number ?? $item->id }}
                                    </h2>
                                    <ui:badge color="{{ $item->statusBadgeColor() }}" size="sm">
                                        {{ $item->statusLabel() }}
                                    </ui:badge>
                                    <ui:badge color="{{ $item->paymentStatusBadgeColor() }}" size="sm">
                                        {{ $item->paymentStatusLabel() }}
                                    </ui:badge>
                                </div>
                            </a>
                        </div>

                        <div class="hidden sm:flex items-center gap-x-6 text-sm text-gray-600 shrink-0">
                            <div class="text-end">
                                <div class="font-bold text-gray-800">{{ $item->formattedGrandTotal() }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">{{ $item->items_count }} منتج</div>
                            </div>
                            <div class="text-end min-w-24">
                                <div>{{ $item->issued_at?->translatedFormat('d M Y') ?? $item->created_at->translatedFormat('d M Y') }}</div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $item->issued_at?->translatedFormat('h:i A') ?? $item->created_at->translatedFormat('h:i A') }}
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

use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;

new class extends Livewire\Component {
    use WithPagination;

    public Client $client;

    public $search;

    public function placeholder()
    {
        return loadingIcon();
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

        return $query->where('number', 'like', $search);
    }

    public function with(): array
    {
        $query = Order::query()
            ->where('client_id', $this->client->id)
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

        return [
            'results' => $query->paginate(20),
        ];
    }
}; ?>
