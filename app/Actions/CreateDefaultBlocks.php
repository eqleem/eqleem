<?php

namespace App\Actions;

use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDefaultBlocks
{
    use AsAction;

    public function handle(Tenant $tenant, ?BlockTypeRegistry $blockTypes = null): void
    {
        $blockTypes ??= app(BlockTypeRegistry::class);

        foreach ($blockTypes->defaults() as $blockType) {
            Block::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'type' => $blockType->slug,
                ],
                [
                    'component' => $blockType->component,
                    'title' => $blockType->name,
                    'slug' => $blockType->slug,
                    'sort_order' => $blockType->order,
                    'is_default' => true,
                    'status' => 'draft',
                    'active' => true,
                ],
            );
        }
    }
}
