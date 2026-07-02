<?php

namespace App\Livewire\Concerns;

use App\Models\Block;

trait EditsBlock
{
    public int $blockId;

    abstract protected function blockType(): string;

    protected function block(): Block
    {
        return Block::queryForTenantRoots()
            ->type($this->blockType())
            ->findOrFail($this->blockId);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function saveData(array $data): void
    {
        $this->block()->update(['data' => $data]);

        $this->notifyStructureChanged();

        $this->dispatch('closemodal');
    }

    protected function notifyStructureChanged(?string $title = null): void
    {
        $this->dispatch('structure-blocks-changed', blockId: $this->blockId, title: $title);
    }
}
