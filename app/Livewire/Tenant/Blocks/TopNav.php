<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TopNav extends Component
{
    use ResolvesTenantBlockView;

    protected function blockType(): string
    {
        return 'top-nav';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();
        $blockData = $block?->data ?? [];
        $showBackButton = (bool) ($blockData['show_back_button'] ?? true);
        $showPagesMenu = (bool) ($blockData['show_pages_menu'] ?? true);

        return $this->renderTenantBlockView($block, [
            'showShareButton' => (bool) ($blockData['show_share_button'] ?? true),
            'showThemeToggle' => (bool) ($blockData['show_theme_toggle'] ?? true),
            'showLanguageSwitcher' => (bool) ($blockData['show_language_switcher'] ?? true),
            'showBackButton' => $showBackButton,
            'showClientLogin' => (bool) ($blockData['show_client_login'] ?? true),
            'showPagesMenu' => $showPagesMenu,
            'clientLoginLabel' => (string) ($blockData['client_login_label'] ?? 'دخول العملاء'),
            'showBackButtonLink' => $showBackButton && ! request()->routeIs('tenant.home'),
            'homeUrl' => route('tenant.home'),
        ]);
    }

    public static function pageMenuIcon(?string $template): string
    {
        return match ($template) {
            'contact' => 'hugeicons:call',
            'faq' => 'hugeicons:help-circle',
            'about' => 'hugeicons:user-group',
            'features' => 'hugeicons:magic-wand-02',
            'pricing' => 'hugeicons:credit-card-change',
            default => 'hugeicons:file-01',
        };
    }
}
