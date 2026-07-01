<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        تحكم في العناصر الظاهرة في الشريط العلوي للصفحة.
    </p>

    <div class="space-y-2">
        <p class="text-xs font-semibold text-gray-500 pt-1">الجانب الأيسر</p>

        <ui:toggle name="showShare" label="زر المشاركة" />
        <ui:toggle name="showThemeToggle" label="زر الوضع الليلي" />
        <ui:toggle name="showLanguageSwitcher" label="مبدّل اللغة" live />

        @if ($showLanguageSwitcher)
            <ui:input name="languageLabel" label="نص اللغة" placeholder="English" />
        @endif

        <ui:separator />

        <p class="text-xs font-semibold text-gray-500">الوسط</p>

        <ui:toggle name="showBackButton" label="زر الرجوع للرئيسية" />

        <ui:separator />

        <p class="text-xs font-semibold text-gray-500">الجانب الأيمن</p>

        <ui:toggle name="showCustomerLogin" label="زر دخول العملاء" live />

        @if ($showCustomerLogin)
            <ui:input name="customerLoginLabel" label="نص زر الدخول" placeholder="دخول العملاء" />
        @endif

        <ui:separator />

        <p class="text-xs font-semibold text-gray-500">المشاركة</p>

        <ui:input name="shareText" label="نص المشاركة الافتراضي" placeholder="شاهد هذه الصفحة" />
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

    public bool $showShare = true;

    public bool $showThemeToggle = true;

    public bool $showLanguageSwitcher = true;

    public string $languageLabel = 'English';

    public bool $showBackButton = true;

    public bool $showCustomerLogin = true;

    public string $customerLoginLabel = 'دخول العملاء';

    public string $shareText = 'شاهد هذه الصفحة';

    protected function blockType(): string
    {
        return 'top-nav';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->showShare = (bool) ($data['show_share'] ?? true);
        $this->showThemeToggle = (bool) ($data['show_theme_toggle'] ?? true);
        $this->showLanguageSwitcher = (bool) ($data['show_language_switcher'] ?? true);
        $this->languageLabel = (string) ($data['language_label'] ?? 'English');
        $this->showBackButton = (bool) ($data['show_back_button'] ?? true);
        $this->showCustomerLogin = (bool) ($data['show_customer_login'] ?? true);
        $this->customerLoginLabel = (string) ($data['customer_login_label'] ?? 'دخول العملاء');
        $this->shareText = (string) ($data['share_text'] ?? 'شاهد هذه الصفحة');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'languageLabel' => 'required_if:showLanguageSwitcher,true|nullable|string|max:50',
            'customerLoginLabel' => 'required_if:showCustomerLogin,true|nullable|string|max:100',
            'shareText' => 'nullable|string|max:255',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->saveData([
            'show_share' => $this->showShare,
            'show_theme_toggle' => $this->showThemeToggle,
            'show_language_switcher' => $this->showLanguageSwitcher,
            'language_label' => $this->languageLabel,
            'show_back_button' => $this->showBackButton,
            'show_customer_login' => $this->showCustomerLogin,
            'customer_login_label' => $this->customerLoginLabel,
            'share_text' => $this->shareText,
        ]);
    }
}; ?>
