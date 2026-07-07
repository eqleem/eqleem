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
        <div class=" ">
            <ui:button @click.prevent="$dispatch('openmodal', { modal: 'add-page' })" label="صفحة جديدة"
                icon="square-rounded-plus" />
        </div>

        <ui:modal title="إضافة صفحة جديدة" size="2xl" name="add-page">
            <livewire:admin::page.content.pages.add-page :contentType="$contentType" />
        </ui:modal>
    </div>

    <div class="relative last-child:rounded-b-2xl  p-1">
        @if ($results->count() === 0)
            <ui:empty subtitle="سيتم عرض الصفحات هنا بعد إضافتها.">
                لا توجد صفحات.
                <x-slot:icon>
                    <img src="{{ asset($contentType['icon']) }}" class="w-12 h-12 opacity-50" alt="">
                </x-slot:icon>
            </ui:empty>
        @else
            <div class="pb-4x">
                @foreach ($results as $item)
                    <div wire:key="page-{{ $item->uuid }}"
                        class="flex items-center justify-between gap-x-7 w-full hover:bg-gray-50 last:rounded-b-2xl">
                        <div class="ps-6">
                            @unless ($item->isSystemPage())
                                <div class="flex items-center">
                                    <input wire:model="selectedIds" value="{{ $item->id }}" type="checkbox"
                                        class="rounded-xl border-gray-300 shadow-sm w-4 h-4">
                                </div>
                            @endunless
                        </div>
                        <div class="py-3 w-full">
                            <button
                                type="button"
                                wire:click="openItem(@js($item->uuid))"
                                class="flex items-center gap-x-3 w-full text-start"
                            >
                                @php($imageUrl = contentImageUrl(data_get($item->data, 'image')) ?? $item->avatar)
                                <img
                                    class="h-12 w-12 flex-none rounded-xl object-cover bg-gray-100"
                                    src="{{ $imageUrl }}"
                                    alt="{{ $item->title }}"
                                >
                                <div>
                                    <h2 class="text-sm font-semibold truncate text-gray-700">{{ $item->title }}</h2>
                                    <div class="flex items-center gap-x-2 mt-1">
                                        @if ($item->active)
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
                                            @if ($item->isSystemPage())
                                                <span
                                                    class="truncate inline-flex items-center gap-x-1 bg-blue-50 text-blue-700 p-1 px-2 rounded-md text-xs">
                                                    {{ Content::pageTemplateOptions()[$item->template] ?? $item->template }}
                                                </span>
                                            @endif
                                            @if ($item->published_at)
                                                <span class="inline-block" dir="ltr">
                                                    {{ $item->published_at->translatedFormat('d M Y') }}
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </button>
                        </div>

                        <div class="pe-6">
                            <ui:table-menu>
<button
                                            type="button"
                                            wire:click="openItem(@js($item->uuid))"
                                            x-on:click="dropdownMenu = false"
                                            class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2 w-full text-start"
                                        >
                                            <ui:icon name="pencil" wire:loading.remove class="!w-4 !h-4 text-gray-400" />
                                            {{ __('Edit') }}
                                        </button>
                                        <button
                                            type="button"
                                            wire:click="toggleActive({{ $item->id }})"
                                            x-on:click="dropdownMenu = false"
                                            wire:loading.attr="disabled"
                                            wire:target="toggleActive({{ $item->id }})"
                                            class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2 w-full text-start"
                                        >
                                            <ui:icon name="{{ $item->active ? 'eye-off' : 'eye' }}" wire:loading.remove wire:target="toggleActive({{ $item->id }})" class="!w-4 !h-4 text-gray-400" />
                                            <ui:icon name="loader-3" wire:loading wire:target="toggleActive({{ $item->id }})" class="!w-4 !h-4 animate-spin text-gray-400" />
                                            {{ $item->active ? 'تعطيل' : 'تفعيل' }}
                                        </button>
                                        @unless ($item->isSystemPage())
                                            <button
                                                type="button"
                                                wire:click="delete({{ $item->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذه الصفحة؟"
                                                x-on:click="dropdownMenu = false"
                                                wire:loading.attr="disabled"
                                                wire:target="delete({{ $item->id }})"
                                                class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2 w-full text-start text-red-600"
                                            >
                                                <ui:icon name="trash" wire:loading.remove wire:target="delete({{ $item->id }})" class="!w-4 !h-4" />
                                                <ui:icon name="loader-3" wire:loading wire:target="delete({{ $item->id }})" class="!w-4 !h-4 animate-spin text-gray-400" />
                                                {{ __('Delete') }}
                                            </button>
                                        @endunless
</ui:table-menu>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div wire:loading wire:target="search, nextPage, previousPage, deleteSelected, delete, toggleActive"
            class="absolute inset-0 bg-white opacity-50"></div>

        <div wire:loading.flex wire:target="search, nextPage, previousPage, deleteSelected, delete, toggleActive"
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

    #[On('updatePagesList')]
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
        $content = Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->whereKey($id)
            ->first();

        if (! $content || $content->isSystemPage()) {
            return;
        }

        $content->delete();

        $this->selectedIds = array_values(array_diff($this->selectedIds, [(string) $id]));
        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    public function deleteSelected(): void
    {
        Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->whereIn('id', $this->selectedIds)
            ->get()
            ->reject(fn (Content $item): bool => $item->isSystemPage())
            ->each(fn (Content $item) => $item->delete());

        $this->selectedIds = [];
        $this->dispatch('notify', text: __('Selected items deleted successfully.'));
    }

    public function toggleActive(int $id): void
    {
        $content = Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->whereKey($id)
            ->first();

        if (! $content) {
            return;
        }

        $content->update(['active' => ! $content->active]);

        $this->dispatch(
            'notify',
            text: $content->active ? 'تم تفعيل الصفحة.' : 'تم تعطيل الصفحة.',
        );
    }

    public function openItem(?string $uuid): void
    {
        if (! filled($uuid)) {
            $this->dispatch('notify', text: 'تعذر فتح هذا العنصر. حاول تحديث الصفحة.');

            return;
        }

        $this->dispatch(
            'openContentItem',
            tab: $this->contentType['tab_id'],
            item: $uuid,
        );
    }

    public function with(): array
    {
        $query = Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->orderByRaw("CASE template WHEN 'contact' THEN 1 WHEN 'faq' THEN 2 ELSE 99 END")
            ->orderByDesc('id');

        $query = $this->applySearch($query);

        $results = $query->paginate(20);

        $this->allIdsOnPage = $results
            ->reject(fn (Content $item): bool => $item->isSystemPage())
            ->map(fn (Content $item): string => (string) $item->id)
            ->values()
            ->all();

        return [
            'results' => $results,
        ];
    }
}; ?>
