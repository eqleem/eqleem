<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        تخصيص أزرار الإجراء الرئيسية في الصفحة.
    </p>

    <div class="space-y-2">
        <ui:toggle name="showBookingButton" label="زر حجز موعد" live />
        <ui:toggle name="showBranchesButton" label="زر زيارة الفرع" live />

        @if ($showBookingButton)
            <ui:separator />
            <p class="text-xs font-semibold text-gray-500">حجز الموعد</p>
            <ui:input name="bookingButtonLabel" label="نص الزر" placeholder="حجز موعد تركيب" />
            <ui:input name="bookingModalTitle" label="عنوان النافذة" placeholder="حجز موعد تركيب" />
            <ui:input name="bookingPhone" label="رقم الاتصال" placeholder="+966500000000" dir="ltr" />
            <ui:input name="bookingWhatsapp" label="رقم واتساب" placeholder="966500000000" dir="ltr" />
        @endif

        @if ($showBranchesButton)
            <ui:separator />
            <p class="text-xs font-semibold text-gray-500">زيارة الفرع</p>
            <ui:input name="branchesButtonLabel" label="نص الزر" placeholder="زيارة الفرع" />
            <ui:input name="branchesUrl" label="رابط الصفحة" placeholder="/branches" dir="ltr" />
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

    public bool $showBookingButton = true;

    public string $bookingButtonLabel = 'حجز موعد تركيب';

    public string $bookingModalTitle = 'حجز موعد تركيب';

    public string $bookingPhone = '+966500000000';

    public string $bookingWhatsapp = '966500000000';

    public bool $showBranchesButton = true;

    public string $branchesButtonLabel = 'زيارة الفرع';

    public string $branchesUrl = '';

    protected function blockType(): string
    {
        return 'cta';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->showBookingButton = (bool) ($data['show_booking_button'] ?? true);
        $this->bookingButtonLabel = (string) ($data['booking_button_label'] ?? 'حجز موعد تركيب');
        $this->bookingModalTitle = (string) ($data['booking_modal_title'] ?? 'حجز موعد تركيب');
        $this->bookingPhone = (string) ($data['booking_phone'] ?? '+966500000000');
        $this->bookingWhatsapp = (string) ($data['booking_whatsapp'] ?? '966500000000');
        $this->showBranchesButton = (bool) ($data['show_branches_button'] ?? true);
        $this->branchesButtonLabel = (string) ($data['branches_button_label'] ?? 'زيارة الفرع');
        $this->branchesUrl = (string) ($data['branches_url'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'bookingButtonLabel' => 'required_if:showBookingButton,true|nullable|string|max:100',
            'bookingModalTitle' => 'nullable|string|max:100',
            'bookingPhone' => 'nullable|string|max:30',
            'bookingWhatsapp' => 'nullable|string|max:30',
            'branchesButtonLabel' => 'required_if:showBranchesButton,true|nullable|string|max:100',
            'branchesUrl' => 'nullable|string|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->saveData([
            'show_booking_button' => $this->showBookingButton,
            'booking_button_label' => $this->bookingButtonLabel,
            'booking_modal_title' => $this->bookingModalTitle,
            'booking_phone' => $this->bookingPhone,
            'booking_whatsapp' => $this->bookingWhatsapp,
            'show_branches_button' => $this->showBranchesButton,
            'branches_button_label' => $this->branchesButtonLabel,
            'branches_url' => $this->branchesUrl,
        ]);
    }
}; ?>
