<?php

namespace App\Livewire\Concerns;

use App\Models\Block;
use Illuminate\Contracts\View\View;

trait ResolvesTenantBlockView
{
    abstract protected function blockType(): string;

    protected function resolveSingletonBlock(): ?Block
    {
        $tenantId = currentTenantId();

        return Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->where('type', $this->blockType())
            ->first();
    }

    protected function resolvePageBlock(int $blockId): ?Block
    {
        $tenantId = currentTenantId();

        return Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereKey($blockId)
            ->where('type', $this->blockType())
            ->first();
    }

    /**
     * @return list<string>
     */
    protected function viewCandidates(?Block $block): array
    {
        $type = $block?->type ?? $this->blockType();

        return array_values(array_filter([
            $block?->variant,
            'tenant-theme::blocks.'.$type,
            'default-tenant-theme::blocks.'.$type,
        ]));
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
