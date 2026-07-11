<?php

namespace App\API\Page\Concerns;

use App\Models\Block;
use App\Support\BlockTypeRegistry;
use App\Support\CtaLink;
use Illuminate\Support\Collection;

trait MapsPageBlocks
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function mapBlocks(Collection $blocks, BlockTypeRegistry $blockTypes): Collection
    {
        $typeIcons = $blockTypes->iconPaths();
        $editors = $blockTypes->editors();

        return $blocks->map(function (Block $block) use ($typeIcons, $editors): array {
            $icon = $typeIcons[$block->type] ?? 'assets/icons/tabler/Blockquote.svg';
            $contentManageUrl = $block->type === 'block-link' && is_array($block->data)
                ? $this->dashboardManageUrlFromData($block->data)
                : null;

            return [
                'id' => $block->id,
                'uuid' => $block->uuid,
                'title' => $block->title,
                'type' => $block->type,
                'sort_order' => $block->sort_order,
                'is_default' => (bool) $block->is_default,
                'editable' => filled($editors[$block->type] ?? null),
                'active' => (bool) $block->active,
                'icon' => $icon,
                'icon_url' => asset($icon),
                'content_manage_url' => $contentManageUrl,
            ];
        });
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    protected function blocksForTypes(Collection $blocks, array $types): Collection
    {
        return collect($types)
            ->map(fn (string $type): ?array => $blocks->firstWhere('type', $type))
            ->filter()
            ->values();
    }

    /**
     * @return array{top: Collection<int, array<string, mixed>>, user: Collection<int, array<string, mixed>>, bottom: Collection<int, array<string, mixed>>}
     */
    protected function groupedBlocks(BlockTypeRegistry $blockTypes): array
    {
        $blocks = Block::queryForTenantRoots()
            ->orderBy('sort_order')
            ->get(['id', 'uuid', 'title', 'type', 'sort_order', 'is_default', 'active', 'data']);

        $mapped = $this->mapBlocks($blocks, $blockTypes);
        $system = $mapped->where('is_default', true)->values();

        return [
            'top' => $this->blocksForTypes($system, ['top-nav', 'header', 'cta']),
            'user' => $mapped->where('is_default', false)->values(),
            'bottom' => $this->blocksForTypes($system, ['footer', 'float-links']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function dashboardManageUrlFromData(array $data): ?string
    {
        $params = CtaLink::adminManageParamsFromData($data);

        if ($params === null) {
            return null;
        }

        $contentType = str_replace('content-', '', (string) ($params['tab'] ?? ''));

        if ($contentType === '') {
            return null;
        }

        if (filled($params['item'] ?? null)) {
            return '/dashboard/manage/'.$contentType.'/detail/'.$params['item'];
        }

        return '/dashboard/manage/'.$contentType;
    }

    protected function findTenantRootBlock(int $id): ?Block
    {
        return Block::queryForTenantRoots()->find($id);
    }

    protected function findUserBlock(int $id): ?Block
    {
        return Block::queryForTenantRoots()->userBlocks()->find($id);
    }
}
