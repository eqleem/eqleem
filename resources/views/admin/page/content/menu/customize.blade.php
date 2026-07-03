<ui:form wire:submit="submit" class="!p-0">

    <div class="space-y-2">
        <ui:input
            name="sectionTitle"
            label="عنوان قسم قائمة الطعام"
            placeholder="قائمة الطعام"
        />

        <ui:textarea
            name="sectionDescription"
            label="وصف القسم"
            placeholder="وجبات طازجة مع أحجام وإضافات متنوعة"
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
        $settings = Setting::menuSettings();

        $this->sectionTitle = $settings['section_title'];
        $this->sectionDescription = $settings['section_description'];
    }

    public function submit(): void
    {
        $this->validate();

        Setting::saveForSlug(Setting::MENU_SETTINGS_SLUG, [
            'section_title' => trim($this->sectionTitle),
            'section_description' => trim($this->sectionDescription),
        ]);

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }
}; ?>
