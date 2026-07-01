<div class="divide-y divide-gray-200 divide-dotted">
    <div class="bg-gray-100 p-3 flex items-center gap-x-4 w-full">
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
            <ui:button wire:click="openAddCategoryModal" label="تصنيف جديد"
                icon="square-rounded-plus" />
        </div>

        <ui:modal :title="$addingParentId ? 'إضافة قسم فرعي' : 'إضافة تصنيف'" size="lg" name="add-blog-category">
            <livewire:admin::page.content.blog.category-form
                :contentType="$contentType"
                :default-parent-id="$addingParentId"
                :key="'add-blog-category-'.($addingParentId ?? 'root')"
            />
        </ui:modal>
    </div>

    <div class="relative last-child:rounded-b-2xl pb-4X p-1">
        @if ($categories->isEmpty())
            <ui:empty subtitle="سيتم عرض تصنيفات المدونة هنا بعد إضافتها.">
                لا توجد تصنيفات.
                <x-slot:icon>
                    <ui:icon name="category" class="w-12 h-12 opacity-50" />
                </x-slot:icon>
            </ui:empty>
        @else
            <ul
                @if ($search === '')
                    wire:sortable="updateCategoryOrder"
                    wire:sortable.options="{ animation: 150 }"
                @endif
                class="pb-4"
            >
                @foreach ($categories as $category)
                    <li
                        wire:key="blog-category-{{ $category->id }}"
                        @if ($search === '')
                            wire:sortable.item="{{ $category->id }}"
                        @endif
                        class="group flex items-center justify-between gap-x-4 w-full hover:bg-gray-50 last:rounded-b-2xl"
                    >
                        <div
                            class="py-3 w-full min-w-0"
                            style="padding-inline-start: calc(1.5rem + {{ ($category->depth ?? 0) * 1.25 }}rem)"
                        >
                            <div class="flex items-center gap-x-3 min-w-0 pe-4">
                                @if ($search === '')
                                    <button
                                        type="button"
                                        wire:sortable.handle
                                        class="cursor-grab active:cursor-grabbing rounded-md p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 opacity-0 pointer-events-none transition group-hover:opacity-100 group-hover:pointer-events-auto"
                                        aria-label="سحب لإعادة الترتيب"
                                    >
                                        <ui:icon name="grip-vertical" class="!w-4 !h-4" />
                                    </button>
                                @endif

                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gray-100">
                                    <ui:icon name="category" class="!w-5 !h-5 text-gray-400" />
                                </div>

                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $category->id }})"
                                    class="min-w-0 text-start hover:text-primary-600 transition"
                                >
                                    <h2 class="text-base text-gray-700 truncate">{{ $category->name }}</h2>
                                    @if (filled($category->description))
                                        <p class="mt-0.5 text-xs text-gray-500 truncate">{{ $category->description }}</p>
                                    @endif
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center gap-x-1 pe-6 shrink-0">
                            <button
                                type="button"
                                wire:click="openAddCategoryModal({{ $category->id }})"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-primary-50 hover:text-primary-600 opacity-0 pointer-events-none transition group-hover:opacity-100 group-hover:pointer-events-auto"
                                title="إضافة قسم فرعي"
                                aria-label="إضافة قسم فرعي"
                            >
                                <ui:icon name="square-rounded-plus" class="!w-4 !h-4" />
                            </button>

                            <div x-data="{ dropdownMenu: false }">
                                <div class="relative" @click.outside="dropdownMenu=false" x-cloak>
                                    <button @click="dropdownMenu = ! dropdownMenu" type="button"
                                        class="hover:bg-gray-200 p-1 rounded-lg inline-block">
                                        <ui:icon name="dots" class="text-gray-400" />
                                    </button>

                                    <div x-show="dropdownMenu"
                                        class="absolute z-50 mt-2 bg-white border shadow-sm rounded-lg text-gray-800 text-sm flex p-1 ltr:right-0 rtl:left-0 w-48 flex-col gap-y-px"
                                        x-transition.scale.origin.top>
                                        <button type="button"
                                            wire:click="openEditModal({{ $category->id }})"
                                            @click="dropdownMenu = false"
                                            class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2 w-full text-start">
                                            {{ __('Edit') }}
                                        </button>
                                        <button type="button"
                                            wire:click="delete({{ $category->id }})"
                                            wire:confirm="هل أنت متأكد من حذف هذا التصنيف؟"
                                            @click="dropdownMenu = false"
                                            class="hover:bg-stone-100 p-1.5 rounded flex items-center gap-x-2 w-full text-start text-red-600">
                                            {{ __('Delete') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <div wire:loading wire:target="search, delete, openEditModal, openAddCategoryModal, updateCategoryOrder"
            class="absolute inset-0 bg-white opacity-50"></div>

        <div wire:loading.flex wire:target="search, delete, openEditModal, openAddCategoryModal, updateCategoryOrder"
            class="flex justify-center items-center absolute inset-0">
            <ui:icon name="loader-3" class="animate-spin text-gray-300 w-10 h-10" />
        </div>
    </div>

    <ui:modal title="تعديل التصنيف" size="lg" name="edit-blog-category">
        @if ($editingCategoryId)
            <livewire:admin::page.content.blog.category-form
                :contentType="$contentType"
                :category-id="$editingCategoryId"
                :key="'edit-blog-category-'.$editingCategoryId"
            />
        @endif
    </ui:modal>
</div>

<?php

use App\Models\Taxonomy;
use Livewire\Attributes\On;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $search = '';

    public ?int $editingCategoryId = null;

    public ?int $addingParentId = null;

    public function placeholder(): string
    {
        return loadingIcon();
    }

    #[On('updateBlogCategoryList')]
    public function refreshList(): void
    {
        $this->editingCategoryId = null;
        $this->addingParentId = null;
    }

    public function openAddCategoryModal(?int $parentId = null): void
    {
        if ($parentId !== null && ! Taxonomy::query()->type('blog_category')->whereKey($parentId)->exists()) {
            return;
        }

        $this->addingParentId = $parentId;
        $this->dispatch('openmodal', modal: 'add-blog-category');
    }

    public function openEditModal(int $categoryId): void
    {
        if (! Taxonomy::query()->type('blog_category')->whereKey($categoryId)->exists()) {
            return;
        }

        $this->editingCategoryId = $categoryId;
        $this->dispatch('openmodal', modal: 'edit-blog-category');
    }

    public function delete(int $id): void
    {
        Taxonomy::query()->type('blog_category')->whereKey($id)->first()?->delete();

        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    /**
     * @param  array<int, array{order: int, value: string}>  $items
     */
    public function updateCategoryOrder(array $items): void
    {
        if ($this->search !== '') {
            return;
        }

        $orderedIds = collect($items)
            ->sortBy('order')
            ->pluck('value')
            ->map(fn (string $value): int => (int) $value)
            ->values()
            ->all();

        $categories = Taxonomy::query()
            ->type('blog_category')
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');

        $siblingCounters = [];

        foreach ($orderedIds as $id) {
            $category = $categories->get($id);

            if (! $category) {
                continue;
            }

            $parentKey = (string) ($category->parent_id ?? 'root');
            $sortOrder = $siblingCounters[$parentKey] ?? 0;

            $category->update(['sort_order' => $sortOrder]);

            $siblingCounters[$parentKey] = $sortOrder + 1;
        }
    }

    public function with(): array
    {
        $categories = Taxonomy::flatTree('blog_category');

        if ($this->search !== '') {
            $term = mb_strtolower($this->search);

            $categories = $categories->filter(function (Taxonomy $category) use ($term): bool {
                return str_contains(mb_strtolower($category->name), $term)
                    || str_contains(mb_strtolower((string) $category->description), $term);
            })->values();
        }

        return [
            'categories' => $categories,
        ];
    }
}; ?>
