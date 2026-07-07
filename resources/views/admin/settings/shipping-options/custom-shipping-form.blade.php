<ui:form wire:submit="submit" class="max-h-[75vh] overflow-y-auto">
    <ui:toggle name="active" label="الحالة" live />

    <ui:input name="name" label="الاسم" placeholder="مثال: محمد للمندوب" />

    <ui:input
        name="price"
        label="السعر"
        type="number"
        min="0"
        step="0.01"
        placeholder="24"
        
        suffix="{{ money_symbol() }}"
    />

    <ui:select
        name="country"
        label="الدولة"
        :options="$countryOptions"
        live
        info="اختر دولة محددة أو كل الدول."
    />

    @if ($country !== \App\Support\WorldLocationOptions::ALL_COUNTRIES)
        <ui:tags-select
            name="cityIds"
            label="المدن"
            :options="$cityOptions"
            :selected="$cityIds"
            placeholder="ابحث عن مدينة أو اختر كل المدن"
            search-name="citySearch"
            open-name="cityPickerOpen"
            live
            info="يمكنك اختيار مدن محددة أو خيار كل المدن داخل الدولة."
        />
    @endif

    <x-slot:footer>
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                @if ($optionId)
                    <button
                        type="button"
                        wire:click="deleteOption"
                        wire:confirm="هل أنت متأكد من حذف خدمة الشحن هذه؟"
                        wire:loading.attr="disabled"
                        wire:target="deleteOption"
                        class="text-sm text-red-500 hover:text-red-600"
                    >
                        حذف؟
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-2">
                <ui:button
                    type="button"
                    variant="ghost"
                    label="إلغاء"
                    wire:click="cancel"
                />
                <ui:button target="submit" :label="$optionId ? 'تعديل' : 'إضافة'" />
            </div>
        </div>
    </x-slot:footer>
</ui:form>

<?php

use App\Models\Setting;
use App\Support\WorldLocationOptions;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;

new class extends \Livewire\Component
{
    #[Locked]
    public ?string $optionId = null;

    public bool $active = true;

    public string $name = '';

    public ?string $price = null;

    public string $country = 'SA';

    /** @var list<string> */
    public array $cityIds = [];

    public string $citySearch = '';

    public bool $cityPickerOpen = false;

    public function mount(?string $optionId = null): void
    {
        $this->optionId = $optionId;

        if ($optionId) {
            $this->loadOption();
        }
    }

    public function updatedCountry(): void
    {
        $this->cityIds = [];
        $this->citySearch = '';
        $this->cityPickerOpen = false;
    }

    public function updatedCityIds(): void
    {
        $this->cityIds = array_values(array_unique(array_map(
            fn (mixed $id): string => (string) $id,
            array_filter(
                $this->cityIds,
                fn (mixed $id): bool => is_string($id) || is_int($id),
            ),
        )));

        if (in_array(WorldLocationOptions::ALL_CITIES, $this->cityIds, true)) {
            $this->cityIds = [WorldLocationOptions::ALL_CITIES];
        }
    }

    public function updatedCitySearch(): void
    {
        // Re-render city options when search changes.
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $locations = app(WorldLocationOptions::class);

        $rules = [
            'active' => ['boolean'],
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'country' => ['required', 'string', Rule::in($locations->selectableCountryIds())],
        ];

        if ($this->country !== WorldLocationOptions::ALL_COUNTRIES) {
            $rules['cityIds'] = ['required', 'array', 'min:1'];
            $rules['cityIds.*'] = [
                'required',
                'string',
                Rule::in($locations->selectableCityIds($this->country, $this->cityIds)),
            ];
        }

        return $rules;
    }

    public function submit(): void
    {
        $this->validate();

        $items = Setting::customShippingOptions();
        $allCities = in_array(WorldLocationOptions::ALL_CITIES, $this->cityIds, true);

        $payload = [
            'id' => $this->optionId ?? (string) Str::uuid(),
            'name' => trim($this->name),
            'price' => (float) $this->price,
            'country' => $this->country,
            'all_cities' => $this->country === WorldLocationOptions::ALL_COUNTRIES || $allCities,
            'city_ids' => $this->country === WorldLocationOptions::ALL_COUNTRIES
                ? []
                : ($allCities ? [WorldLocationOptions::ALL_CITIES] : array_values($this->cityIds)),
            'active' => $this->active,
        ];

        if ($this->optionId) {
            $items = collect($items)
                ->map(fn (array $item): array => ($item['id'] ?? null) === $this->optionId ? $payload : $item)
                ->values()
                ->all();
        } else {
            $items[] = $payload;
        }

        Setting::saveCustomShippingOptions($items);

        $this->dispatch('customShippingSaved');
        $this->dispatch('closemodal', modal: 'custom-shipping-form');
        $this->dispatch('notify', text: __('Saved'));
    }

    public function deleteOption(): void
    {
        if (! $this->optionId) {
            return;
        }

        $items = collect(Setting::customShippingOptions())
            ->reject(fn (array $item): bool => ($item['id'] ?? null) === $this->optionId)
            ->values()
            ->all();

        Setting::saveCustomShippingOptions($items);

        $this->dispatch('customShippingSaved');
        $this->dispatch('closemodal', modal: 'custom-shipping-form');
        $this->dispatch('notify', text: __('Item(s) deleted successfully.'));
    }

    public function cancel(): void
    {
        $this->dispatch('closemodal', modal: 'custom-shipping-form');
    }

    protected function loadOption(): void
    {
        $option = collect(Setting::customShippingOptions())
            ->firstWhere('id', $this->optionId);

        if (! is_array($option)) {
            return;
        }

        $this->active = (bool) ($option['active'] ?? true);
        $this->name = (string) ($option['name'] ?? '');
        $this->price = isset($option['price']) ? (string) $option['price'] : null;
        $this->country = (string) ($option['country'] ?? 'SA');
        $this->cityIds = array_values(array_map(
            fn (mixed $id): string => (string) $id,
            (array) ($option['city_ids'] ?? []),
        ));

        if (($option['all_cities'] ?? false) && $this->country !== WorldLocationOptions::ALL_COUNTRIES) {
            $this->cityIds = [WorldLocationOptions::ALL_CITIES];
        }
    }

    public function render()
    {
        $locations = app(WorldLocationOptions::class);

        return $this->view([
            'countryOptions' => $locations->countrySelectOptions(),
            'cityOptions' => $locations->citySelectOptions($this->country, $this->citySearch, $this->cityIds),
        ]);
    }
}; ?>
