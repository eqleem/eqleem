<div>

    <ui:mainbox title="الدومين المجاني" subtitle="تعديل الدومين الفرعي للمجاني من إقليم في حال كنت لا تملك دومين مخصص بعد.">
        <x-slot:icon>
            <img src="{{ asset('assets/icons/business/015-cloud-network.svg') }}" class="w-7 h-7" alt="">
        </x-slot:icon>

        <ui:form wire:submit="submit" id="domain-form">

            <ui:input
                name="handle"
                label="رابط الصفحة"
                placeholder="admin"
                prefix="https://"
                dir="ltr"
                :suffix="'.'.config('app.domain')"
            />

            <x-slot:footer>
                <ui:button target="submit" label="{{ __('Save') }}" />
            </x-slot>
        </ui:form>

    </ui:mainbox>
</div>

<?php

use Illuminate\Validation\Rule;

new class extends \Livewire\Component {
    public $tenant;

    public $handle;

    public function rules(): array
    {
        return [
            'handle' => [
                'required',
                'min:2',
                'max:100',
                'alpha_dash:ascii',
                Rule::unique('tenants', 'handle')->ignore($this->tenant->id),
            ],
        ];
    }

    public function mount(): void
    {
        $this->tenant = currentTenant();
        $this->handle = $this->tenant->handle;
    }

    public function submit(): void
    {
        $this->validate();

        $this->tenant->handle = $this->handle;
        $this->tenant->save();

        setCurrentTenant($this->tenant->fresh());

        $this->dispatch('notify', text: __('Settings updated successfully.'));
    }
};
?>
