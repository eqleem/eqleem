<div class="p-4">
    <ui:form wire:submit="submit" id="tamara-form">
        <ui:input name="apiToken" label="API Token" placeholder="..." dir="ltr" />
        <ui:input name="notificationToken" label="Notification Token" placeholder="..." dir="ltr" />

        <ui:input
            name="minLimit"
            label="الحد الأدنى"
            type="number"
            min="0"
            step="0.01"
            placeholder="99"
            dir="ltr"
            info="أقل مبلغ لقبول الدفع عبر تمارا"
        />

        <ui:input name="label" label="المسمى" placeholder="مثال: ادفع على دفعات مع تمارا" />
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
    public string $slug = 'tamara';

    public string $apiToken = '';

    public string $notificationToken = '';

    public ?string $minLimit = null;

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
            'apiToken' => ['nullable', 'string', 'max:255'],
            'notificationToken' => ['nullable', 'string', 'max:255'],
            'minLimit' => ['nullable', 'numeric', 'min:0'],
            'label' => ['nullable', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        Setting::savePaymentMethod($this->slug, [
            'api_token' => trim($this->apiToken),
            'notification_token' => trim($this->notificationToken),
            'min_limit' => filled($this->minLimit) ? (float) $this->minLimit : null,
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

        $this->apiToken = (string) data_get($saved, 'api_token', '');
        $this->notificationToken = (string) data_get($saved, 'notification_token', '');
        $this->minLimit = data_get($saved, 'min_limit') !== null
            ? (string) data_get($saved, 'min_limit')
            : null;
        $this->label = (string) data_get($saved, 'label', '');
        $this->description = (string) data_get($saved, 'description', '');
    }
}; ?>
