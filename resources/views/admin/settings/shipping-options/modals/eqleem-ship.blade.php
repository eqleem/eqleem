<div class="p-4">
    <div class="mb-4 overflow-hidden rounded-xl border border-gray-200">
        <div class="grid grid-cols-3 divide-x divide-x-reverse divide-gray-100 bg-gray-50 text-center text-xs font-medium text-gray-600">
            <div class="px-3 py-3">
                <p>الشحن الداخلي</p>
                <p class="mt-1 text-sm font-semibold text-gray-800">21 {{ money_symbol() }}</p>
                <p class="text-[11px] text-gray-400">2-3 أيام</p>
            </div>
            <div class="px-3 py-3">
                <p>الشحن الخليجي</p>
                <p class="mt-1 text-sm font-semibold text-gray-800">41 {{ money_symbol() }}</p>
                <p class="text-[11px] text-gray-400">5-7 أيام</p>
            </div>
            <div class="px-3 py-3">
                <p>الشحن الدولي</p>
                <p class="mt-1 text-sm font-semibold text-gray-800">81 {{ money_symbol() }}</p>
                <p class="text-[11px] text-gray-400">7-14 يوم</p>
            </div>
        </div>
    </div>

    <ui:callout color="red" class="mb-4">
        إقليم شيب متاح حالياً للمتاجر السعودية فقط. الأسعار أعلاه إرشادية ويمكنك تخصيصها أدناه.
    </ui:callout>

    <ui:form wire:submit="submit" id="eqleem-ship-form">
        <ui:toggle name="active" label="الحالة" info="فعّل أو عطّل هذه الوسيلة لعملائك." live />

        <ui:input
            name="label"
            label="الاسم"
            placeholder="مثال: شحن سريع"
        />

        <ui:input
            name="domesticPrice"
            label="سعر الشحن الداخلي"
            type="number"
            min="0"
            step="0.01"
            placeholder="25"
            dir="ltr"
            suffix="{{ money_symbol() }}"
        />

        <ui:input
            name="gulfPrice"
            label="سعر الشحن الخليجي"
            type="number"
            min="0"
            step="0.01"
            placeholder="45"
            dir="ltr"
            suffix="{{ money_symbol() }}"
        />

        <ui:input
            name="internationalPrice"
            label="سعر الشحن الدولي"
            type="number"
            min="0"
            step="0.01"
            placeholder="85"
            dir="ltr"
            suffix="{{ money_symbol() }}"
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
    public string $slug = 'eqleem-ship';

    public bool $active = false;

    public string $label = '';

    public ?string $domesticPrice = null;

    public ?string $gulfPrice = null;

    public ?string $internationalPrice = null;

    public function mount(string $slug): void
    {
        $this->slug = $slug;
        $this->loadSettings();
    }

    public function rules(): array
    {
        return [
            'active' => ['boolean'],
            'label' => ['nullable', 'string', 'max:120'],
            'domesticPrice' => ['nullable', 'numeric', 'min:0'],
            'gulfPrice' => ['nullable', 'numeric', 'min:0'],
            'internationalPrice' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function submit(): void
    {
        $this->validate();

        Setting::saveShippingMethod($this->slug, [
            'label' => trim($this->label),
            'domestic_price' => filled($this->domesticPrice) ? (float) $this->domesticPrice : null,
            'gulf_price' => filled($this->gulfPrice) ? (float) $this->gulfPrice : null,
            'international_price' => filled($this->internationalPrice) ? (float) $this->internationalPrice : null,
        ], $this->active);

        $this->dispatch('shippingMethodSaved');
        $this->dispatch('notify', text: __('Settings updated successfully.'));
        $this->dispatch('closemodal', modal: 'shipping-method-'.$this->slug);
    }

    protected function loadSettings(): void
    {
        $saved = Setting::shippingMethod($this->slug);

        $this->active = (bool) data_get($saved, 'active', false);
        $this->label = (string) data_get($saved, 'label', '');
        $this->domesticPrice = data_get($saved, 'domestic_price') !== null
            ? (string) data_get($saved, 'domestic_price')
            : null;
        $this->gulfPrice = data_get($saved, 'gulf_price') !== null
            ? (string) data_get($saved, 'gulf_price')
            : null;
        $this->internationalPrice = data_get($saved, 'international_price') !== null
            ? (string) data_get($saved, 'international_price')
            : null;
    }
}; ?>
