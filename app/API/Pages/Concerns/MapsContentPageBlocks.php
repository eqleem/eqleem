<?php

namespace App\API\Pages\Concerns;

use App\Models\Block;
use App\Support\BlockTypeRegistry;
use Illuminate\Support\Collection;

trait MapsContentPageBlocks
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapContentPageBlocks(Collection $blocks, BlockTypeRegistry $blockTypes): Collection
    {
        $typeIcons = $blockTypes->iconPaths();
        $editors = $blockTypes->editors();

        return $blocks->map(function (Block $block) use ($typeIcons, $editors): array {
            $icon = $typeIcons[$block->type] ?? 'assets/icons/tabler/Blockquote.svg';

            return [
                'id' => $block->id,
                'uuid' => $block->uuid,
                'title' => $block->title,
                'type' => $block->type,
                'sort_order' => $block->sort_order,
                'is_default' => false,
                'editable' => filled($editors[$block->type] ?? null),
                'active' => (bool) $block->active,
                'icon' => $icon,
                'icon_url' => asset($icon),
                'brand_mark' => null,
                'content_manage_url' => null,
                'content_manage_label' => null,
            ];
        });
    }

    protected function findContentUserBlock(int $contentId, int $blockId): ?Block
    {
        return Block::queryForContent($contentId)
            ->userBlocks()
            ->find($blockId);
    }
}
