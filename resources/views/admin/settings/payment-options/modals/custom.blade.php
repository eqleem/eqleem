<div class="p-4">
    <ui:form wire:submit="submit" id="custom-payment-form">
        <ui:input
            name="label"
            label="المسمى"
            placeholder="مثال: الدفع عبر stc pay"
        />

        <ui:input
            name="description"
            label="الوصف"
            placeholder="مثال: للطلبات داخل المملكة فقط"
        />

        <ui:textarea
            name="instructions"
            label="تعليمات الدفع"
            placeholder="اكتب التعليمات التي ستظهر للعميل عند اختيار هذه الوسيلة"
            rows="4"
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
    public string $slug = 'custom';

    public string $label = '';

    public string $description = '';

    public string $instructions = '';

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->loadSettings();
    }

    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'instructions' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        Setting::savePaymentMethod($this->slug, [
            'label' => trim($this->label),
            'description' => trim($this->description),
            'instructions' => trim($this->instructions),
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
        $this->instructions = (string) data_get($saved, 'instructions', '');
    }
}; ?>
