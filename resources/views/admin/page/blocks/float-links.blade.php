<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        تخصيص الأزرار الطافية الثابتة على الصفحة.
    </p>

    <div class="space-y-2">
        <ui:select
            name="position"
            label="موضع الأزرار"
            :options="[
                'bottom-start' => 'أسفل اليسار',
                'bottom-end' => 'أسفل اليمين',
            ]"
        />

        <ui:separator />

        <ui:toggle name="showWhatsapp" label="زر واتساب" live />
        <ui:toggle name="showPhone" label="زر الاتصال" live />

        @if ($showWhatsapp)
            <ui:input name="whatsappNumber" label="رقم واتساب" placeholder="966500000000" dir="ltr" />
        @endif

        @if ($showPhone)
            <ui:input name="phoneNumber" label="رقم الاتصال" placeholder="+966500000000" dir="ltr" />
        @endif
    </div>

    <x-slot:footer>
        <ui:button type="submit" target="save" label="{{ __('Save') }}" />
    </x-slot:footer>
</ui:form>

<?php

use App\Livewire\Concerns\EditsBlock;
use App\Services\TenantProfileService;

new class extends \Livewire\Component
{
    use EditsBlock;

    public string $position = 'bottom-end';

    public bool $showWhatsapp = true;

    public string $whatsappNumber = '';

    public bool $showPhone = false;

    public string $phoneNumber = '';

    protected function blockType(): string
    {
        return 'float-links';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];
        $tenant = currentTenant();
        $contact = $tenant
            ? app(TenantProfileService::class)->contact($tenant)
            : ['phone' => '', 'whatsapp' => ''];

        $this->position = (string) ($data['position'] ?? 'bottom-end');
        $this->showWhatsapp = (bool) ($data['show_whatsapp'] ?? true);
        $this->whatsappNumber = (string) ($contact['whatsapp'] ?? '');
        $this->showPhone = (bool) ($data['show_phone'] ?? false);
        $this->phoneNumber = (string) ($contact['phone'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'position' => 'required|in:bottom-start,bottom-end',
            'whatsappNumber' => 'nullable|string|max:30',
            'phoneNumber' => 'nullable|string|max:30',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $tenant = currentTenant();

        if ($tenant) {
            app(TenantProfileService::class)->saveContact($tenant, [
                'whatsapp' => $this->whatsappNumber,
                'phone' => $this->phoneNumber,
            ]);
        }

        $this->saveData([
            'position' => $this->position,
            'show_whatsapp' => $this->showWhatsapp,
            'show_phone' => $this->showPhone,
        ]);
    }
}; ?>
