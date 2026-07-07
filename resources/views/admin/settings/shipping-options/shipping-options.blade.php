<div class="space-y-10">
    <ui:mainbox title="وسائل الشحن" subtitle="قم بتفعيل وتخصيص طرق الشحن المتاحة لعملائك.">
        <x-slot:icon>
            <img src="{{ asset('assets/icons/ecommerce/018-cart.svg') }}" alt="" class="h-6 w-6">
        </x-slot:icon>

        <div class="divide-y divide-gray-200 divide-dotted border-t border-dotted border-gray-200">
            @foreach ($methods as $method)
                <div wire:key="shipping-method-{{ $method['slug'] }}" class="group flex items-center gap-4 px-4 py-4 hover:bg-gray-50/80 transition">
                    <button
                        type="button"
                        wire:click="openMethodModal('{{ $method['slug'] }}')"
                        class="flex min-w-0 flex-1 items-center gap-4 text-start"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-800">{{ $method['name'] }}</p>
                            <p class="mt-0.5 text-xs text-gray-500 line-clamp-2">{{ $method['description'] }}</p>
                        </div>

                        <div class="shrink-0 rounded-lg border border-gray-100 bg-white p-2">
                            <img
                                src="{{ $method['icon_url'] }}"
                                alt="{{ $method['name'] }}"
                                class="h-8 w-auto max-w-[72px] object-contain"
                            >
                        </div>
                    </button>

                    <button
                        type="button"
                        wire:click.stop="toggleMethodActive('{{ $method['slug'] }}')"
                        wire:loading.attr="disabled"
                        wire:target="toggleMethodActive('{{ $method['slug'] }}')"
                        class="shrink-0 rounded-lg p-1 hover:bg-gray-100 transition disabled:opacity-50"
                        aria-label="{{ $method['active'] ? 'تعطيل' : 'تفعيل' }} {{ $method['name'] }}"
                        role="switch"
                        aria-checked="{{ $method['active'] ? 'true' : 'false' }}"
                    >
                        <span
                            @class([
                                'relative inline-block h-6 w-11 rounded-full transition-colors duration-200',
                                'bg-gray-200' => ! $method['active'],
                                'bg-primary-500' => $method['active'],
                            ])
                        >
                            <span
                                @class([
                                    'absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition-all duration-200',
                                    'start-0.5' => ! $method['active'],
                                    'end-0.5 start-auto' => $method['active'],
                                ])
                            ></span>
                        </span>
                    </button>
                </div>
            @endforeach
        </div>

        @foreach ($methods as $method)
            <ui:modal
                :title="$method['name']"
                size="3xl"
                :name="'shipping-method-'.$method['slug']"
            >
                @livewire($method['modal_component'], ['slug' => $method['slug']], key('shipping-modal-'.$method['slug']))
            </ui:modal>
        @endforeach
    </ui:mainbox>

    <ui:mainbox title="خيارات الشحن المخصصة" subtitle="مناديب وشركات الشحن الخاصة المتعاقد معهم خارج المنصة.">
        <x-slot:icon>
            <ui:icon name="truck-delivery" class="!h-6 !w-6 text-gray-500" />
        </x-slot:icon>

        <x-slot:actions>
            <ui:button wire:click="openCustomForm" label="أضف خدمة شحن" icon="square-rounded-plus" />
        </x-slot:actions>

        <div class="divide-y divide-gray-200 divide-dotted border-t border-dotted border-gray-200">
            @if ($customOptions === [])
                <ui:empty subtitle="سيتم عرض خيارات الشحن المخصصة هنا بعد إضافتها.">
                    لا توجد خيارات شحن مخصصة.
                    <x-slot:icon>
                        <ui:icon name="truck-delivery" class="h-12 w-12 opacity-50" />
                    </x-slot:icon>
                </ui:empty>
            @else
                @foreach ($customOptions as $option)
                    <div wire:key="custom-shipping-{{ $option['id'] }}" class="group flex items-center gap-4 px-4 py-4 hover:bg-gray-50/80 transition">
                        <button
                            type="button"
                            wire:click="openCustomForm('{{ $option['id'] }}')"
                            class="flex min-w-0 flex-1 items-center gap-4 text-start"
                        >
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                                <ui:icon name="truck-delivery" class="!h-5 !w-5 text-emerald-500" />
                            </div>

                            <div class="min-w-0 flex-1">
                                <p @class([
                                    'text-sm font-semibold truncate',
                                    'text-gray-800' => $option['active'],
                                    'text-gray-400 line-through' => ! $option['active'],
                                ])>
                                    {{ $option['name'] }}
                                </p>
                                <p class="mt-0.5 text-xs text-gray-500 truncate">
                                    {{ money_format((int) round($option['price'] * 100)) }}
                                    · {{ $option['country_label'] }}
                                    @if ($option['cities_summary'] !== '')
                                        · {{ $option['cities_summary'] }}
                                    @endif
                                </p>
                            </div>
                        </button>

                        <button
                            type="button"
                            wire:click.stop="toggleCustomActive('{{ $option['id'] }}')"
                            wire:loading.attr="disabled"
                            wire:target="toggleCustomActive('{{ $option['id'] }}')"
                            class="shrink-0 rounded-lg p-1 hover:bg-gray-100 transition disabled:opacity-50"
                            aria-label="{{ $option['active'] ? 'تعطيل' : 'تفعيل' }} {{ $option['name'] }}"
                            role="switch"
                            aria-checked="{{ $option['active'] ? 'true' : 'false' }}"
                        >
                            <span
                                @class([
                                    'relative inline-block h-6 w-11 rounded-full transition-colors duration-200',
                                    'bg-gray-200' => ! $option['active'],
                                    'bg-primary-500' => $option['active'],
                                ])
                            >
                                <span
                                    @class([
                                        'absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition-all duration-200',
                                        'start-0.5' => ! $option['active'],
                                        'end-0.5 start-auto' => $option['active'],
                                    ])
                                ></span>
                            </span>
                        </button>
                    </div>
                @endforeach
            @endif
        </div>

        <ui:modal :title="$editingCustomId ? 'تعديل خدمة شحن' : 'أضف خدمة شحن'" size="3xl" name="custom-shipping-form">
            @if ($customFormMounted)
                <livewire:admin::settings.shipping-options.custom-shipping-form
                    :option-id="$editingCustomId"
                    :key="'custom-shipping-form-'.($editingCustomId ?? 'new')"
                />
            @endif
        </ui:modal>
    </ui:mainbox>
