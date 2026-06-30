<div class=" divide-y divide-gray-200 divide-dotted">

    <div class="bg-gray-100 p-3 flex items-center gap-x-7 w-full">
        <div class="ps-3">
            <div class="flex items-center">
                <ui:check-all />
            </div>
        </div>
        <div class="flex-grow">
            <div class="relative text-sm text-gray-800 col-span-3">
                <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                    <ui:icon name="search" class="  text-gray-400" />
                </div>

                <input wire:model.live="search" type="text" placeholder="ابحث .."
                    class="block w-full rounded-lg   py-1.5 ps-10 text-gray-800 ring-0 ring-inset border-transparent border ring-gray-200 placeholder:text-gray-400 focus:border focus:outline-none focus:border-primary-500 sm:text-sm sm:leading-6">
            </div>
        </div>
        <div x-show="$wire.selectedIds.length > 0" x-cloak class="flex items-center gap-x-2">
            <div class="flex items-center gap-1 text-sm text-gray-600">
                <span x-text="$wire.selectedIds.length"></span>

                <span>محددة</span>
            </div>

            <button wire:click="deleteSelected" wire:confirm="Are you sure you want to delete all selected items?"
                class="flex items-center gap-2 rounded-lg border px-3 py-1.5 bg-white text-sm text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-75">

                <ui:icon name="trash" wire:loading.remove class=" text-gray-300 w-4 h-4" />
                <ui:icon name="loader-3" wire:loading wire:target="deleteSelected"
                    class="animate-spin  text-gray-300 w-4 h-4" />
                {{ __('Delete selected') }}<span x-text="'('+$wire.selectedIds.length+')'"></span>
            </button>
        </div>
    </div>

    <div class="relative last-child:rounded-b-2xl pb-4X">

        @if ($results->count() == 0)
            <ui:empty subtitle="سيتم عرض العملاء هنا بعد إضافتهم أو شراء أحد المنتجات أو الخدمات.">
                {{ __('No clients.') }}
                <x-slot:icon>
                    <ui:icon name="users" class="!w-12 !h-12 text-gray-400 p-0.5" />
                </x-slot:icon>
            </ui:empty>
        @else
            <div class="pb-4">
                @foreach ($results as $item)
                    <div wire:key="{{ $item->id }}"
                        class="flex items-center justify-between gap-x-7 w-full hover:bg-gray-50 last:rounded-b-2xl">
                        <div class="ps-6">
                            <div class="flex items-center">
                                <input wire:model="selectedIds" value="{{ $item->id }}" type="checkbox"
                                    class="rounded-xl border-gray-300 shadow-sm w-4 h-4">
                            </div>
                        </div>
                        <div class="py-3 w-full">
                            <a href="{{ route('admin.clients.detail', ['id' => $item->id]) }}" wire:navigate
                                class="flex items-center gap-x-2">

                                <img class="h-10 w-10 flex-none rounded-full bg-gray-50" src="{{ $item->avatar }}"
                                    alt="">
                                <div>
                                    <h2 class="text-lg text-gray-700 dark:text-white ">
                                        {{ $item->name }}</h2>
                                    <div class="flex items-center gap-x-2 mt-1">

                                        <div class="text-base leading-6 text-gray-900 flex items-center gap-x-2">
                                            @if ($item->active)
                                                <div class="mt-1 flex items-center gap-x-1.5">
                                                    <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                                                        <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                        <p class="mt-1 flex text-xs items-center gap-1  text-gray-500">
                                            @if ($item->email)
                                                <span
                                                    class="truncate inline-flex items-center gap-x-1 bg-gray-100 p-1 px-2 rounded-md text-xs">{{ $item->email }}
                                                </span>
                                            @endif
                                            @if ($item->phone)
                                                <span class="inline-block" dir="ltr">
                                                    {{ $item->phone }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </div>

                        <div class="pe-6">

                            <div x-data="{ dropdownMenu: false }">
                                <div class="relative" @click.outside="dropdownMenu=false" x-cloak>
                                    <button @click="dropdownMenu = ! dropdownMenu" type="button"
                                        class="hover:bg-gray-200 p-1 rounded-lg inline-block" id="user-menu-button"
                                        aria-expanded="false" aria-haspopup="true">
                                        <ui:icon name="dots" class="  text-gray-400" />
                                    </button>

                                    <div x-show="dropdownMenu"
                                        class="absolute z-50 mt-2 bg-white border shadow-sm rounded-lg text-gray-800 text-sm flex p-1 ltr:right-0 rtl:left-0 w-48 flex-col gap-y-px"
                                        role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button"
                                        tabindex="-1" x-transition.scale.origin.top>


                                        <a href="{{ route('admin.clients.detail', ['id' => $item->id]) }}"
                                            wire:navigate
                                            class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2">
                                            {{ __('View') }}
                                        </a>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Table loading spinners... --}}
        <div wire:loading wire:target="sortBy, search, nextPage, previousPage, archive, archiveSelected"
            class="absolute inset-0 bg-white opacity-50">
            {{--  --}}
        </div>

        <div wire:loading.flex wire:target="sortBy, search, nextPage, previousPage, archive, archiveSelected"
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

use App\Models\Client;
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

    #[On('updateClientList')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    protected function applySearch($query)
    {
        return $this->search === ''
            ? $query
            : $query
                ->where('name', 'like', '%' . $this->search . '%')
                ->orWhere('email', 'like', '%' . $this->search . '%')
                ->orWhere('phone', 'like', '%' . $this->search . '%');
    }

    function delete($id)
    {
        $entry = Client::whereId($id)->first();
        $entry?->delete();

        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    public function deleteSelected()
    {
        $items = Client::whereIn('id', $this->selectedIds)->get();

        foreach ($items as $item) {
            $this->delete($item->id);
        }

        $this->selectedIds = [];
        $this->dispatch('notify', text: __('Selected items deleted successfully.'));
    }

    public function with(): array
    {
        $query = Client::orderBy('id', 'desc');

        $query = $this->applySearch($query);

        $results = $query->paginate(20);

        $this->allIdsOnPage = $results->map(fn($item) => (string) $item->id)->toArray();

        return [
            'results' => $results,
        ];
    }
}; ?>
