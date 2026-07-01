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
        <ui:toggle name="showScrollTop" label="زر العودة للأعلى" />

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

new class extends \Livewire\Component
{
    use EditsBlock;

    public string $position = 'bottom-end';

    public bool $showWhatsapp = true;

    public string $whatsappNumber = '';

    public bool $showPhone = false;

    public string $phoneNumber = '';

    public bool $showScrollTop = true;

    protected function blockType(): string
    {
        return 'float-links';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->position = (string) ($data['position'] ?? 'bottom-end');
        $this->showWhatsapp = (bool) ($data['show_whatsapp'] ?? true);
        $this->whatsappNumber = (string) ($data['whatsapp_number'] ?? '');
        $this->showPhone = (bool) ($data['show_phone'] ?? false);
        $this->phoneNumber = (string) ($data['phone_number'] ?? '');
        $this->showScrollTop = (bool) ($data['show_scroll_top'] ?? true);
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

        $this->saveData([
            'position' => $this->position,
            'show_whatsapp' => $this->showWhatsapp,
            'whatsapp_number' => $this->whatsappNumber,
            'show_phone' => $this->showPhone,
            'phone_number' => $this->phoneNumber,
            'show_scroll_top' => $this->showScrollTop,
        ]);
    }
}; ?>
