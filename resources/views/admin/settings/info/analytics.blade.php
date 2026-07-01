<div>

    <ui:mainbox title="ربط الإحصائيات" subtitle="ألحق أكواد التتبع لبدء قياس زيارات وإحصائيات صفحتك.">
        <x-slot:icon>
            <img src="{{ asset('assets/icons/business/030-growth-chart.svg') }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:form wire:submit="submit" id="analytics-form">

            <div class="flex flex-col gap-3">
                @foreach (config('analytics.providers') as $key => $provider)
                    <div wire:key="analytics-{{ $key }}" class="border border-gray-100 rounded-xl p-4 bg-gray-50/50">
                        <div class="flex items-center justify-between gap-4 mb-3">
                            <h3 class="text-sm font-semibold text-gray-700">{{ $provider['name'] }}</h3>
                            <ui:toggle
                                name="integrations.{{ $key }}.active"
                                label="تفعيل"
                                live
                                width="w-auto"
                                labelWidth="w-auto"
                            />
                        </div>
                        <ui:input
                            name="integrations.{{ $key }}.identifier"
                            :label="$provider['label']"
                            :placeholder="$provider['placeholder']"
                            dir="ltr"
                            block
                        />
                    </div>
                @endforeach

                <div class="border border-dashed border-gray-200 rounded-xl p-4 bg-gray-50/30 flex flex-col justify-center items-center text-center min-h-[80px]">
                    <p class="text-sm font-semibold text-gray-500">قريباً</p>
                    <p class="text-xs text-gray-400 mt-1">سيتم إضافة المزيد من التكاملات لاحقاً.</p>
                </div>
            </div>

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot>
        </ui:form>

    </ui:mainbox>
</div>

<?php

use App\Models\Setting;

new class extends \Livewire\Component {
    /** @var array<string, array{identifier: string, active: bool}> */
    public array $integrations = [];

    public function rules(): array
    {
        $rules = [];

        foreach (array_keys(config('analytics.providers')) as $provider) {
            $rules["integrations.{$provider}.identifier"] = [
                'nullable',
                'string',
                'max:100',
                "required_if:integrations.{$provider}.active,true",
            ];
            $rules["integrations.{$provider}.active"] = ['boolean'];
        }

        return $rules;
    }

    public function mount(): void
    {
        $saved = Setting::forGroup('analytics');

        foreach (array_keys(config('analytics.providers')) as $provider) {
            $row = $saved->get($provider);

            $this->integrations[$provider] = [
                'identifier' => data_get($row, 'settings.identifier', ''),
                'active' => (bool) data_get($row, 'active', false),
            ];
        }
    }

    public function submit(): void
    {
        $this->validate();

        foreach ($this->integrations as $provider => $data) {
            $identifier = trim((string) data_get($data, 'identifier', ''));
            $active = (bool) data_get($data, 'active', false);
            $slug = Setting::groupSlug('analytics', $provider);

            if ($identifier === '') {
                Setting::query()
                    ->where('tenant_id', currentTenantId())
                    ->where('slug', $slug)
                    ->delete();

                continue;
            }

            Setting::saveForSlug($slug, ['identifier' => $identifier], $active);
        }

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }
};
?>
