<?php

namespace App\Livewire\Concerns;

use App\Models\Block;

trait EditsBlock
{
    public int $blockId;

    abstract protected function blockType(): string;

    protected function block(): Block
    {
        $tenantId = currentTenantId();

        return Block::query()
            ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
            ->whereNull('parent_id')
            ->where('type', $this->blockType())
            ->findOrFail($this->blockId);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function saveData(array $data): void
    {
        $this->block()->update(['data' => $data]);

        $this->dispatch('closemodal');
    }
}
