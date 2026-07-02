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
                <ui:select name="linkType" label="نوع الرابط" :options="$linkTypeOptions" live />

                <ui:input
                    name="label"
                    label="اسم الرابط"
                    placeholder="{{ $this->labelPlaceholder() }}"
                    info="{{ $this->labelHint() }}"
                />

                @if ($this->isExternalLink())
                    <ui:input name="url" label="الرابط" placeholder="https://..." dir="ltr" />
                    <ui:input
                        name="icon"
                        label="الأيقونة"
                        placeholder="hugeicons:calendar-add-01"
                        dir="ltr"
                        info="اسم أيقونة من مكتبة iconify"
                    />
                @endif

                @if ($this->needsContentPicker())
                    <div class="space-y-2">
                        @if ($contentId && $selectedContentTitle)
                            <ui:field name="contentId" label="{{ $this->contentPickerLabel() }} *">
                                <div class="flex items-center justify-between rounded-lg bg-gray-50 p-3">
                                    <p class="text-sm font-medium text-gray-800">{{ $selectedContentTitle }}</p>
                                    <button
                                        type="button"
                                        wire:click="clearContentSelection"
                                        class="text-xs text-red-500 hover:text-red-700 px-2 py-1 rounded hover:bg-red-50"
                                    >
                                        تغيير
                                    </button>
                                </div>
                            </ui:field>
                        @else
                            <div class="relative">
                                <ui:field name="contentSearch" label="{{ $this->contentPickerLabel() }} *">
                                    <div class="relative">
                                        <div class="absolute ps-2 right-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500">
                                            <ui:icon name="search" class="text-gray-400" />
                                        </div>
                                        <input
                                            wire:model.live.debounce.300ms="contentSearch"
                                            type="text"
                                            placeholder="ابحث بالاسم..."
                                            class="block w-full rounded-lg py-2 ps-10 text-gray-800 border border-gray-200 placeholder:text-gray-400 focus:border-primary-500 focus:outline-none sm:text-sm @error('contentId') border-red-300 @enderror"
                                        >
                                    </div>
                                </ui:field>

                                @if ($showContentResults && $contentResults->isNotEmpty())
                                    <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                        @foreach ($contentResults as $result)
                                            <button
                                                type="button"
                                                wire:click="selectContent({{ $result->id }})"
                                                wire:key="cta-content-{{ $result->id }}"
                                                class="w-full text-start px-3 py-2 hover:bg-gray-50 border-b border-gray-50 last:border-0"
                                            >
                                                <p class="text-sm font-semibold text-gray-800">{{ $result->title }}</p>
                                            </button>
                                        @endforeach
                                    </div>
                                @elseif ($showContentResults && mb_strlen(trim($contentSearch)) >= 2)
                                    <div class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg p-3 text-sm text-gray-500">
                                        لا توجد نتائج.
                                    </div>
                                @endif

                                @error('contentId')
                                    <small class="text-red-600 text-xs px-1">{{ $message }}</small>
                                @enderror
                            </div>
                        @endif
                    </div>
                @endif
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
use App\Models\Content;
use App\Support\CtaLink;
use Illuminate\Support\Str;

