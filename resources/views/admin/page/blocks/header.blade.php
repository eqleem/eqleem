<ui:form wire:submit="save" class="!p-4 !rounded-none">
    <p class="text-xs text-gray-400 mb-4">
        تخصيص صورة الملف الشخصي والعنوان والروابط الاجتماعية في رأس الصفحة.
    </p>

    <div class="space-y-2">
        <ui:toggle name="showAvatar" label="عرض الصورة الشخصية" live />
        <ui:toggle name="showVerifiedBadge" label="شارة التوثيق" />
        <ui:toggle name="showLocation" label="عرض الموقع" live />
        <ui:toggle name="useTenantSlogan" label="استخدام شعار الموقع من الإعدادات" live />
        <ui:toggle name="showSocialLinks" label="عرض روابط التواصل" live />

        @if ($showAvatar)
            <ui:input name="avatarUrl" label="رابط الصورة" placeholder="https://..." dir="ltr" />
        @endif

        @if ($showLocation)
            <ui:input name="location" label="الموقع" placeholder="الرياض، السعودية" />
        @endif

        @unless ($useTenantSlogan)
            <ui:textarea name="slogan" label="الشعار / الوصف" placeholder="وصف قصير يظهر تحت العنوان" />
        @endunless

        @if ($showSocialLinks)
            <ui:separator />
            <p class="text-xs font-semibold text-gray-500">روابط التواصل</p>
            <ui:input name="socialTwitter" label="X (تويتر)" placeholder="https://x.com/..." dir="ltr" />
            <ui:input name="socialInstagram" label="إنستغرام" placeholder="https://instagram.com/..." dir="ltr" />
            <ui:input name="socialSnapchat" label="سناب شات" placeholder="https://snapchat.com/..." dir="ltr" />
            <ui:input name="socialYoutube" label="يوتيوب" placeholder="https://youtube.com/..." dir="ltr" />
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

    public bool $showAvatar = true;

    public string $avatarUrl = '';

    public bool $showVerifiedBadge = true;

    public bool $showLocation = true;

    public string $location = '';

    public bool $useTenantSlogan = true;

    public string $slogan = '';

    public bool $showSocialLinks = true;

    public string $socialTwitter = '';

    public string $socialInstagram = '';

    public string $socialSnapchat = '';

    public string $socialYoutube = '';

    protected function blockType(): string
    {
        return 'header';
    }

    public function mount(int $blockId): void
    {
        $this->blockId = $blockId;

        $data = $this->block()->data ?? [];

        $this->showAvatar = (bool) ($data['show_avatar'] ?? true);
        $this->avatarUrl = (string) ($data['avatar_url'] ?? '');
        $this->showVerifiedBadge = (bool) ($data['show_verified_badge'] ?? true);
        $this->showLocation = (bool) ($data['show_location'] ?? true);
        $this->location = (string) ($data['location'] ?? '');
        $this->useTenantSlogan = (bool) ($data['use_tenant_slogan'] ?? true);
        $this->slogan = (string) ($data['slogan'] ?? '');
        $this->showSocialLinks = (bool) ($data['show_social_links'] ?? true);
        $this->socialTwitter = (string) ($data['social_twitter'] ?? '');
        $this->socialInstagram = (string) ($data['social_instagram'] ?? '');
        $this->socialSnapchat = (string) ($data['social_snapchat'] ?? '');
        $this->socialYoutube = (string) ($data['social_youtube'] ?? '');
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'avatarUrl' => 'nullable|url|max:500',
            'location' => 'nullable|string|max:100',
            'slogan' => 'nullable|string|max:500',
            'socialTwitter' => 'nullable|url|max:500',
            'socialInstagram' => 'nullable|url|max:500',
            'socialSnapchat' => 'nullable|url|max:500',
            'socialYoutube' => 'nullable|url|max:500',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->saveData([
            'show_avatar' => $this->showAvatar,
            'avatar_url' => $this->avatarUrl,
            'show_verified_badge' => $this->showVerifiedBadge,
            'show_location' => $this->showLocation,
            'location' => $this->location,
            'use_tenant_slogan' => $this->useTenantSlogan,
            'slogan' => $this->slogan,
            'show_social_links' => $this->showSocialLinks,
            'social_twitter' => $this->socialTwitter,
            'social_instagram' => $this->socialInstagram,
            'social_snapchat' => $this->socialSnapchat,
            'social_youtube' => $this->socialYoutube,
        ]);
    }
}; ?>
