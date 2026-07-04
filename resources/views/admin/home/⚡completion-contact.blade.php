<div>
    <ui:form wire:submit="save" class="!p-4">
        <div class="space-y-2">
            <ui:input name="phone" label="رقم الجوال" placeholder="0501234567" dir="ltr" />
            <ui:input name="email" label="البريد الإلكتروني" placeholder="hello@example.com" type="email" dir="ltr" />
            <ui:input name="country" label="الدولة" placeholder="السعودية" />
            <ui:input name="city" label="المدينة" placeholder="الرياض" />
        </div>

        <x-slot:footer>
            <ui:button type="submit" target="save" label="{{ __('Save') }}" />
        </x-slot:footer>
    </ui:form>
</div>

<?php

use App\Services\TenantProfileService;

new class extends Livewire\Component
{
    public int $headerBlockId;

    public string $phone = '';

    public string $email = '';

    public string $country = '';

    public string $city = '';

    public function mount(int $headerBlockId): void
    {
        $this->headerBlockId = $headerBlockId;

        $tenant = currentTenant();

        if (! $tenant) {
            return;
        }

        $contact = app(TenantProfileService::class)->contact($tenant);
        $this->phone = $contact['phone'];
        $this->email = $contact['email'];
        $this->country = $contact['country'];
        $this->city = $contact['city'];
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $tenant = currentTenant();

        if ($tenant) {
            app(TenantProfileService::class)->saveContact($tenant, [
                'phone' => $this->phone,
                'email' => $this->email,
                'country' => $this->country,
                'city' => $this->city,
            ]);
        }

        $this->dispatch('page-completion-updated');
        $this->dispatch('closemodal', modal: 'home-step-contact');
        $this->dispatch('notify', text: __('Settings updated successfully.'), type: 'success');
    }
}; ?>
