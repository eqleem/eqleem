<?php

namespace App\Livewire\Tenant\Blocks;

use App\Livewire\Concerns\ResolvesTenantBlockView;
use App\Support\BlockLinkCard;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BlockLink extends Component
{
    use ResolvesTenantBlockView;

    public int $blockId;

    protected function blockType(): string
    {
        return 'block-link';
    }

    /**
     * @return list<string>
     */
    protected function pageBlockTypes(): array
    {
        return ['block-link', 'link'];
    }

    public function render(): View
    {
        $block = $this->resolvePageBlock($this->blockId);
        $card = BlockLinkCard::fromBlock($block);

        if (! $card) {
            return $this->renderEmptyBlockView();
        }

        return $this->renderTenantBlockView($block, [
            'card' => $card,
        ]);
    }
}
