<div>
    <ui:mainbox>

        <ui:alert color="blue" text="إدارة ترتيب الأقسام وإضافة أو إزالة الكتل من الصفحة الرئيسية." class="mx-4" />
     

        <div
            wire:sortable-group="updateBlockOrder"
            class="p-4 space-y-6"
        >
            @foreach ($positions as $position)
                <section
                    wire:key="block-position-{{ $position['slug'] }}"
                    class="rounded-xl borderx border-gray-200 bg-stone-100 overflow-hidden"
                >
                    <div class="flex items-center justify-between gap-3 px-4 py-3 xborder-b border-gray-200/80 bg-white/60">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <img
                                src="{{ $position['icon_url'] }}"
                                alt=""
                                class="w-5 h-5 shrink-0 opacity-70"
                            >
                            <div class="min-w-0">
                                <h3 class="text-sm font-semibold text-gray-800">{{ $position['name'] }}</h3>
                                <p class="text-xs text-gray-500 truncate">{{ $position['description'] }}</p>
                            </div>
                        </div>

                        <ui:button
                            variant="outline"
                            icon="square-rounded-plus"
                            label="إضافة بلوك"
                            class="!h-8 !px-2.5 !text-xs"
                            @click.prevent="$wire.openAddBlockModal('{{ $position['slug'] }}')"
                        />
                    </div>

                    <div class="relative min-h-14">
                        <ul
                            wire:sortable-group.item-group="{{ $position['slug'] }}"
                            wire:sortable-group.options="{ animation: 150 }"
                            class="p-2 space-y-1.5"
                        >
                            @foreach ($blocksByPosition[$position['slug']] ?? [] as $block)
                                <li
                                    wire:sortable-group.item="{{ $block['id'] }}"
                                    wire:key="block-{{ $block['id'] }}"
                                    class="group flex items-center gap-2 rounded-lg border border-transparent bg-white px-2 py-2 hover:border-gray-200 transition"
                                >
                                    <button
                                        type="button"
                                        wire:sortable-group.handle
                                        class="cursor-grab active:cursor-grabbing rounded-md p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 transition"
                                        aria-label="سحب لإعادة الترتيب"
                                    >
                                        <ui:icon name="grip-vertical" class="!w-4 !h-4" />
                                    </button>

                                    <div class="flex-1 min-w-0 text-sm font-medium text-gray-800 truncate">
                                        {{ $block['title'] }}
                                    </div>

                                    <button
                                        type="button"
                                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition"
                                        aria-label="خيارات"
                                    >
                                        <ui:icon name="dots" class="!w-4 !h-4" />
                                    </button>

                                    <img
                                        src="{{ $block['icon_url'] }}"
                                        alt=""
                                        class="w-8 h-8 shrink-0 rounded-lg bg-gray-100 p-1.5"
                                    >
                                </li>
                            @endforeach
                        </ul>

                        @if (collect($blocksByPosition[$position['slug']] ?? [])->isEmpty())
                            <div class="pointer-events-none absolutex inset-0 flex items-center justify-center text-[11px] text-gray-300 select-none pb-3">
                                أضف بلوك أو اسحب بلوكات هنا
                            </div>
                        @endif
                    </div>
                </section>
            @endforeach
        </div>

        <ui:modal
            title="{{ $addBlockModalTitle }}"
            size="lg"
            name="add-block"
        >
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
    </ui:mainbox>
</div>

<?php

use App\Models\Block;
use App\Support\BlockPositionRegistry;
use App\Support\BlockTypeRegistry;
use Illuminate\Support\Collection;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $tab = [];

    public ?string $addBlockPosition = null;

    public function openAddBlockModal(string $position, BlockPositionRegistry $positions): void
    {
        if (! $positions->find($position)) {
            return;
        }

        $this->addBlockPosition = $position;

        $this->dispatch('openmodal', modal: 'add-block');
    }

    public function addBlock(string $type, BlockTypeRegistry $blockTypes, BlockPositionRegistry $positions): void
    {
        $blockType = $blockTypes->find($type);
        $blockPosition = $this->addBlockPosition
            ? $positions->find($this->addBlockPosition)
            : null;

        if (! $blockType || ! $blockPosition) {
            return;
        }

        $position = $blockPosition->slug;

        $tenantId = currentTenantId();

        $maxOrder = Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->where('position', $position)
            ->max('sort_order') ?? 0;

        Block::create([
            'tenant_id' => $tenantId,
            'component' => $blockType->component,
            'type' => $blockType->slug,
            'position' => $position,
            'title' => $blockType->name,
            'sort_order' => $maxOrder + 1,
            'status' => 'draft',
            'active' => true,
        ]);

        $this->dispatch('closemodal');
    }

    /**
     * @param  array<int, array{order: int, value: string, items: array<int, array{order: int, value: string}>}>  $groups
     */
    public function updateBlockOrder(array $groups, BlockPositionRegistry $positions): void
    {
        $validPositions = $positions->all()->pluck('slug')->all();
        $tenantId = currentTenantId();

        foreach ($groups as $group) {
            $position = $group['value'];

            if (! in_array($position, $validPositions, true)) {
                continue;
            }

            foreach ($group['items'] as $item) {
                Block::query()
                    ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                    ->whereNull('parent_id')
                    ->where('id', $item['value'])
                    ->update([
                        'position' => $position,
                        'sort_order' => $item['order'],
                    ]);
            }
        }
    }

    /**
     * @return array<string, Collection<int, array<string, mixed>>>
     */
    protected function blocksByPosition(BlockPositionRegistry $positions, BlockTypeRegistry $blockTypes): array
    {
        $tenantId = currentTenantId();
        $typeIcons = $blockTypes->all()->mapWithKeys(
            fn ($blockType): array => [$blockType->slug => $blockType->icon]
        );

        $blocks = Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get(['id', 'title', 'type', 'position', 'sort_order']);

        $grouped = $blocks->groupBy('position');

        return $positions->all()
            ->mapWithKeys(function ($position) use ($grouped, $typeIcons): array {
                $items = ($grouped->get($position->slug) ?? collect())
                    ->map(function (Block $block) use ($typeIcons): array {
                        $icon = $typeIcons->get($block->type, 'assets/icons/tabler/Blockquote.svg');

                        return [
                            'id' => $block->id,
                            'title' => $block->title,
                            'type' => $block->type,
                            'position' => $block->position,
                            'sort_order' => $block->sort_order,
                            'icon_url' => asset($icon),
                        ];
                    });

                return [$position->slug => $items];
            })
            ->all();
    }

    public function render(BlockPositionRegistry $positions, BlockTypeRegistry $blockTypes)
    {
        $activePosition = $this->addBlockPosition
            ? $positions->find($this->addBlockPosition)
            : null;

        return $this->view([
            'positions' => $positions->options(),
            'blockTypes' => $blockTypes->options(),
            'blocksByPosition' => $this->blocksByPosition($positions, $blockTypes),
            'addBlockModalTitle' => $activePosition
                ? 'إضافة بلوك — '.$activePosition->name
                : 'إضافة بلوك',
        ]);
    }
}; ?>
