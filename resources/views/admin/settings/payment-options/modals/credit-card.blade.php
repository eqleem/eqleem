<div class="p-4">
    <ui:form wire:submit="submit" id="credit-card-form">
        <ui:input
            name="label"
            label="المسمى"
            placeholder="مثال: بطاقة ائتمانية"
        />

        <ui:input
            name="description"
            label="الوصف"
            placeholder="مثال: فيزا، ماستركارد، مدى"
        />

        <x-slot:footer>
            <ui:button target="submit" icon="check" label="{{ __('Save') }}" />
        </x-slot:footer>
    </ui:form>
</div>

<?php

use App\Models\Setting;

new class extends \Livewire\Component
{
    public string $slug = 'credit-card';

    public string $label = '';

    public string $description = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->loadSettings();
    }

    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        Setting::savePaymentMethod($this->slug, [
            'label' => trim($this->label),
            'description' => trim($this->description),
        ], (bool) data_get(Setting::paymentMethod($this->slug), 'active', false));

        $this->dispatch('paymentMethodSaved');
        $this->dispatch('notify', text: __('Settings updated successfully.'));
        $this->dispatch('closemodal', modal: 'payment-method-'.$this->slug);
    }

    protected function loadSettings(): void
    {
        $saved = Setting::paymentMethod($this->slug);

        $this->label = (string) data_get($saved, 'label', '');
        $this->description = (string) data_get($saved, 'description', '');
    }
}; ?>
