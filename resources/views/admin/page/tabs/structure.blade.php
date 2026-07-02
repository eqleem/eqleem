<div>
    <ui:mainbox title="هيكل الصفحة" subtitle="إدارة وترتيب بلوكات الصفحة الرئيسية.">
        <x-slot:icon>
            <img src="{{ asset($tab['icon']) }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <x-slot:actions>
            <ui:button
                icon="square-rounded-plus"
                label="إضافة بلوك"
                @click.prevent="$wire.openAddBlockModal()"
            />
        </x-slot:actions>

        <div class="p-4 space-y-4">
            @if ($topBlocks->isNotEmpty())
                <div class="rounded-xl border border-gray-200 bg-gray-50/80 overflow-hidden">
                    <ul class="p-2 space-y-1.5">
                        @foreach ($topBlocks as $block)
                            @include('admin.page.partials.structure-system-block', ['block' => $block])
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="relative min-h-20 rounded-xl border border-gray-200 bg-gray-50/80">
                <ul
                    wire:sortable="updateBlockOrder"
                    wire:sortable.options="{ animation: 150 }"
                    wire:key="user-blocks-list-{{ $blocksVersion }}"
                    class="p-2 space-y-1.5"
                >
                    @foreach ($userBlocks as $block)
                        <li
                            wire:sortable.item="{{ $block['id'] }}"
                            wire:key="block-{{ $block['id'] }}"
                            class="group flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2 hover:border-gray-200 transition @unless ($block['active']) opacity-50 @endunless"
                        >
                            <button
                                type="button"
                                wire:sortable.handle
                                class="cursor-grab active:cursor-grabbing rounded-md p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 transition"
                                aria-label="سحب لإعادة الترتيب"
                            >
                                <ui:icon name="grip-vertical" class="!w-4 !h-4" />
                            </button>

                            @if ($block['editable'])
                                <button
                                    type="button"
                                    wire:click="openEditBlockModal({{ $block['id'] }})"
                                    class="flex flex-1 min-w-0 items-center gap-2 text-start hover:text-primary-600 transition"
                                >
                                    <img
                                        src="{{ $block['icon_url'] }}"
                                        alt=""
                                        class="w-6 h-6 shrink-0 rounded-md bg-gray-100 p-1"
                                    >
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $block['title'] }}</span>
                                </button>
                            @else
                                <div class="flex flex-1 min-w-0 items-center gap-2">
                                    <img
                                        src="{{ $block['icon_url'] }}"
                                        alt=""
                                        class="w-6 h-6 shrink-0 rounded-md bg-gray-100 p-1"
                                    >
                                    <span class="text-sm font-medium text-gray-800 truncate">{{ $block['title'] }}</span>
                                </div>
                            @endif

                            <button
                                type="button"
                                wire:click.stop="deleteBlock({{ $block['id'] }})"
                                wire:confirm="هل أنت متأكد من حذف هذا البلوك؟"
                                wire:loading.attr="disabled"
                                wire:target="deleteBlock({{ $block['id'] }})"
                                class="shrink-0 rounded-lg p-1 text-red-400/80 hover:bg-red-50 hover:text-red-500 opacity-0 pointer-events-none transition group-hover:opacity-100 group-hover:pointer-events-auto"
                                aria-label="حذف البلوك"
                            >
                                <ui:icon name="trash" class="!w-4 !h-4" />
                            </button>

                            @if ($block['editable'])
                                <button
                                    type="button"
                                    wire:click="openEditBlockModal({{ $block['id'] }})"
                                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-primary-600 transition"
                                    aria-label="خيارات البلوك"
                                >
                                    <ui:icon name="settings-cog" class="!w-4 !h-4" />
                                </button>
                            @endif

                            <button
                                type="button"
                                wire:click.stop="toggleBlockActive({{ $block['id'] }})"
                                wire:loading.attr="disabled"
                                wire:target="toggleBlockActive({{ $block['id'] }})"
                                class="shrink-0 rounded-lg p-1 hover:bg-gray-100 transition disabled:opacity-50"
                                aria-label="{{ $block['active'] ? 'تعطيل البلوك' : 'تفعيل البلوك' }}"
                                role="switch"
                                aria-checked="{{ $block['active'] ? 'true' : 'false' }}"
                            >
                                <span
                                    @class([
                                        'relative inline-block h-5 w-9 rounded-full transition-colors duration-200',
                                        'bg-gray-200' => ! $block['active'],
                                        'bg-primary-500' => $block['active'],
                                    ])
                                >
                                    <span
                                        @class([
                                            'absolute top-0.5 size-4 rounded-full bg-white shadow-sm transition-all duration-200',
                                            'start-0.5' => ! $block['active'],
                                            'end-0.5 start-auto' => $block['active'],
                                        ])
                                    ></span>
                                </span>
                            </button>
                        </li>
                    @endforeach
                </ul>

                @if ($userBlocks->isEmpty())
                    <p class="pointer-events-none absolutex pb-3 inset-0 flex items-center justify-center text-[11px] text-gray-300 select-none">
                       أضف بلوات لصفحتك من الزر بالأعلى
                    </p>
                @endif
            </div>

            @if ($bottomBlocks->isNotEmpty())
                <div class="rounded-xl border border-gray-200 bg-gray-50/80 overflow-hidden">
                    <ul class="p-2 space-y-1.5">
                        @foreach ($bottomBlocks as $block)
                            @include('admin.page.partials.structure-system-block', ['block' => $block])
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <ui:modal title="إضافة بلوك" size="lg" name="add-block">
            <div class="p-4 space-y-2">
                @foreach ($blockTypes as $blockType)
                    <button
                        type="button"
                        wire:click="addBlock('{{ $blockType['slug'] }}')"
                        wire:loading.attr="disabled"
                        wire:target="addBlock('{{ $blockType['slug'] }}')"
                        class="flex w-full items-center gap-3 rounded-xl border border-gray-100 px-3 py-3 text-start hover:border-gray-200 hover:bg-gray-50 transition disabled:opacity-50"
                    >
                        <img
                            src="{{ $blockType['icon_url'] }}"
                            alt=""
                            class="w-9 h-9 shrink-0 rounded-lg bg-gray-100 p-1.5"
                        >
                        <span class="min-w-0">
                            <span class="block text-sm font-medium text-gray-800">{{ $blockType['name'] }}</span>
                            <span class="block text-xs text-gray-400">{{ $blockType['description'] }}</span>
                        </span>
                    </button>
                @endforeach
            </div>
        </ui:modal>

        <ui:modal title="{{ $editingBlockTitle }}" size="lg" name="edit-block">
            @if ($editingBlockId && $editingBlockEditor)
                <livewire:dynamic-component
                    :is="$editingBlockEditor"
                    :block-id="$editingBlockId"
                    :key="'block-editor-'.$editingBlockId"
                />
            @endif
        </ui:modal>
    </ui:mainbox>
