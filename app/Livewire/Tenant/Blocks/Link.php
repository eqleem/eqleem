<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Support\SimpleLinkBlock;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Link extends Component
{
    use ResolvesTenantBlockView;

    public int $blockId;

    protected function blockType(): string
    {
        return 'link';
    }

    public function render(): View
    {
        $block = $this->resolvePageBlock($this->blockId);
        $card = SimpleLinkBlock::card($block);

        if (! $card) {
            return $this->renderEmptyBlockView();
        }

        return $this->renderTenantBlockView($block, [
            'card' => $card,
        ]);
    }
}
