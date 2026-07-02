<?php

namespace App\Livewire\Concerns;

use App\Models\Block;
use Illuminate\Contracts\View\View;

trait RendersBlock
{
    abstract protected function blockType(): string;

    public function render(): View
    {
        $type = $this->blockType();
        $tenantId = currentTenantId();

        $block = Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->where('type', $type)
            ->first();

        $candidates = array_values(array_filter([
            $block?->variant,
            "tenant-theme::blocks.{$type}",
            "default-tenant-theme::blocks.{$type}",
        ]));

        return view()->first($candidates, ['block' => $block]);
    }
}
