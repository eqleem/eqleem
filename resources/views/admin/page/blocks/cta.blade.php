<div x-data="{ linkModal: false }" x-on:cta-link-saved.window="linkModal = false">
    <div class="!p-4 space-y-2">
            <ui:button
                class="w-full"
                type="button"
                variant="secondary"
                icon="square-rounded-plus"
                label="إضافة رابط"
                x-on:click="linkModal = true"
                wire:click="openAddModal"
            />

        @if ($ctaLinks->isEmpty())
            <p class="text-xs text-gray-400 py-2">لا توجد روابط بعد. أضف أول زر إجراء.</p>
        @else
            <ul
                wire:sortable="updateLinkOrder"
                wire:sortable.options="{ animation: 150 }"
                class="space-y-1.5"
            >
                @foreach ($ctaLinks as $link)
                    <li
                        wire:sortable.item="{{ $link->id }}"
                        wire:key="cta-link-{{ $link->id }}"
                        class="group flex items-center gap-2 rounded-lg border border-gray-100 bg-white px-2 py-2 hover:border-gray-200 transition"
                    >
                        <button
                            type="button"
                            wire:sortable.handle
                            class="cursor-grab active:cursor-grabbing rounded-md p-1 text-gray-300 hover:bg-gray-100 hover:text-gray-500 transition"
                            aria-label="سحب لإعادة الترتيب"
                        >
                            <ui:icon name="grip-vertical" class="!w-4 !h-4" />
                        </button>

                        <iconify-icon icon="{{ \App\Support\CtaLink::icon($link) }}" class="text-xl text-gray-500 shrink-0"></iconify-icon>

                        <button
                            type="button"
                            wire:click="openEditModal({{ $link->id }})"
                            x-on:click="linkModal = true"
                            class="flex flex-1 min-w-0 flex-col items-start text-start hover:text-primary-600 transition"
                        >
                            <span class="text-sm font-medium text-gray-800 truncate">{{ \App\Support\CtaLink::label($link) }}</span>
                            <span class="text-xs text-gray-400 truncate">{{ \App\Support\CtaLink::typeLabel($link) }} · {{ \App\Support\CtaLink::summary($link) }}</span>
                        </button>

                        <button
                            type="button"
                            wire:click="deleteLink({{ $link->id }})"
                            wire:confirm="هل أنت متأكد من حذف هذا الرابط؟"
                            wire:loading.attr="disabled"
                            wire:target="deleteLink({{ $link->id }})"
                            class="shrink-0 rounded-lg p-1 text-red-400/80 hover:bg-red-50 hover:text-red-500 transition"
                            aria-label="حذف الرابط"
                        >
                            <ui:icon name="trash" class="!w-4 !h-4" />
                        </button>
                    </li>
                @endforeach
            </ul>
        @endif

        <div class="flex justify-end pt-4">
            <ui:button type="button" wire:click="close" label="تم" />
        </div>
    </div>

    <div
        x-show="linkModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition.opacity
    >
        <div class="absolute inset-0 bg-gray-800/75" x-on:click="linkModal = false"></div>

        <div class="relative w-full max-w-lg rounded-xl bg-white shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-gray-100 p-3 px-4 sticky top-0 bg-white z-10">
                <p class="text-sm font-semibold text-gray-600">{{ $editingLinkId ? 'تعديل رابط' : 'إضافة رابط' }}</p>
                <button type="button" x-on:click="linkModal = false" class="rounded-md bg-gray-100 p-1 text-gray-400 hover:bg-gray-200">
                    <ui:icon name="x" class="!w-4 !h-4" />
                </button>
            </div>

            <div class="space-y-3 p-4">
                <ui:link-fields
                    profile="nav"
                    content-key="cta"
                    :link-type="$linkType"
                    :content-id="$contentId"
                    :content-search="$contentSearch"
                    :selected-content-title="$selectedContentTitle"
                    :show-content-results="$showContentResults"
                    :link-type-options="$linkTypeOptions"
                    :content-results="$contentResults"
                />
            </div>

            <div class="flex justify-end gap-2 border-t border-gray-100 p-3 px-4 sticky bottom-0 bg-white">
                <ui:button type="button" variant="ghost" label="إلغاء" x-on:click="linkModal = false" />
                <ui:button type="button" wire:click="saveLink" target="saveLink" label="{{ $editingLinkId ? 'حفظ' : 'إضافة' }}" />
            </div>
        </div>
    </div>