</div>

<?php

use App\Models\Setting;
use App\Support\ShippingMethodRegistry;
use App\Support\WorldLocationOptions;
use Livewire\Attributes\On;

new class extends \Livewire\Component
{
    /** @var array<int, array<string, mixed>> */
    public array $methods = [];

    /** @var list<array<string, mixed>> */
    public array $customOptions = [];

    public ?string $editingCustomId = null;

    public bool $customFormMounted = false;

    public function mount(): void
    {
        $this->loadMethods();
        $this->loadCustomOptions();
    }

    public function toggleMethodActive(string $slug): void
    {
        if (! config("shipping-methods.{$slug}")) {
            return;
        }

        $saved = Setting::shippingMethod($slug);
        $active = ! (bool) data_get($saved, 'active', false);
        $settings = collect($saved)->except('active')->all();

        Setting::saveShippingMethod($slug, $settings, $active);
        $this->loadMethods();

        $this->dispatch('notify', text: $active ? 'تم تفعيل وسيلة الشحن.' : 'تم تعطيل وسيلة الشحن.');
    }

    public function openMethodModal(string $slug): void
    {
        if (! config("shipping-methods.{$slug}")) {
            return;
        }

        $this->dispatch('openmodal', modal: 'shipping-method-'.$slug);
    }

    public function openCustomForm(?string $optionId = null): void
    {
        $this->editingCustomId = $optionId;
        $this->customFormMounted = true;
        $this->dispatch('openmodal', modal: 'custom-shipping-form');
    }

    public function toggleCustomActive(string $optionId): void
    {
        $items = Setting::customShippingOptions();
        $updated = false;

        foreach ($items as $index => $item) {
            if (($item['id'] ?? null) !== $optionId) {
                continue;
            }

            $items[$index]['active'] = ! (bool) ($item['active'] ?? true);
            $updated = true;
            break;
        }

        if (! $updated) {
            return;
        }

        Setting::saveCustomShippingOptions($items);
        $this->loadCustomOptions();

        $this->dispatch('notify', text: 'تم تحديث حالة خيار الشحن.');
    }

    #[On('shippingMethodSaved')]
    public function refreshMethods(): void
    {
        $this->loadMethods();
    }

    #[On('customShippingSaved')]
    public function refreshCustomOptions(): void
    {
        $this->editingCustomId = null;
        $this->customFormMounted = false;
        $this->loadCustomOptions();
    }

    protected function loadMethods(): void
    {
        $registry = app(ShippingMethodRegistry::class);

        $this->methods = $registry->all()
            ->map(function ($method) {
                $saved = Setting::shippingMethod($method->slug);

                return array_merge($method->toArray(), [
                    'active' => (bool) data_get($saved, 'active', false),
                    'modal_component' => $method->component('modal'),
                ]);
            })
            ->all();
    }

    protected function loadCustomOptions(): void
    {
        $locations = app(WorldLocationOptions::class);
        $savedOptions = collect(Setting::customShippingOptions());

        $cityIds = $savedOptions
            ->flatMap(function (array $option): array {
                if (($option['all_cities'] ?? false) || ($option['country'] ?? '') === WorldLocationOptions::ALL_COUNTRIES) {
                    return [];
                }

                return collect($option['city_ids'] ?? [])
                    ->reject(fn (mixed $id): bool => (string) $id === WorldLocationOptions::ALL_CITIES)
                    ->map(fn (mixed $id): int => (int) $id)
                    ->filter(fn (int $id): bool => $id > 0)
                    ->all();
            })
            ->unique()
            ->values()
            ->all();

        $cityLabelMap = $locations->cityLabelsByIds($cityIds);

        $this->customOptions = $savedOptions
            ->map(function (array $option) use ($locations, $cityLabelMap): array {
                $country = (string) ($option['country'] ?? WorldLocationOptions::ALL_COUNTRIES);
                $cityLabels = $this->resolveCityLabels($option, $cityLabelMap);

                return array_merge($option, [
                    'country_label' => $locations->countryLabel($country),
                    'cities_summary' => $cityLabels === [] ? '' : implode('، ', array_slice($cityLabels, 0, 2)).(count($cityLabels) > 2 ? ' +' .(count($cityLabels) - 2) : ''),
                ]);
            })
            ->sortBy('name')
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $option
     * @param  array<int, string>  $cityLabelMap
     * @return list<string>
     */
    protected function resolveCityLabels(array $option, array $cityLabelMap): array
    {
        $country = (string) ($option['country'] ?? WorldLocationOptions::ALL_COUNTRIES);

        if ($country === WorldLocationOptions::ALL_COUNTRIES || ($option['all_cities'] ?? false)) {
            return ['كل المدن'];
        }

        $cityIds = (array) ($option['city_ids'] ?? []);

        if ($cityIds === [WorldLocationOptions::ALL_CITIES] || in_array(WorldLocationOptions::ALL_CITIES, $cityIds, true)) {
            return ['كل المدن داخل الدولة'];
        }

        return collect($cityIds)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->map(fn (int $id): ?string => $cityLabelMap[$id] ?? null)
            ->filter()
            ->sort()
            ->values()
            ->all();
    }
}; ?>
