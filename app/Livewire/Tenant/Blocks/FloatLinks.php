<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Support\FloatLinksBlock;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class FloatLinks extends Component
{
    use ResolvesTenantBlockView;

    protected function blockType(): string
    {
        return 'float-links';
    }

    public function render(): View
    {
        $block = $this->resolveSingletonBlock();

        return $this->renderTenantBlockView($block, FloatLinksBlock::viewData($block?->data ?? []));
    }
}