</div>

<?php

use App\Models\Block;
use App\Support\BlockTypeRegistry;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $tab = [];

    public ?int $editingBlockId = null;

    public ?string $editingBlockEditor = null;

    public string $editingBlockTitle = '';

    public int $blocksVersion = 0;

    public function openAddBlockModal(): void
    {
        $this->dispatch('openmodal', modal: 'add-block');
    }

    public function openEditBlockModal(int $blockId, BlockTypeRegistry $blockTypes): void
    {
        $block = Block::queryForTenantRoots()->find($blockId);

        if (! $block) {
            return;
        }

        $blockType = $blockTypes->find($block->type);

        if (! $blockType?->editor) {
            return;
        }

        $this->editingBlockId = $block->id;
        $this->editingBlockEditor = $blockType->editor;
        $this->editingBlockTitle = $block->title ?? $blockType->name;

        $this->dispatch('openmodal', modal: 'edit-block');
    }

    public function addBlock(string $type, BlockTypeRegistry $blockTypes): void
    {
        $blockType = $blockTypes->find($type);

        if (! $blockType || $blockType->default) {
            return;
        }

        $maxOrder = Block::queryForTenantRoots()
            ->userBlocks()
            ->max('sort_order') ?? 0;

        $block = Block::create([
            'tenant_id' => currentTenantId(),
            'component' => $blockType->component,
            'type' => $blockType->slug,
            'title' => $blockType->name,
            'sort_order' => $maxOrder + 1,
            'is_default' => false,
            'status' => 'draft',
            'active' => true,
            'position' => 'home',
        ]);

        $this->blocksVersion++;

        $this->dispatch('closemodal');

        $this->openEditBlockModal($block->id, $blockTypes);
    }

    #[On('structure-blocks-changed')]
    public function onStructureBlocksChanged(?int $blockId = null, ?string $title = null): void
    {
        $this->blocksVersion++;

        if ($blockId === $this->editingBlockId && filled($title)) {
            $this->editingBlockTitle = $title;
        }
    }

    /**
     * @param  array<int, array{order: int, value: string}>  $items
     */
    public function updateBlockOrder(array $items): void
    {
        foreach ($items as $item) {
            Block::queryForTenantRoots()
                ->userBlocks()
                ->where('id', $item['value'])
                ->update(['sort_order' => $item['order']]);
        }
    }

    public function toggleBlockActive(int $blockId): void
    {
        $block = Block::queryForTenantRoots()
            ->userBlocks()
            ->find($blockId);

        if (! $block) {
            return;
        }

        $block->update(['active' => ! $block->active]);
    }

    public function deleteBlock(int $blockId): void
    {
        Block::queryForTenantRoots()
            ->userBlocks()
            ->where('id', $blockId)
            ->delete();

        $this->blocksVersion++;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapBlocks(Collection $blocks, BlockTypeRegistry $blockTypes): Collection
    {
        $typeIcons = $blockTypes->iconPaths();
        $editors = $blockTypes->editors();

        return $blocks->map(function (Block $block) use ($typeIcons, $editors): array {
            $icon = $typeIcons[$block->type] ?? 'assets/icons/tabler/Blockquote.svg';

            return [
                'id' => $block->id,
                'title' => $block->title,
                'type' => $block->type,
                'sort_order' => $block->sort_order,
                'is_default' => $block->is_default,
                'editable' => filled($editors[$block->type] ?? null),
                'active' => $block->active,
                'icon_url' => asset($icon),
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function blocksForTypes(Collection $blocks, array $types): Collection
    {
        return collect($types)
            ->map(fn (string $type): ?array => $blocks->firstWhere('type', $type))
            ->filter()
            ->values();
    }

    /**
     * @return array{top: Collection<int, array<string, mixed>>, user: Collection<int, array<string, mixed>>, bottom: Collection<int, array<string, mixed>>}
     */
    protected function groupedBlocks(BlockTypeRegistry $blockTypes): array
    {
        $blocks = Block::queryForTenantRoots()
            ->orderBy('sort_order')
            ->get(['id', 'title', 'type', 'sort_order', 'is_default', 'active']);

        $mapped = $this->mapBlocks($blocks, $blockTypes);
        $system = $mapped->where('is_default', true)->values();

        return [
            'top' => $this->blocksForTypes($system, ['top-nav', 'header', 'cta']),
            'user' => $mapped->where('is_default', false)->values(),
            'bottom' => $this->blocksForTypes($system, ['footer', 'float-links']),
        ];
    }

    public function render(BlockTypeRegistry $blockTypes)
    {
        $grouped = $this->groupedBlocks($blockTypes);

        return $this->view([
            'blockTypes' => $blockTypes->options(addableOnly: true),
            'topBlocks' => $grouped['top'],
            'userBlocks' => $grouped['user'],
            'bottomBlocks' => $grouped['bottom'],
            'blocksVersion' => $this->blocksVersion,
        ]);
    }
}; ?>
