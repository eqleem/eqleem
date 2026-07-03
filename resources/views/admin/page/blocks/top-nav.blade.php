<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        تحكم في العناصر الظاهرة في الشريط العلوي للصفحة.
    </p>

    <div class="space-y-2">
 
        <ui:toggle name="showShareButton" label="زر المشاركة" />
        <ui:toggle name="showThemeToggle" label="زر الوضع الليلي" />
        <ui:toggle name="showLanguageSwitcher" label="مبدّل اللغة" live />
 
        <ui:toggle name="showBackButton" label="زر الرجوع للرئيسية" />

        <ui:toggle name="showPagesMenu" label="قائمة الصفحات" />
 
        <div class="flex items-center gap-2">
            <ui:toggle name="showClientLogin" label="زر دخول العملاء" live />

            @if ($showClientLogin)
                <ui:input name="clientLoginLabel"  placeholder="دخول العملاء" />
            @endif
        </div>
       
  
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

    public bool $showShareButton = true;

    public bool $showThemeToggle = true;

    public bool $showLanguageSwitcher = true;
 
    public bool $showBackButton = true;

    public bool $showPagesMenu = true;

    public bool $showClientLogin = true;

    public string $clientLoginLabel = 'دخول العملاء';
 
    protected function blockType(): string
    {
        return 'top-nav';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->showShareButton = (bool) ($data['show_share_button'] ?? true);
        $this->showThemeToggle = (bool) ($data['show_theme_toggle'] ?? true);
        $this->showLanguageSwitcher = (bool) ($data['show_language_switcher'] ?? true);
        $this->showBackButton = (bool) ($data['show_back_button'] ?? true);
        $this->showPagesMenu = (bool) ($data['show_pages_menu'] ?? true);
        $this->showClientLogin = (bool) ($data['show_client_login'] ?? true);
        $this->clientLoginLabel = (string) ($data['client_login_label'] ?? 'دخول العملاء');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'clientLoginLabel' => 'required_if:showClientLogin,true|nullable|string|max:100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->saveData([
            'show_share_button' => $this->showShareButton,
            'show_theme_toggle' => $this->showThemeToggle,
            'show_language_switcher' => $this->showLanguageSwitcher,
            'show_back_button' => $this->showBackButton,
            'show_pages_menu' => $this->showPagesMenu,
            'show_client_login' => $this->showClientLogin,
            'client_login_label' => $this->clientLoginLabel,
        ]);
    }
}; ?>
