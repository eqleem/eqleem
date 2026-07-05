<div>

    <ui:mainbox title="اللغة" subtitle="حدد اللغة الافتراضية واللغات المتاحة لزوار صفحتك.">
        <x-slot:icon>
            <ui:icon name="language" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>

        <ui:form wire:submit="submit" id="language-form">
            <ui:select
                name="defaultLanguage"
                label="اللغة الافتراضية"
                :options="$defaultLanguageOptions"
                live
            />

            <ui:checkbox-select
                name="availableLanguages"
                label="اللغات المتاحة"
                :options="$languageOptions"
                :selected="$availableLanguages"
                placeholder="اختر اللغات"
                live
            />

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot:footer>
        </ui:form>
    </ui:mainbox>

    <ui:mainbox title="العملة" subtitle="حدد العملة الافتراضية والعملات المتاحة للاستخدام في صفحتك." class="mt-10">
        <x-slot:icon>
            <ui:icon name="currency-riyal" class="!w-7 !h-7 text-gray-500 p-0.5" />
        </x-slot:icon>

        <ui:form wire:submit="submit" id="currency-form">
            <ui:select
                name="defaultCurrency"
                label="العملة الافتراضية"
                :options="$defaultCurrencyOptions"
                live
            />

            <ui:checkbox-select
                name="availableCurrencies"
                label="العملات المتاحة"
                :options="$currencyOptions"
                :selected="$availableCurrencies"
                placeholder="اختر العملات"
                live
            />

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot:footer>
        </ui:form>
    </ui:mainbox>
</div>

<?php

use App\Models\Setting;
use Illuminate\Validation\Rule;

new class extends \Livewire\Component
{
    public string $defaultLanguage = 'ar';

    public string $defaultCurrency = 'SAR';

    /** @var list<string> */
    public array $availableLanguages = ['ar'];

    /** @var list<string> */
    public array $availableCurrencies = ['SAR'];

    public function rules(): array
    {
        $languageKeys = array_keys(config('locales.languages', []));
        $currencyKeys = array_keys(config('locales.currencies', []));

        return [
            'defaultLanguage' => ['required', 'string', Rule::in($this->availableLanguages)],
            'defaultCurrency' => ['required', 'string', Rule::in($this->availableCurrencies)],
            'availableLanguages' => ['required', 'array', 'min:1'],
            'availableLanguages.*' => ['required', 'string', Rule::in($languageKeys)],
            'availableCurrencies' => ['required', 'array', 'min:1'],
            'availableCurrencies.*' => ['required', 'string', Rule::in($currencyKeys)],
        ];
    }

    public function mount(): void
    {
        $settings = Setting::localeCurrencySettings();

        $this->defaultLanguage = (string) $settings['default_language'];
        $this->defaultCurrency = (string) $settings['default_currency'];
        $this->availableLanguages = $settings['available_languages'];
        $this->availableCurrencies = $settings['available_currencies'];

        $this->ensureDefaultsAreAvailable();
    }

    public function updatedAvailableLanguages(): void
    {
        $this->availableLanguages = array_values(array_unique(array_filter(
            $this->availableLanguages,
            fn (mixed $code): bool => is_string($code) && $code !== '',
        )));

        $this->ensureDefaultsAreAvailable();
    }

    public function updatedAvailableCurrencies(): void
    {
        $this->availableCurrencies = array_values(array_unique(array_filter(
            $this->availableCurrencies,
            fn (mixed $code): bool => is_string($code) && $code !== '',
        )));

        $this->ensureDefaultsAreAvailable();
    }

    public function submit(): void
    {
        $this->validate();

        Setting::saveLocaleCurrencySettings([
            'default_language' => $this->defaultLanguage,
            'default_currency' => $this->defaultCurrency,
            'available_languages' => array_values($this->availableLanguages),
            'available_currencies' => array_values($this->availableCurrencies),
        ]);

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }

    protected function ensureDefaultsAreAvailable(): void
    {
        if ($this->availableLanguages === []) {
            $this->availableLanguages = [(string) config('locales.defaults.default_language', 'ar')];
        }

        if ($this->availableCurrencies === []) {
            $this->availableCurrencies = [(string) config('locales.defaults.default_currency', 'SAR')];
        }

        if (! in_array($this->defaultLanguage, $this->availableLanguages, true)) {
            $this->defaultLanguage = $this->availableLanguages[0];
        }

        if (! in_array($this->defaultCurrency, $this->availableCurrencies, true)) {
            $this->defaultCurrency = $this->availableCurrencies[0];
        }
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    protected function languageOptions(): array
    {
        return collect(config('locales.languages', []))
            ->map(fn (string $label, string $code): array => [
                'id' => $code,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: string, label: string}>
     */
    protected function currencyOptions(): array
    {
        return collect(config('locales.currencies', []))
            ->map(fn (string $label, string $code): array => [
                'id' => $code,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function with(): array
    {
        return [
            'languageOptions' => $this->languageOptions(),
            'currencyOptions' => $this->currencyOptions(),
            'defaultLanguageOptions' => collect(config('locales.languages', []))
                ->only($this->availableLanguages)
                ->all(),
            'defaultCurrencyOptions' => collect(config('locales.currencies', []))
                ->only($this->availableCurrencies)
                ->all(),
        ];
    }
};
?>
