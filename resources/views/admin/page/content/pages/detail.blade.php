<div class="bg-white rounded-2xl overflow-hidden" x-data="{ formTab: 'edit', contentTab: 'text' }">
    <div class="bg-stone-200/70 flex items-center justify-between gap-4 px-4 py-3 border-b border-stone-200">
        <div class="flex items-center gap-3 min-w-0">
            <a
                href="{{ route('admin.page.home', ['tab' => $contentType['tab_id']]) }}"
                wire:navigate
                title="{{ __('Back') }}"
                class="bg-white p-2 rounded-lg shadow-sm hover:bg-gray-50 flex items-center justify-center shrink-0"
            >
                <ui:icon name="arrow-right" class="!w-5 !h-5 text-gray-600" />
            </a>
            <div class="flex items-center gap-2 min-w-0 text-sm text-gray-700">
                <img src="{{ asset($contentType['icon']) }}" class="w-5 h-5 shrink-0" alt="">
                <span class="font-semibold truncate">{{ $contentType['name'] }}</span>
                <span class="text-gray-400">/</span>
                <span class="text-gray-600 truncate">تحرير الصفحة</span>
            </div>
        </div>

        <nav class="flex items-center gap-1 shrink-0 bg-gray-300/40 rounded-xl p-0.5">
            <button
                type="button"
                x-on:click="formTab = 'edit'"
                x-bind:class="formTab === 'edit'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition"
            >
                <ui:icon name="Pencil" class="!w-4 !h-4 shrink-0" />
                تحرير
            </button>
            <button
                type="button"
                x-on:click="formTab = 'advanced'"
                x-bind:class="formTab === 'advanced'
                    ? 'bg-white text-gray-900 shadow-sm font-semibold'
                    : 'text-gray-600 hover:bg-white/60 hover:text-gray-800'"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm transition"
            >
                <ui:icon name="adjustments-horizontal" class="!w-4 !h-4 shrink-0" />
                متقدم
            </button>
        </nav>
    </div>

    <ui:form wire:submit="save" class="!p-4 md:!p-6 !rounded-none">
        <div x-cloak x-show="formTab === 'edit'" class="space-y-4">
            <div class="space-y-2">
                <ui:input name="title" placeholder="عنوان الصفحة" />

                <ui:textarea
                    name="subtitle"
                    placeholder="عنوان فرعي"
                    info="عنوان فرعي يظهر تحت العنوان الرئيسي في الصفحة."
                />
            </div>

            <div class="space-y-3">
                <nav class="flex items-center gap-1 border-b border-stone-200">
                    <button
                        type="button"
                        x-on:click="contentTab = 'text'"
                        x-bind:class="contentTab === 'text'
                            ? 'border-b-2 border-primary-500 text-stone-900 font-semibold -mb-px'
                            : 'text-gray-500 hover:text-gray-800'"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm transition"
                    >
                        <ui:icon name="article" class="!w-4 !h-4 shrink-0" />
                        نص
                    </button>
                    <button
                        type="button"
                        x-on:click="contentTab = 'blocks'"
                        x-bind:class="contentTab === 'blocks'
                            ? 'border-b-2 border-primary-500 text-stone-900 font-semibold -mb-px'
                            : 'text-gray-500 hover:text-gray-800'"
                        class="flex items-center gap-1.5 px-3 py-2 text-sm transition"
                    >
                        <ui:icon name="layout-grid" class="!w-4 !h-4 shrink-0" />
                        البلوكات
                    </button>
                </nav>

                <div x-cloak x-show="contentTab === 'text'">
                    <ui:ck
                        name="body"
                        :value="$body"
                        :model-id="$content->id"
                        model-type="content"
                    />
                </div>

                <div x-cloak x-show="contentTab === 'blocks'" class="space-y-3">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-gray-800">بلوكات الصفحة</p>
                            <p class="text-xs text-gray-400 mt-0.5">أضف ورتّب بلوكات محتوى الصفحة.</p>
                        </div>
                        <ui:button
                            type="button"
                            icon="square-rounded-plus"
                            label="إضافة بلوك"
                            @click.prevent="$wire.openAddBlockModal()"
                        />
                    </div>

                    <div class="relative min-h-20 rounded-xl border border-gray-200 bg-gray-50/80">
                        <ul
                            wire:sortable="updateBlockOrder"
                            wire:sortable.options="{ animation: 150 }"
                            wire:key="page-blocks-list-{{ $blocksVersion }}"
                            class="p-2 space-y-1.5"
                        >
                            @foreach ($pageBlocks as $block)
                                <li
                                    wire:sortable.item="{{ $block['id'] }}"
                                    wire:key="page-block-{{ $block['id'] }}"
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

                        @if ($pageBlocks->isEmpty())
                            <p class="pointer-events-none pb-3 inset-0 flex items-center justify-center text-[11px] text-gray-300 select-none min-h-20">
                                أضف بلوكات لصفحتك من الزر بالأعلى
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div x-cloak x-show="formTab === 'advanced'" class="space-y-2">
            <ui:input
                name="slug"
                label="نص الرابط"
                dir="ltr"
                :prefix="$slugPrefix"
            />

            <ui:toggle name="published" label="حالة النشر" live />
        </div>

        <x-slot:footer>
            <div class="flex items-center gap-2">
                <ui:button wire:click="saveAndClose" type="button" target="saveAndClose" label="حفظ وإغلاق" />
                <ui:button type="submit" target="save" label="{{ __('Save') }}" />
            </div>
        </x-slot:footer>
    </ui:form>

    <ui:modal title="إضافة بلوك" size="lg" name="add-page-block">
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

    <ui:modal title="{{ $editingBlockTitle }}" size="lg" name="edit-page-block">
        @if ($editingBlockId && $editingBlockEditor)
            <livewire:dynamic-component
                :is="$editingBlockEditor"
                :block-id="$editingBlockId"
                :key="'page-block-editor-'.$editingBlockId"
            />
        @endif
    </ui:modal>