</div>

<?php

use App\Livewire\Concerns\EditsBlock;
use App\Livewire\Concerns\ManagesCtaLinkFields;
use App\Models\Content;
use App\Support\CtaLink;
use Illuminate\Support\Str;

new class extends \Livewire\Component
{
    use EditsBlock;
    use ManagesCtaLinkFields;

    public ?int $editingLinkId = null;

    public string $label = '';

    public string $url = '';

    public string $icon = 'hugeicons:link-04';

    protected function blockType(): string
    {
        return 'cta';
    }

    protected function ctaLinkProfile(): string
    {
        return 'nav';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;
    }

    public function openAddModal(): void
    {
        $this->resetLinkForm();
        $this->editingLinkId = null;
    }

    public function openEditModal(int $id): void
    {
        $link = $this->findLink($id);

        if (! $link) {
            return;
        }

        $data = $link->data ?? [];

        $this->editingLinkId = $link->id;
        $this->loadCtaLinkFieldsFromData($data, $link);
        $this->label = (string) ($data['label'] ?? '');
        $this->url = (string) ($data['url'] ?? '');
        $this->icon = (string) ($data['icon'] ?? 'hugeicons:link-04');
    }

    public function saveLink(): void
    {
        if (! $this->validateCtaLinkFields()) {
            return;
        }

        $parsed = CtaLink::parseTypeKey($this->linkType);
        $data = $this->buildCtaLinkData();

        if ($this->editingLinkId) {
            $link = $this->findLink($this->editingLinkId);

            if (! $link) {
                return;
            }

            $link->update([
                'title' => $this->label ?: $this->defaultTitle($parsed),
                'data' => $data,
            ]);
        } else {
            $maxOrder = Content::query()
                ->where('block_id', $this->blockId)
                ->type('cta-link')
                ->max('sort_order') ?? 0;

            Content::create([
                'block_id' => $this->blockId,
                'type' => 'cta-link',
                'title' => $this->label ?: $this->defaultTitle($parsed),
                'slug' => 'cta-'.Str::lower(Str::random(8)),
                'data' => $data,
                'sort_order' => $maxOrder + 1,
                'active' => true,
                'status' => 'published',
                'published_at' => now(),
            ]);
        }

        $this->resetLinkForm();
        $this->editingLinkId = null;
        $this->dispatch('cta-link-saved');
    }

    public function deleteLink(int $id): void
    {
        $this->findLink($id)?->delete();
    }

    /**
     * @param  array<int, array{order: int, value: string}>  $items
     */
    public function updateLinkOrder(array $items): void
    {
        foreach ($items as $item) {
            Content::query()
                ->where('block_id', $this->blockId)
                ->type('cta-link')
                ->whereKey($item['value'])
                ->update(['sort_order' => $item['order']]);
        }
    }

    public function close(): void
    {
        $this->dispatch('closemodal');
    }

    /**
     * @param  array{link_type: string, content_type: ?string}  $parsed
     */
    protected function defaultTitle(array $parsed): string
    {
        if ($parsed['link_type'] === 'section' && filled($parsed['content_type'])) {
            return (string) config('content-types.'.$parsed['content_type'].'.name', 'رابط');
        }

        if ($this->contentId) {
            return Content::query()->find($this->contentId)?->title ?? 'رابط';
        }

        return 'رابط';
    }

    protected function resetLinkForm(): void
    {
        $this->resetCtaLinkFields();
        $this->label = '';
        $this->url = '';
        $this->icon = 'hugeicons:link-04';
        $this->resetErrorBag();
    }

    protected function findLink(int $id): ?Content
    {
        return Content::query()
            ->where('block_id', $this->blockId)
            ->type('cta-link')
            ->whereKey($id)
            ->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'linkTypeOptions' => $this->ctaLinkTypeOptions(),
            'ctaLinks' => Content::query()
                ->where('block_id', $this->blockId)
                ->type('cta-link')
                ->orderBy('sort_order')
                ->get(),
            'contentResults' => $this->ctaLinkContentResults(),
        ];
    }
}; ?>
