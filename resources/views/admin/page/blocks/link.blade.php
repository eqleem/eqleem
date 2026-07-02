<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        إعدادات الرابط البسيط.
    </p>

    <div class="space-y-2">
        <ui:input name="title" label="العنوان" placeholder="عنوان الرابط" />
        <ui:input name="url" label="الرابط" placeholder="https://..." dir="ltr" />
        <ui:toggle name="openInNewTab" label="فتح في تبويب جديد" />
    </div>

    <x-slot:footer>
        <ui:button type="submit" target="save" label="{{ __('Save') }}" />
    </x-slot:footer>
</ui:form>

<?php

use App\Livewire\Concerns\EditsBlock;

new class extends \Livewire\Component
{
    use EditsBlock;

    public string $title = '';

    public string $url = '';

    public bool $openInNewTab = false;

    protected function blockType(): string
    {
        return 'link';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->title = (string) ($data['title'] ?? $this->block()->title ?? '');
        $this->url = (string) ($data['url'] ?? '');
        $this->openInNewTab = (bool) ($data['open_in_new_tab'] ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->block()->update([
            'title' => $this->title,
            'data' => [
                'title' => $this->title,
                'url' => $this->url,
                'open_in_new_tab' => $this->openInNewTab,
            ],
        ]);

        $this->notifyStructureChanged($this->title);

        $this->dispatch('closemodal');
    }
}; ?>