</div>

<?php

use App\Models\Block;
use App\Models\Content;
use App\Support\BlockTypeRegistry;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

new class extends \Livewire\Component
{
    /** @var array<string, mixed> */
    public array $contentType = [];

    public string $itemId = '';

    public string $title = '';

    public string $subtitle = '';

    public string $body = '';

    public string $editorMode = 'html';

    public string $slug = '';

    public bool $published = false;

    public ?int $editingBlockId = null;

    public ?string $editingBlockEditor = null;

    public string $editingBlockTitle = '';

    public int $blocksVersion = 0;

    public function mount(): void
    {
        $content = $this->content();

        $this->title = $content->title;
        $this->subtitle = (string) data_get($content->data, 'subtitle', '');
        $this->body = (string) data_get($content->data, 'body', '');
        $this->editorMode = (string) data_get($content->data, 'editor_mode', 'html');
        $this->slug = $content->slug;
        $this->published = $content->status === 'published';
    }

    public function content(): Content
    {
        return Content::query()
            ->type(contentTypeModel($this->contentType['slug']))
            ->where('uuid', $this->itemId)
            ->firstOrFail();
    }

    public function openAddBlockModal(): void
    {
        $this->dispatch('openmodal', modal: 'add-page-block');
    }

    public function openEditBlockModal(int $blockId, BlockTypeRegistry $blockTypes): void
    {
        $block = Block::queryForContent($this->content()->id)->find($blockId);

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

        $this->dispatch('openmodal', modal: 'edit-page-block');
    }

    public function addBlock(string $type, BlockTypeRegistry $blockTypes): void
    {
        $blockType = $blockTypes->find($type);

        if (! $blockType || $blockType->default) {
            return;
        }

        $content = $this->content();

        $maxOrder = Block::queryForContent($content->id)
            ->userBlocks()
            ->max('sort_order') ?? 0;

        $block = Block::create([
            'tenant_id' => currentTenantId(),
            'content_id' => $content->id,
            'component' => $blockType->component,
            'type' => $blockType->slug,
            'title' => $blockType->name,
            'sort_order' => $maxOrder + 1,
            'is_default' => false,
            'status' => 'draft',
            'active' => true,
            'position' => 'page',
        ]);

        $this->blocksVersion++;

        $this->dispatch('closemodal', modal: 'add-page-block');

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
            Block::queryForContent($this->content()->id)
                ->userBlocks()
                ->where('id', $item['value'])
                ->update(['sort_order' => $item['order']]);
        }
    }

    public function toggleBlockActive(int $blockId): void
    {
        $block = Block::queryForContent($this->content()->id)
            ->userBlocks()
            ->find($blockId);

        if (! $block) {
            return;
        }

        $block->update(['active' => ! $block->active]);
    }

    public function deleteBlock(int $blockId): void
    {
        Block::queryForContent($this->content()->id)
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
                'editable' => filled($editors[$block->type] ?? null),
                'active' => $block->active,
                'icon_url' => asset($icon),
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function pageBlocks(BlockTypeRegistry $blockTypes): Collection
    {
        $blocks = Block::queryForContent($this->content()->id)
            ->userBlocks()
            ->orderBy('sort_order')
            ->get(['id', 'title', 'type', 'sort_order', 'active']);

        return $this->mapBlocks($blocks, $blockTypes);
    }

    public function slugPrefix(): string
    {
        $base = rtrim((string) (tenant('url') ?? url('/')), '/');

        return $base.'/';
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|min:1|max:255',
            'subtitle' => 'nullable|string|max:500',
            'body' => 'nullable|string',
            'editorMode' => 'required|in:html,markdown',
            'slug' => 'required|string|max:255',
            'published' => 'boolean',
        ];
    }

    public function save(): void
    {
        $this->persist(close: false);
    }

    public function saveAndClose(): void
    {
        $this->persist(close: true);
    }

    public function persist(bool $close = false): mixed
    {
        $this->validate();

        $content = $this->content();
        $data = $content->data ?? [];

        $data['subtitle'] = $this->subtitle;
        $data['body'] = $this->body;
        $data['editor_mode'] = $this->editorMode;

        $slug = $this->uniqueSlug(
            filled($this->slug) ? $this->slug : Str::slug($this->title),
            (int) $content->id,
        );

        $status = $this->published ? 'published' : 'draft';

        $content->update([
            'title' => $this->title,
            'slug' => $slug,
            'status' => $status,
            'data' => $data,
            'published_at' => $this->published
                ? ($content->published_at ?? now())
                : null,
        ]);

        $this->slug = $slug;
        $this->dispatch('updatePagesList');
        $this->dispatch('notify', text: __('Saved'));

        if ($close) {
            return $this->redirect(route('admin.page.home', [
                'tab' => $this->contentType['tab_id'],
            ]), navigate: true);
        }

        return null;
    }

    private function uniqueSlug(string $baseSlug, int $exceptId): string
    {
        $slug = $baseSlug !== '' ? $baseSlug : 'page';
        $counter = 1;

        while (
            Content::query()
                ->where('slug', $slug)
                ->whereKeyNot($exceptId)
                ->exists()
        ) {
            $slug = $baseSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function render(BlockTypeRegistry $blockTypes)
    {
        return $this->view([
            'content' => $this->content(),
            'slugPrefix' => $this->slugPrefix(),
            'blockTypes' => $blockTypes->options(addableOnly: true),
            'pageBlocks' => $this->pageBlocks($blockTypes),
            'blocksVersion' => $this->blocksVersion,
        ]);
    }
}; ?>
