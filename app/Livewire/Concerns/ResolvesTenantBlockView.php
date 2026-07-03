<?php

namespace App\Livewire\Concerns;

use App\Models\Block;
use Illuminate\Contracts\View\View;

trait ResolvesTenantBlockView
{
    abstract protected function blockType(): string;

    /**
     * @return list<string>
     */
    protected function pageBlockTypes(): array
    {
        return [$this->blockType()];
    }

    protected function resolveSingletonBlock(): ?Block
    {
        return Block::findSingleton($this->blockType());
    }

    protected function resolvePageBlock(int $blockId): ?Block
    {
        return Block::findPageBlock($blockId, $this->pageBlockTypes());
    }

    /**
     * @param  callable(?Block): array<string, mixed>  $viewDataResolver
     */
    protected function renderSingletonBlockView(callable $viewDataResolver): View
    {
        $block = $this->resolveSingletonBlock();

        return $this->renderTenantBlockView($block, $viewDataResolver($block));
    }

    /**
     * @return list<string>
     */
    protected function viewCandidates(?Block $block): array
    {
        $type = $block?->type ?? $this->blockType();

        $candidates = array_values(array_filter([

            'tenant-theme::blocks.'.$type.'.'.$block?->variant,
            'tenant-theme::blocks.'.$type.'.'.$type,
            'tenant-theme::blocks.'.$type,
            'default-tenant-theme::blocks.'.$type,
        ]));

        if ($type === 'link') {
            $candidates[] = 'tenant-theme::blocks.block-link';
            $candidates[] = 'default-tenant-theme::blocks.block-link';
        }

        return array_values(array_unique($candidates));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function renderTenantBlockView(?Block $block, array $data): View
    {
        return view()->first($this->viewCandidates($block), $data);
    }

    protected function renderEmptyBlockView(): View
    {
        return view('livewire.tenant.blocks.empty');
    }
}
