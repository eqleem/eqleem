<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        تخصيص محتوى تذييل الصفحة.
    </p>

    <div class="space-y-2">
        <ui:toggle name="showCopyright" label="عرض حقوق النشر" live />
        <ui:toggle name="showContactEmail" label="عرض البريد الإلكتروني" live />
        <ui:toggle name="showPoweredBy" label="عرض «صُنع بواسطة»" live />

        @if ($showCopyright)
            <ui:input name="copyrightText" label="نص حقوق النشر" placeholder="جميع الحقوق محفوظة © {{ date('Y') }}" />
        @endif

        @if ($showContactEmail)
            <ui:input name="contactEmail" label="البريد الإلكتروني" placeholder="info@example.com" dir="ltr" />
        @endif

        @if ($showPoweredBy)
            <ui:input name="poweredByText" label="نص «صُنع بواسطة»" placeholder="صُنع بواسطة إقليم" />
            <ui:input name="poweredByUrl" label="رابط «صُنع بواسطة»" placeholder="https://eqleem.com" dir="ltr" />
        @endif
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

    public bool $showCopyright = true;

    public string $copyrightText = '';

    public bool $showContactEmail = true;

    public string $contactEmail = '';

    public bool $showPoweredBy = true;

    public string $poweredByText = 'صُنع بواسطة إقليم';

    public string $poweredByUrl = '';

    protected function blockType(): string
    {
        return 'footer';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->showCopyright = (bool) ($data['show_copyright'] ?? true);
        $this->copyrightText = (string) ($data['copyright_text'] ?? '');
        $this->showContactEmail = (bool) ($data['show_contact_email'] ?? true);
        $this->contactEmail = (string) ($data['contact_email'] ?? '');
        $this->showPoweredBy = (bool) ($data['show_powered_by'] ?? true);
        $this->poweredByText = (string) ($data['powered_by_text'] ?? 'صُنع بواسطة إقليم');
        $this->poweredByUrl = (string) ($data['powered_by_url'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'copyrightText' => 'nullable|string|max:255',
            'contactEmail' => 'nullable|email|max:255',
            'poweredByText' => 'nullable|string|max:100',
            'poweredByUrl' => 'nullable|url|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->saveData([
            'show_copyright' => $this->showCopyright,
            'copyright_text' => $this->copyrightText,
            'show_contact_email' => $this->showContactEmail,
            'contact_email' => $this->contactEmail,
            'show_powered_by' => $this->showPoweredBy,
            'powered_by_text' => $this->poweredByText,
            'powered_by_url' => $this->poweredByUrl,
        ]);
    }
}; ?>
