<ui:mainbox title="الفروع" subtitle="قائمة الفروع والمستودعات.">
    <x-slot:icon>
        <img src="{{ asset('assets/icons/business/010-location.svg') }}" alt="" class="h-6 w-6">
    </x-slot:icon>

    <x-slot:actions>
        <ui:button wire:click="openAddModal" label="أضف فرع" icon="square-rounded-plus" />
    </x-slot:actions>

    <div class="divide-y divide-gray-200 divide-dotted border-t border-dotted border-gray-200">
        <div class="bg-gray-100 p-3">
            <div class="relative text-sm text-gray-800">
                <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                    <ui:icon name="search" class="text-gray-400" />
                </div>

                <input
                    wire:model.live="search"
                    type="text"
                    placeholder="ابحث .."
                    class="block w-full rounded-lg py-1.5 ps-10 text-gray-800 ring-0 ring-inset border-transparent border ring-gray-200 placeholder:text-gray-400 focus:border focus:outline-none focus:border-primary-500 sm:text-sm sm:leading-6"
                >
            </div>
        </div>

        <div class="relative">
            @if ($branches->isEmpty())
                <ui:empty subtitle="سيتم عرض الفروع هنا بعد إضافتها.">
                    لا توجد فروع.
                    <x-slot:icon>
                        <ui:icon name="map-pin" class="w-12 h-12 opacity-50" />
                    </x-slot:icon>
                </ui:empty>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($branches as $branch)
                        <li wire:key="branch-{{ $branch->id }}">
                            <button
                                type="button"
                                wire:click="openEditModal({{ $branch->id }})"
                                class="group flex w-full items-center gap-3 px-4 py-3 text-start hover:bg-gray-50 transition"
                            >
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-50">
                                    <ui:icon name="map-pin" class="!w-5 !h-5 text-red-500" />
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $branch->display_name }}</p>
                                    @if (filled($branch->location_summary))
                                        <p class="mt-0.5 text-xs text-gray-500 truncate">{{ $branch->location_summary }}</p>
                                    @endif
                                </div>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div wire:loading wire:target="search, openEditModal, openAddModal"
                class="absolute inset-0 bg-white opacity-50"></div>

            <div wire:loading.flex wire:target="search, openEditModal, openAddModal"
                class="flex justify-center items-center absolute inset-0">
                <ui:icon name="loader-3" class="animate-spin text-gray-300 w-10 h-10" />
            </div>
        </div>
    </div>

    <ui:modal :title="$editingBranchId ? 'تعديل' : 'أضف فرع'" size="3xl" name="branch-form">
        <livewire:admin::settings.branches.branch-form
            :branch-id="$editingBranchId"
            :key="'branch-form-'.($editingBranchId ?? 'new')"
        />
    </ui:modal>
</ui:mainbox>

<?php

use App\Models\Branch;
use Livewire\Attributes\On;

new class extends \Livewire\Component
{
    public string $search = '';

    public ?int $editingBranchId = null;

    #[On('updateBranchList')]
    public function refreshList(): void
    {
        $this->editingBranchId = null;
    }

    public function openAddModal(): void
    {
        $this->editingBranchId = null;
        $this->dispatch('openmodal', modal: 'branch-form');
    }

    public function openEditModal(int $branchId): void
    {
        if (! Branch::query()->whereKey($branchId)->exists()) {
            return;
        }

        $this->editingBranchId = $branchId;
        $this->dispatch('openmodal', modal: 'branch-form');
    }

    public function with(): array
    {
        $branches = Branch::query()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($this->search !== '') {
            $term = mb_strtolower($this->search);

            $branches = $branches->filter(function (Branch $branch) use ($term): bool {
                return str_contains(mb_strtolower($branch->display_name), $term)
                    || str_contains(mb_strtolower((string) $branch->city), $term)
                    || str_contains(mb_strtolower($branch->country_label), $term)
                    || str_contains(mb_strtolower((string) $branch->address), $term);
            })->values();
        }

        return [
            'branches' => $branches,
        ];
    }
}; ?>
