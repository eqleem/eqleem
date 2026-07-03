<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        اختر نوع الرابط أولاً — يُعبّأ العنوان والوصف تلقائياً ويمكنك تعديلهما لاحقاً.
    </p>

    <div class="space-y-2">
        <ui:select name="linkType" label="نوع الرابط *" :options="$linkTypeOptions" live />

        <ui:input
            name="title"
            label="عنوان الرابط"
            placeholder="{{ $this->titlePlaceholder() }}"
            info="{{ $this->titleHint() }}"
        />

        <ui:textarea name="description" label="الوصف" placeholder="وصف قصير يظهر تحت العنوان" rows="3" />

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
                                    wire:focus="showRecentContent"
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
                                        wire:key="block-link-content-{{ $result->id }}"
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

    <x-slot:footer>
        <ui:button type="submit" target="save" label="{{ __('Save') }}" />
    </x-slot:footer>
</ui:form>

<?php

use App\Livewire\Concerns\EditsBlock;
use App\Models\Content;
use App\Support\CtaLink;
use Illuminate\Support\Str;

new class extends \Livewire\Component
{
    use EditsBlock;

    public string $linkType = '';

    public string $title = '';

    public string $description = '';

    public ?int $contentId = null;

    public string $contentSearch = '';

    public string $selectedContentTitle = '';

    public bool $showContentResults = false;

    protected function blockType(): string
    {
        return 'block-link';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];
        $options = CtaLink::contentLinkTypeOptions();
        $hasSavedLink = filled($data['link_type'] ?? null) && filled($data['content_type'] ?? null);

        if ($hasSavedLink) {
            $this->linkType = CtaLink::typeKeyFromData($data);

            if (! array_key_exists($this->linkType, $options)) {
                $this->linkType = '';
            }

            $this->title = (string) ($data['title'] ?? '');
            $this->description = (string) ($data['description'] ?? '');
        } else {
            $this->linkType = '';
            $this->title = '';
            $this->description = '';
        }

        $this->contentId = filled($data['content_id'] ?? null) ? (int) $data['content_id'] : null;
        $this->selectedContentTitle = $this->contentId
            ? (Content::query()->find($this->contentId)?->title ?? '')
            : '';
        $this->contentSearch = $this->selectedContentTitle;
    }

    public function updatedLinkType(): void
    {
        $this->contentId = null;
        $this->contentSearch = '';
        $this->selectedContentTitle = '';
        $this->showContentResults = false;
        $this->resetErrorBag('contentId');

        if (filled($this->linkType)) {
            $this->applyLinkTypeDefaults();
        } else {
            $this->title = '';
            $this->description = '';
        }
    }

    protected function applyLinkTypeDefaults(): void
    {
        $this->title = CtaLink::blockLinkTitleFromTypeKey($this->linkType);
        $this->description = CtaLink::blockLinkDescriptionFromTypeKey($this->linkType);
    }

    public function showRecentContent(): void
    {
        if (mb_strlen(trim($this->contentSearch)) < 2) {
            $this->showContentResults = true;
        }
    }

    public function updatedContentSearch(): void
    {
        $this->contentId = null;
        $this->selectedContentTitle = '';

        $searchLength = mb_strlen(trim($this->contentSearch));

        $this->showContentResults = match (true) {
            $searchLength >= 2 => true,
            $searchLength === 0 => $this->showContentResults,
            default => false,
        };

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

    public function needsContentPicker(): bool
    {
        return str_starts_with($this->linkType, 'item:');
    }

    public function titlePlaceholder(): string
    {
        if (str_starts_with($this->linkType, 'section:')) {
            $contentType = Str::after($this->linkType, 'section:');

            return config('content-types.'.$contentType.'.name', 'عنوان الرابط');
        }

        if (str_starts_with($this->linkType, 'item:')) {
            return 'اتركه فارغاً لاستخدام عنوان المحتوى';
        }

        return 'عنوان الرابط';
    }

    public function titleHint(): string
    {
        if (str_starts_with($this->linkType, 'item:')) {
            return 'اختياري — يُستخدم عنوان المحتوى المحدد إذا تُرك فارغاً.';
        }

        return 'يُعبّأ تلقائياً من نوع الرابط ويمكنك تعديله.';
    }

    public function contentPickerLabel(): string
    {
        $contentType = Str::after($this->linkType, 'item:');

        return config('cta-link-types.item_labels.'.$contentType, 'اختر المحتوى');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $rules = [
            'linkType' => 'required|in:'.implode(',', array_keys(CtaLink::contentLinkTypeOptions())),
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
        ];

        if ($this->needsContentPicker()) {
            $rules['contentId'] = 'required|integer';
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate($this->rules(), [
            'linkType.required' => 'يرجى اختيار نوع الرابط.',
            'contentId.required' => 'يرجى اختيار محتوى من نتائج البحث.',
        ]);

        if ($this->needsContentPicker() && ! $this->findPickableContent((int) $this->contentId)) {
            $this->addError('contentId', 'يرجى اختيار محتوى صالح من القائمة.');

            return;
        }

        $parsed = CtaLink::parseTypeKey($this->linkType);

        $data = [
            'link_type' => $parsed['link_type'],
            'content_type' => $parsed['content_type'],
            'content_id' => $this->needsContentPicker() ? $this->contentId : null,
            'title' => $this->title,
            'description' => $this->description,
        ];

        $blockTitle = $this->title ?: CtaLink::titleFromData($data);

        $this->block()->update([
            'title' => $blockTitle,
            'data' => $data,
        ]);

        $this->notifyStructureChanged($blockTitle);

        $this->dispatch('closemodal');
    }

    protected function findPickableContent(int $id): ?Content
    {
        if (! str_starts_with($this->linkType, 'item:')) {
            return null;
        }

        $contentType = Str::after($this->linkType, 'item:');

        return Content::query()->type(CtaLink::modelType($contentType))->whereKey($id)->first();
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'linkTypeOptions' => ['' => 'اختر نوع الرابط...'] + CtaLink::contentLinkTypeOptions(),
            'contentResults' => $this->needsContentPicker() && $this->showContentResults
                ? (mb_strlen(trim($this->contentSearch)) >= 2
                    ? CtaLink::searchContents($this->linkType, $this->contentSearch)
                    : CtaLink::recentContents($this->linkType))
                : collect(),
        ];
    }
}; ?>
