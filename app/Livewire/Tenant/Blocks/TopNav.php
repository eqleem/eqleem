<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Support\TopNavBlock;
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
        return $this->renderSingletonBlockView(
            fn ($block) => TopNavBlock::viewData($block?->data ?? [])
        );
    }
}
