<ui:form wire:submit="submit" class="!p-0">

    <div class="space-y-2">
        <ui:input
            name="sectionTitle"
            label="عنوان قسم المنتجات الرقمية"
            placeholder="المنتجات الرقمية"
        />

        <ui:textarea
            name="sectionDescription"
            label="وصف القسم"
            placeholder="منتجات رقمية قابلة للتحميل والوصول الفوري"
            rows="3"
        />
    </div>

    <x-slot:footer>
        <ui:button target="submit" label="{{ __('Save') }}" />
    </x-slot>
</ui:form>

<?php

use App\Models\Setting;

new class extends \Livewire\Component
{
    public string $sectionTitle = '';

    public string $sectionDescription = '';

    public function rules(): array
    {
        return [
            'sectionTitle' => ['required', 'string', 'min:2', 'max:255'],
            'sectionDescription' => ['required', 'string', 'min:2', 'max:500'],
        ];
    }

    public function mount(): void
    {
        $settings = Setting::digitalProductSettings();

        $this->sectionTitle = $settings['section_title'];
        $this->sectionDescription = $settings['section_description'];
    }

    public function submit(): void
    {
        $this->validate();

        Setting::saveForSlug(Setting::DIGITAL_PRODUCT_SETTINGS_SLUG, [
            'section_title' => trim($this->sectionTitle),
            'section_description' => trim($this->sectionDescription),
        ]);

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }
}; ?>
