<div class="p-4">
    <ui:form wire:submit="submit" id="tabby-form">
        <ui:input name="publicKey" label="Public Key" placeholder="pk_..." dir="ltr" />
        <ui:input name="secretKey" label="Secret Key" placeholder="sk_..." dir="ltr" />

        <ui:input
            name="minLimit"
            label="الحد الأدنى"
            type="number"
            min="0"
            step="0.01"
            placeholder="99"
            dir="ltr"
            info="أقل مبلغ لقبول الدفع عبر تابي"
        />

        <ui:input
            name="maxLimit"
            label="الحد الأقصى"
            type="number"
            min="0"
            step="0.01"
            placeholder="5000"
            dir="ltr"
            info="أعلى مبلغ لقبول الدفع عبر تابي"
        />

        <ui:input name="label" label="المسمى" placeholder="مثال: ادفع على 4 دفعات مع تابي" />
        <ui:input name="description" label="الوصف" placeholder="مثال: بدون رسوم أو فوائد" />

        <x-slot:footer>
            <ui:button target="submit" icon="check" label="{{ __('Save') }}" />
        </x-slot:footer>
    </ui:form>
</div>

<?php

use App\Models\Setting;

new class extends \Livewire\Component
{
    public string $slug = 'tabby';

    public string $publicKey = '';

    public string $secretKey = '';

    public ?string $minLimit = null;

    public ?string $maxLimit = null;

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
            'publicKey' => ['nullable', 'string', 'max:255'],
            'secretKey' => ['nullable', 'string', 'max:255'],
            'minLimit' => ['nullable', 'numeric', 'min:0'],
            'maxLimit' => ['nullable', 'numeric', 'min:0'],
            'label' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        Setting::savePaymentMethod($this->slug, [
            'public_key' => trim($this->publicKey),
            'secret_key' => trim($this->secretKey),
            'min_limit' => filled($this->minLimit) ? (float) $this->minLimit : null,
            'max_limit' => filled($this->maxLimit) ? (float) $this->maxLimit : null,
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

        $this->publicKey = (string) data_get($saved, 'public_key', '');
        $this->secretKey = (string) data_get($saved, 'secret_key', '');
        $this->minLimit = data_get($saved, 'min_limit') !== null
            ? (string) data_get($saved, 'min_limit')
            : null;
        $this->maxLimit = data_get($saved, 'max_limit') !== null
            ? (string) data_get($saved, 'max_limit')
            : null;
        $this->label = (string) data_get($saved, 'label', '');
        $this->description = (string) data_get($saved, 'description', '');
    }
}; ?>
