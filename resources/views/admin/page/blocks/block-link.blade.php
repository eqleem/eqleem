<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        إعدادات بطاقة الرابط مع الأيقونة والوصف.
    </p>

    <div class="space-y-2">
        <ui:input name="title" label="العنوان" placeholder="عنوان البطاقة" />
        <ui:textarea name="description" label="الوصف" placeholder="وصف قصير يظهر تحت العنوان" />
        <ui:input name="url" label="الرابط" placeholder="https://..." dir="ltr" />
        <ui:input
            name="icon"
            label="الأيقونة"
            placeholder="hugeicons:store-02"
            dir="ltr"
            info="اسم أيقونة من مكتبة iconify"
        />
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

    public string $description = '';

    public string $url = '#';

    public string $icon = 'hugeicons:store-02';

    protected function blockType(): string
    {
        return 'block-link';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->title = (string) ($data['title'] ?? $this->block()->title ?? '');
        $this->description = (string) ($data['description'] ?? '');
        $this->url = (string) ($data['url'] ?? '#');
        $this->icon = (string) ($data['icon'] ?? 'hugeicons:store-02');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'url' => 'required|string|max:500',
            'icon' => 'nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->block()->update([
            'title' => $this->title,
            'data' => [
                'title' => $this->title,
                'description' => $this->description,
                'url' => $this->url,
                'icon' => $this->icon,
            ],
        ]);

        $this->dispatch('closemodal');
    }
}; ?>
