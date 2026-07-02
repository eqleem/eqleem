<?php

namespace App\Support;

class TopNavBlock
{
    /**
     * @param  array<string, mixed>  $blockData
     * @return array{
     *     showShareButton: bool,
     *     showThemeToggle: bool,
     *     showLanguageSwitcher: bool,
     *     showBackButton: bool,
     *     showClientLogin: bool,
     *     clientLoginLabel: string,
     *     showBackButtonLink: bool,
     *     homeUrl: string
     * }
     */
    public static function viewData(array $blockData): array
    {
        $showBackButton = (bool) ($blockData['show_back_button'] ?? true);

        return [
            'showShareButton' => (bool) ($blockData['show_share_button'] ?? true),
            'showThemeToggle' => (bool) ($blockData['show_theme_toggle'] ?? true),
            'showLanguageSwitcher' => (bool) ($blockData['show_language_switcher'] ?? true),
            'showBackButton' => $showBackButton,
            'showClientLogin' => (bool) ($blockData['show_client_login'] ?? true),
            'clientLoginLabel' => (string) ($blockData['client_login_label'] ?? 'دخول العملاء'),
            'showBackButtonLink' => $showBackButton && ! request()->routeIs('tenant.home'),
            'homeUrl' => route('tenant.home'),
        ];
    }
}