new class extends \Livewire\Component
{
    use EditsBlock;

    public ?int $editingLinkId = null;

    public string $linkType = 'external';

    public string $label = '';

    public string $url = '';

    public string $icon = 'hugeicons:link-04';

    public ?int $contentId = null;

    public string $contentSearch = '';

    public string $selectedContentTitle = '';

    public bool $showContentResults = false;

    protected function blockType(): string
    {
        return 'cta';
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
        $this->linkType = CtaLink::typeKey($link);
        $this->label = (string) ($data['label'] ?? '');
        $this->url = (string) ($data['url'] ?? '');
        $this->icon = (string) ($data['icon'] ?? 'hugeicons:link-04');
        $this->contentId = filled($data['content_id'] ?? null) ? (int) $data['content_id'] : null;
        $this->selectedContentTitle = $this->contentId
            ? (Content::query()->find($this->contentId)?->title ?? '')
            : '';
        $this->contentSearch = $this->selectedContentTitle;
        $this->showContentResults = false;
    }

    public function updatedContentSearch(): void
    {
        $this->contentId = null;
        $this->selectedContentTitle = '';
        $this->showContentResults = mb_strlen(trim($this->contentSearch)) >= 2;
        $this->resetErrorBag('contentId');
    }

    public function updatedLinkType(): void
    {
        $this->contentId = null;
        $this->contentSearch = '';
        $this->selectedContentTitle = '';
        $this->showContentResults = false;
        $this->resetErrorBag('contentId');
    }

    public function selectContent(int $id): void
    {
        $content = $this->findPickableContent($id);

        if (! $content) {
            return;
        }

        $this->contentId = $content->id;
        $this->selectedContentTitle = $content->title;
        $this->contentSearch = $content->title;
        $this->showContentResults = false;
        $this->resetErrorBag('contentId');
    }

    public function clearContentSelection(): void
    {
        $this->contentId = null;
        $this->contentSearch = '';
        $this->selectedContentTitle = '';
        $this->showContentResults = false;
        $this->resetErrorBag('contentId');
    }

    public function isExternalLink(): bool
    {
        return $this->linkType === 'external';
    }

    public function needsContentPicker(): bool
    {
        return str_starts_with($this->linkType, 'item:') || $this->linkType === 'form';
    }

    public function labelPlaceholder(): string
    {
        if ($this->isExternalLink()) {
            return 'مثال: تواصل معنا';
        }

        if ($this->linkType === 'form') {
            return 'اتركه فارغاً لاستخدام اسم النموذج';
        }

        if (str_starts_with($this->linkType, 'section:')) {
            $contentType = Str::after($this->linkType, 'section:');

            return config('content-types.'.$contentType.'.name', 'اسم الرابط');
        }

        if (str_starts_with($this->linkType, 'item:')) {
            return 'اتركه فارغاً لاستخدام عنوان المحتوى';
        }

        return 'اسم الرابط';
    }

    public function labelHint(): string
    {
        if ($this->isExternalLink()) {
            return 'مطلوب للروابط الخارجية.';
        }

        return 'اختياري — يُستخدم اسم المحتوى أو القسم تلقائياً إذا تُرك فارغاً.';
    }

    public function contentPickerLabel(): string
    {
        if ($this->linkType === 'form') {
            return 'اختر النموذج';
        }

        $contentType = Str::after($this->linkType, 'item:');

        return config('cta-link-types.item_labels.'.$contentType, 'اختر المحتوى');
    }

    public function saveLink(): void
    {
        $rules = [
            'linkType' => 'required|in:'.implode(',', array_keys(CtaLink::typeOptions())),
            'label' => 'nullable|string|max:100',
        ];

        if ($this->isExternalLink()) {
            $rules['label'] = 'required|string|max:100';
            $rules['url'] = 'required|url|max:500';
            $rules['icon'] = 'required|string|max:100';
        }

        if ($this->needsContentPicker()) {
            $rules['contentId'] = 'required|integer';
        }

        $messages = [
            'contentId.required' => 'يرجى اختيار '.($this->linkType === 'form' ? 'نموذج' : 'محتوى').' من نتائج البحث.',
        ];

        $this->validate($rules, $messages);

        if ($this->needsContentPicker() && ! $this->findPickableContent((int) $this->contentId)) {
            $this->addError('contentId', 'يرجى اختيار عنصر صالح من القائمة.');

            return;
        }

        $parsed = CtaLink::parseTypeKey($this->linkType);

        $data = [
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'label' => $this->label,
            'url' => $this->isExternalLink() ? $this->url : null,
            'icon' => $this->isExternalLink() ? $this->icon : null,
            'content_id' => $this->needsContentPicker() ? $this->contentId : null,
        ];

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
        $this->linkType = 'external';
        $this->label = '';
        $this->url = '';
        $this->icon = 'hugeicons:link-04';
        $this->contentId = null;
        $this->contentSearch = '';
        $this->selectedContentTitle = '';
        $this->showContentResults = false;
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

    protected function findPickableContent(int $id): ?Content
    {
        if ($this->linkType === 'form') {
            return Content::query()->type('form')->whereKey($id)->first();
        }

        if (str_starts_with($this->linkType, 'item:')) {
            $contentType = Str::after($this->linkType, 'item:');

            return Content::query()->type($contentType)->whereKey($id)->first();
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'linkTypeOptions' => CtaLink::typeOptions(),
            'ctaLinks' => Content::query()
                ->where('block_id', $this->blockId)
                ->type('cta-link')
                ->orderBy('sort_order')
                ->get(),
            'contentResults' => $this->needsContentPicker() && $this->showContentResults
                ? CtaLink::searchContents($this->linkType, $this->contentSearch)
                : collect(),
        ];
    }
}; ?>
