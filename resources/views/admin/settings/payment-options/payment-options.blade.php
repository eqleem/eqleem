<ui:mainbox title="وسائل الدفع" subtitle="قم بتفعيل وتخصيص وسائل الدفع المناسبة لجمهورك.">
    <x-slot:icon>
        <img src="{{ asset('assets/icons/business/017-atm-card.svg') }}" alt="" class="h-6 w-6">
    </x-slot:icon>

    <div class="divide-y divide-gray-200 divide-dotted border-t border-dotted border-gray-200">
        @foreach ($methods as $method)
            <div
                wire:key="payment-method-{{ $method['slug'] }}"
                @class([
                    'group flex items-center gap-4 px-4 py-4 transition',
                    'hover:bg-gray-50/80' => $method['available'],
                    'opacity-40' => ! $method['available'],
                ])
            >
                <button
                    type="button"
                    @if ($method['available'])
                        wire:click="openModal('{{ $method['slug'] }}')"
                    @endif
                    @disabled(! $method['available'])
                    class="flex min-w-0 flex-1 items-center gap-4 text-start disabled:cursor-not-allowed"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-semibold text-gray-800">{{ $method['name'] }}</p>
                            @unless ($method['available'])
                                <span class="rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium tracking-wide text-gray-500">
                                    قريباً
                                </span>
                            @endunless
                        </div>
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
                    @if ($method['available'])
                        wire:click.stop="toggleActive('{{ $method['slug'] }}')"
                    @endif
                    wire:loading.attr="disabled"
                    wire:target="toggleActive('{{ $method['slug'] }}')"
                    @disabled(! $method['available'])
                    class="shrink-0 rounded-lg p-1 hover:bg-gray-100 transition disabled:cursor-not-allowed disabled:opacity-50"
                    aria-label="{{ $method['active'] ? 'تعطيل' : 'تفعيل' }} {{ $method['name'] }}"
                    role="switch"
                    aria-checked="{{ ($method['available'] && $method['active']) ? 'true' : 'false' }}"
                >
                    <span
                        @class([
                            'relative inline-block h-6 w-11 rounded-full transition-colors duration-200',
                            'bg-gray-200' => ! ($method['available'] && $method['active']),
                            'bg-primary-500' => $method['available'] && $method['active'],
                        ])
                    >
                        <span
                            @class([
                                'absolute top-0.5 size-5 rounded-full bg-white shadow-sm transition-all duration-200',
                                'start-0.5' => ! ($method['available'] && $method['active']),
                                'end-0.5 start-auto' => $method['available'] && $method['active'],
                            ])
                        ></span>
                    </span>
                </button>
            </div>
        @endforeach
    </div>

    @foreach ($methods as $method)
        @continue(! $method['available'])
        <ui:modal
            :title="$method['name']"
            size="3xl"
            :name="'payment-method-'.$method['slug']"
        >
            @livewire($method['modal_component'], ['slug' => $method['slug']], key('payment-modal-'.$method['slug']))
        </ui:modal>
    @endforeach
</ui:mainbox>

<?php

use App\Models\Setting;
use App\Support\PaymentMethodRegistry;

new class extends \Livewire\Component
{
    /** @var array<int, array<string, mixed>> */
    public array $methods = [];

    public function mount(): void
    {
        $this->loadMethods();
    }

    public function toggleActive(string $slug): void
    {
        $method = app(PaymentMethodRegistry::class)->find($slug);

        if (! $method || ! $method->available) {
            return;
        }

        $saved = Setting::paymentMethod($slug);
        $active = ! (bool) data_get($saved, 'active', false);
        $settings = collect($saved)->except('active')->all();

        Setting::savePaymentMethod($slug, $settings, $active);
        $this->loadMethods();

        $this->dispatch('notify', text: $active ? 'تم تفعيل وسيلة الدفع.' : 'تم تعطيل وسيلة الدفع.');
    }

    public function openModal(string $slug): void
    {
        $method = app(PaymentMethodRegistry::class)->find($slug);

        if (! $method || ! $method->available) {
            return;
        }

        $this->dispatch('openmodal', modal: 'payment-method-'.$slug);
    }

    #[\Livewire\Attributes\On('paymentMethodSaved')]
    public function refreshMethods(): void
    {
        $this->loadMethods();
    }

    protected function loadMethods(): void
    {
        $registry = app(PaymentMethodRegistry::class);

        $this->methods = $registry->all()
            ->map(function ($method) {
                $saved = Setting::paymentMethod($method->slug);

                return array_merge($method->toArray(), [
                    'active' => (bool) data_get($saved, 'active', false),
                    'modal_component' => $method->component('modal'),
                ]);
            })
            ->all();
    }
}; ?>
