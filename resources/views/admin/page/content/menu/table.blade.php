<div class="divide-y divide-gray-200 divide-dotted">
    <div class="bg-gray-100 p-3 flex items-center gap-x-4 w-full">
        <div class="ps-3" x-cloak>
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
        <div x-show="$wire.selectedIds.length > 0" x-cloak class="flex items-center gap-x-2">
            <div class="flex items-center gap-1 text-sm text-gray-600">
                <span x-text="$wire.selectedIds.length"></span>
                <span>محددة</span>
            </div>

            <button wire:click="deleteSelected" wire:confirm="هل أنت متأكد من حذف العناصر المحددة؟"
                class="flex items-center gap-2 rounded-lg border px-3 py-1.5 bg-white text-sm text-gray-700 hover:bg-gray-200 disabled:cursor-not-allowed disabled:opacity-75">
                <ui:icon name="trash" wire:loading.remove class="text-gray-300 w-4 h-4" />
                <ui:icon name="loader-3" wire:loading wire:target="deleteSelected"
                    class="animate-spin text-gray-300 w-4 h-4" />
                {{ __('Delete selected') }}<span x-text="'('+$wire.selectedIds.length+')'"></span>
            </button>
        </div>
        <div>
            <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-menu-item' })" label="طبق جديد"
                icon="square-rounded-plus" />
        </div>

        <ui:modal title="إضافة طبق جديد" size="2xl" name="add-menu-item">
            <livewire:admin::page.content.menu.add-item :contentType="$contentType" />
        </ui:modal>
    </div>

    <div class="relative last-child:rounded-b-2xl pb-4X p-1">
        @if ($results->count() === 0)
            <ui:empty subtitle="سيتم عرض الأطباق هنا بعد إضافتها.">
                لا توجد أطباق.
                <x-slot:icon>
                    <img src="{{ asset($contentType['icon']) }}" class="w-12 h-12 opacity-50" alt="">
                </x-slot:icon>
            </ui:empty>
        @else
            <div class="">
                @foreach ($results as $item)
                    <div wire:key="menu-item-{{ $item->uuid }}"
                        class="flex items-center justify-between gap-x-7 w-full hover:bg-gray-50 last:rounded-b-2xl">
                        <div class="ps-6">
                            <div class="flex items-center">
                                <input wire:model="selectedIds" value="{{ $item->id }}" type="checkbox"
                                    class="rounded-xl border-gray-300 shadow-sm w-4 h-4">
                            </div>
                        </div>
                        <div class="py-3 w-full">
                            <a href="{{ route('admin.page.home', ['tab' => $contentType['tab_id'], 'item' => $item->uuid]) }}"
                                wire:navigate class="flex items-center gap-x-3">
                                @php($imageUrl = $item->getFirstMediaUrl('menu-media') ?: $item->avatar)
                                <img
                                    class="h-12 w-12 flex-none rounded-xl object-cover bg-gray-100"
                                    src="{{ $imageUrl }}"
                                    alt="{{ $item->title }}"
                                >
                                <div>
                                    <h2 class="text-sm font-semibold truncate text-gray-700">{{ $item->title }}</h2>
                                    <div class="flex items-center gap-x-2 mt-1">
                                        @if ($item->status === 'published')
                                            <div class="mt-1 flex items-center gap-x-1.5">
                                                <div class="flex-none rounded-full bg-emerald-500/20 p-1">
                                                    <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                                                </div>
                                            </div>
                                        @endif
                                        <p class="mt-1 flex text-xs items-center gap-1 text-gray-500">
                                            <span
                                                class="truncate inline-flex items-center gap-x-1 bg-gray-100 p-1 px-2 rounded-md text-xs">
                                                {{ $item->status_label }}
                                            </span>
                                            @if (data_get($item->data, 'price'))
                                                <span class="inline-block font-medium text-gray-700">
                                                    {{ money_format(data_get($item->data, 'price')) }}
                                                </span>
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
                                        class="hover:bg-gray-200 p-1 rounded-lg inline-block">
                                        <ui:icon name="dots" class="text-gray-400" />
                                    </button>

                                    <div x-show="dropdownMenu"
                                        class="absolute z-50 mt-2 bg-white border shadow-sm rounded-lg text-gray-800 text-sm flex p-1 ltr:right-0 rtl:left-0 w-48 flex-col gap-y-px"
                                        x-transition.scale.origin.top>
                                        <a href="{{ route('admin.page.home', ['tab' => $contentType['tab_id'], 'item' => $item->uuid]) }}"
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

        <div wire:loading wire:target="search, nextPage, previousPage, deleteSelected"
            class="absolute inset-0 bg-white opacity-50"></div>

        <div wire:loading.flex wire:target="search, nextPage, previousPage, deleteSelected"
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

use App\Models\Content;
use Livewire\Attributes\On;
use Livewire\WithPagination;

new class extends Livewire\Component
{
    use WithPagination;

    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $search = '';

    /** @var array<int, string> */
    public array $selectedIds = [];

    /** @var array<int, string> */
    public array $allIdsOnPage = [];

    public function placeholder(): string
    {
        return loadingIcon();
    }

    #[On('updateMenuItemList')]
    public function refreshList(): void
    {
        $this->resetPage();
    }

    protected function applySearch($query)
    {
        return $this->search === ''
            ? $query
            : $query->where('title', 'like', '%'.$this->search.'%');
    }

    public function delete(int $id): void
    {
        Content::query()->type(contentTypeModel($this->contentType['slug']))->whereKey($id)->first()?->delete();

        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    public function deleteSelected(): void
    {
        Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->whereIn('id', $this->selectedIds)
            ->get()
            ->each(fn (Content $item) => $item->delete());

        $this->selectedIds = [];
        $this->dispatch('notify', text: __('Selected items deleted successfully.'));
    }

    public function with(): array
    {
        $query = Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->orderByDesc('id');

        $query = $this->applySearch($query);

        $results = $query->paginate(20);

        $this->allIdsOnPage = $results->map(fn (Content $item): string => (string) $item->id)->toArray();

        return [
            'results' => $results,
        ];
    }
}; ?>
