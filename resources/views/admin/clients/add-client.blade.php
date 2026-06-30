<ui:form wire:submit="submit">
    <ui:input name="name" label="{{ __('Name') }}" placeholder="{{ __('Name') }}" />
    <ui:input name="phone" label="{{ __('Phone') }}" placeholder="{{ __('Phone') }}" type="number" dir="ltr" placeholder="123456789" />
    <ui:input name="email" label="{{ __('Email') }}" placeholder="{{ __('Email') }}" type="email" dir="ltr" placeholder="client@email.com" />

    {{-- <ui:fields.toggle name="active" label="{{__('Active')}}" />   --}}

    <x-slot:footer>
        <ui:button target="submit" label="{{ __('Save') }}" />
    </x-slot>
</ui:form>

<?php
use App\Models\Client;

new class extends Livewire\Component {
    public $event;
    public $name;
    public $phone;
    public $email;
    public $phonecode;

    protected function rules()
    {
        return [
            'name' => 'required|min:1|max:255',
            'phone' => 'required|max:14',
            'email' => 'nullable|email|max:255',
            // 'data.phonecode' => 'required_with:data.phone',
            // 'data.active'   => 'nullable',
        ];
    }

    function submit()
    {
        $this->validate();

        $tenantId = currentTenantId();

        if (! $tenantId) {
            $this->addError('name', __('No tenant selected.'));

            return;
        }

        $client = Client::withoutGlobalScope('tenantable')->firstOrCreate(
            [
                'phone' => $this->phone,
            ],
            [
                'name' => $this->name,
                'phone' => $this->phone,
                'email' => $this->email,
                'tenant_id' => $tenantId,
            ],
        );

        $client->tenants()->sync(
            [
                $tenantId => [
                    'active' => true,
                    'meta' => [
                        'name' => $this->name,
                        'email' => $this->email,
                        'phone' => $this->phone,
                        'phonecode' => $this->phonecode,
                    ],
                ],
            ],
            false,
        );

        $this->dispatch('updateClientList');
        $this->dispatch('closemodal', modal: 'add-client');

        if ($this->event) {
            $this->dispatch($this->event, client: $client);
        } else {
            return redirect(route('admin.clients.detail', ['id' => $client->id]));
        }
    }
};
?>
