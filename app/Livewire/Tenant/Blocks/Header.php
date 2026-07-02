<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Support\HeaderBlock;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Header extends Component
{
    use ResolvesTenantBlockView;

    protected function blockType(): string
    {
        return 'header';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();

        return $this->renderTenantBlockView($block, HeaderBlock::viewData($block));
    }
}
