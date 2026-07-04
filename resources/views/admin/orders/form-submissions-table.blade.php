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

        @if ($results->count() === 0)
            <ui:empty subtitle="سيتم عرض ردود النماذج هنا بعد إرسالها من الموقع.">
                لا توجد ردود.
                <x-slot:icon>
                    <ui:icon name="clipboard-list" class="!w-12 !h-12 text-gray-400 p-0.5" />
                </x-slot:icon>
            </ui:empty>
        @else
            <div class="pb-4">
                @foreach ($results as $item)
                    @php
                        $isUnread = $item->isUnread();
                    @endphp
                    <div wire:key="form-submission-{{ $item->id }}"
                        class="flex items-center justify-between gap-x-4 w-full px-4 sm:px-6 transition-colors {{ $isUnread ? 'bg-primary-50/60 hover:bg-primary-50 border-s-4 border-primary-500' : 'hover:bg-gray-50 border-s-4 border-transparent' }}">
                        <div class="py-3.5 flex-1 min-w-0">
                            <a href="{{ route('admin.orders.form-submissions.detail', ['id' => $item->id]) }}" wire:navigate
                                class="block">
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                    @if ($isUnread)
                                        <span class="h-2 w-2 shrink-0 rounded-full bg-primary-500" aria-hidden="true"></span>
                                    @endif
                                    <h2 class="text-lg font-semibold {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }}">
                                        #{{ $item->id }}
                                    </h2>
                                    @if ($isUnread)
                                        <ui:badge color="blue" size="sm">غير مقروء</ui:badge>
                                    @else
                                        <ui:badge color="{{ $item->statusBadgeColor() }}" size="sm">
                                            {{ $item->statusLabel() }}
                                        </ui:badge>
                                    @endif
                                    @if ($item->form)
                                        <span
                                            class="truncate inline-flex items-center gap-x-1 bg-white/80 p-1 px-2 rounded-md text-xs text-gray-600 ring-1 ring-gray-200/80">
                                            <ui:icon name="clipboard-list" class="!w-3.5 !h-3.5 text-gray-400" />
                                            {{ $item->form->title }}
                                        </span>
                                    @endif
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm {{ $isUnread ? 'text-gray-700' : 'text-gray-500' }}">
                                    @if ($item->client)
                                        <span class="truncate font-medium">{{ $item->client->name }}</span>
                                        @if ($item->client->email)
                                            <span
                                                class="truncate inline-flex items-center gap-x-1 bg-white/80 p-1 px-2 rounded-md text-xs ring-1 ring-gray-200/80">{{ $item->client->email }}</span>
                                        @endif
                                        @if ($item->client->phone)
                                            <span class="inline-block text-xs" dir="ltr">{{ $item->client->phone }}</span>
                                        @endif
                                    @elseif (filled($item->previewText()))
                                        <span class="truncate">{{ $item->previewText() }}</span>
                                    @else
                                        <span class="text-gray-400">{{ __('Guest') }}</span>
                                    @endif
                                </div>
                            </a>
                        </div>

                        <div class="hidden sm:flex items-center gap-x-6 text-sm shrink-0 {{ $isUnread ? 'text-gray-700' : 'text-gray-600' }}">
                            <div class="text-end min-w-24">
                                <div class="{{ $isUnread ? 'font-medium' : '' }}">
                                    {{ $item->submitted_at?->translatedFormat('d M Y') ?? $item->created_at->translatedFormat('d M Y') }}
                                </div>
                                <div class="text-xs text-gray-400 mt-0.5">
                                    {{ $item->submitted_at?->translatedFormat('h:i A') ?? $item->created_at->translatedFormat('h:i A') }}
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
                                        <a href="{{ route('admin.orders.form-submissions.detail', ['id' => $item->id]) }}"
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

use App\Models\FormSubmission;
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

    protected function applySearch($query)
    {
        if ($this->search === '' || $this->search === null) {
            return $query;
        }

        $search = '%'.$this->search.'%';

        return $query->where(function ($query) use ($search): void {
            $query->where('id', 'like', $search)
                ->orWhereHas('form', function ($query) use ($search): void {
                    $query->where('title', 'like', $search);
                })
                ->orWhereHas('client', function ($query) use ($search): void {
                    $query->where('name', 'like', $search)
                        ->orWhere('email', 'like', $search)
                        ->orWhere('phone', 'like', $search);
                })
                ->orWhereRaw('data::text ilike ?', [$search]);
        });
    }

    public function with(): array
    {
        $query = FormSubmission::query()
            ->with(['form', 'client'])
            ->orderByRaw('read_at IS NULL DESC')
            ->orderByDesc('submitted_at')
            ->orderByDesc('id');

        if ($tenantId = currentTenantId()) {
            $query->where('tenant_id', $tenantId);
        }

        $query = $this->applySearch($query);

        return [
            'results' => $query->paginate(20),
        ];
    }
}; ?>
