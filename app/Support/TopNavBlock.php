<?php

namespace App\Support;

use App\Models\Content;
use Illuminate\Database\Eloquent\Collection;

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
     *     showPagesMenu: bool,
     *     clientLoginLabel: string,
     *     showBackButtonLink: bool,
     *     homeUrl: string,
     *     publishedPages: Collection<int, Content>
     * }
     */
    public static function viewData(array $blockData): array
    {
        $showBackButton = (bool) ($blockData['show_back_button'] ?? true);
        $showPagesMenu = (bool) ($blockData['show_pages_menu'] ?? true);

        return [
            'showShareButton' => (bool) ($blockData['show_share_button'] ?? true),
            'showThemeToggle' => (bool) ($blockData['show_theme_toggle'] ?? true),
            'showLanguageSwitcher' => (bool) ($blockData['show_language_switcher'] ?? true),
            'showBackButton' => $showBackButton,
            'showClientLogin' => (bool) ($blockData['show_client_login'] ?? true),
            'showPagesMenu' => $showPagesMenu,
            'clientLoginLabel' => (string) ($blockData['client_login_label'] ?? 'دخول العملاء'),
            'showBackButtonLink' => $showBackButton && ! request()->routeIs('tenant.home'),
            'homeUrl' => route('tenant.home'),
            'publishedPages' => $showPagesMenu ? static::publishedPages() : new Collection,
        ];
    }

    /**
     * @return Collection<int, Content>
     */
    protected static function publishedPages(): Collection
    {
        return Content::query()
            ->type(contentTypeModel('pages'))
            ->published()
            ->where('active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);
    }
}
