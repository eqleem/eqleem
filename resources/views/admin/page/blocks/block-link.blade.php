<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        اختر نوع الرابط أولاً — يُعبّأ العنوان والوصف تلقائياً ويمكنك تعديلهما لاحقاً.
    </p>

    <ui:link-fields
        profile="block"
        content-key="block-link"
        :link-type="$linkType"
        :content-id="$contentId"
        :content-search="$contentSearch"
        :selected-content-title="$selectedContentTitle"
        :show-content-results="$showContentResults"
        :link-type-options="$linkTypeOptions"
        :content-results="$contentResults"
    />

    <x-slot:footer>
        <ui:button type="submit" target="save" label="{{ __('Save') }}" />
    </x-slot:footer>
</ui:form>

<?php

use App\Livewire\Concerns\EditsBlock;
use App\Livewire\Concerns\ManagesCtaLinkFields;
use App\Models\Content;
use App\Support\CtaLink;

new class extends \Livewire\Component
{
    use EditsBlock;
    use ManagesCtaLinkFields;

    public string $title = '';

    public string $description = '';

    protected function blockType(): string
    {
        return 'block-link';
    }

    protected function ctaLinkProfile(): string
    {
        return 'block';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];
        $hasSavedLink = filled($data['link_type'] ?? null) && filled($data['content_type'] ?? null);

        if ($hasSavedLink) {
            $this->loadCtaLinkFieldsFromData($data);
            $this->title = (string) ($data['title'] ?? '');
            $this->description = (string) ($data['description'] ?? '');
        } else {
            $this->resetCtaLinkFields();
            $this->title = '';
            $this->description = '';
        }
    }

    public function save(): void
    {
        if (! $this->validateCtaLinkFields()) {
            return;
        }

        $data = $this->buildCtaLinkData();
        $blockTitle = $this->title ?: CtaLink::titleFromData($data);

        $this->block()->update([
            'title' => $blockTitle,
            'data' => $data,
        ]);

        $this->notifyStructureChanged($blockTitle);

        $this->dispatch('closemodal');
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'linkTypeOptions' => $this->ctaLinkTypeOptions(),
            'contentResults' => $this->ctaLinkContentResults(),
        ];
    }
}; ?>
