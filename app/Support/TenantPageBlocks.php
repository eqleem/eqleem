<?php

namespace App\Support;

use App\Models\Block;
use App\Models\Content;
use Illuminate\Database\Eloquent\Collection;

/**
 * Request-scoped loader for tenant front-page blocks.
 *
 * Shell blocks (header/cta/nav/footer/float-links) load separately from home
 * page blocks so the home links section can be lazy-loaded without paying
 * that query cost on the initial paint.
 */
class TenantPageBlocks
{
    /**
     * @var list<string>
     */
    public const SHELL_TYPES = ['top-nav', 'header', 'cta', 'footer', 'float-links'];

    /**
     * @var list<string>
     */
    public const LINK_CONTENT_TYPES = ['cta-link', 'footer-link'];

    /** @var Collection<int, Block>|null */
    protected ?Collection $shellBlocks = null;

    /** @var Collection<string, Block>|null */
    protected ?Collection $singletonsByType = null;

    /** @var Collection<int, Block>|null */
    protected ?Collection $homeBlocks = null;

    /**
     * @return Collection<int, Block>
     */
    public function homeBlocks(): Collection
    {
        $this->ensureHomeLoaded();

        return $this->homeBlocks ?? new Collection;
    }

    public function singleton(string $type): ?Block
    {
        $this->ensureShellLoaded();

        return $this->singletonsByType?->get($type);
    }

    /**
     * @param  list<string>  $types
     */
    public function pageBlock(int $id, array $types): ?Block
    {
        $this->ensureHomeLoaded();

        $block = $this->homeBlocks?->firstWhere('id', $id);

        if ($block && in_array($block->type, $types, true)) {
            return $block;
        }

        $this->ensureShellLoaded();

        $shell = $this->shellBlocks?->get($id);

        if ($shell && in_array($shell->type, $types, true)) {
            return $shell;
        }

        return null;
    }

    public function flush(): void
    {
        $this->shellBlocks = null;
        $this->singletonsByType = null;
        $this->homeBlocks = null;
    }

    protected function ensureShellLoaded(): void
    {
        if ($this->shellBlocks !== null) {
            return;
        }

        if (! currentTenantId()) {
            $this->shellBlocks = new Collection;
            $this->singletonsByType = new Collection;

            return;
        }

        $blocks = Block::query()
            ->roots()
            ->whereNull('content_id')
            ->whereIn('type', self::SHELL_TYPES)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('id');

        $this->hydrateLinkContents($blocks);

        $this->shellBlocks = $blocks;
        $this->singletonsByType = $blocks->keyBy('type');
    }

    protected function ensureHomeLoaded(): void
    {
        if ($this->homeBlocks !== null) {
            return;
        }

        if (! currentTenantId()) {
            $this->homeBlocks = new Collection;

            return;
        }

        $this->homeBlocks = Block::query()
            ->roots()
            ->whereNull('content_id')
            ->where('is_default', false)
            ->where('active', true)
            ->where('position', 'home')
            ->orderBy('sort_order')
            ->get(['id', 'type', 'variant', 'title', 'data', 'sort_order', 'active', 'is_default', 'position'])
            ->values();
    }

    /**
     * Load CTA/footer link rows only for blocks that need them.
     *
     * @param  Collection<int, Block>  $blocks
     */
    protected function hydrateLinkContents(Collection $blocks): void
    {
        $linkBlockIds = $blocks
            ->filter(fn (Block $block): bool => in_array($block->type, ['cta', 'footer'], true))
            ->keys()
            ->all();

        if ($linkBlockIds === []) {
            foreach ($blocks as $block) {
                $block->setRelation('contents', new Collection);
            }

            return;
        }

        $contentsByBlockId = Content::query()
            ->whereIn('block_id', $linkBlockIds)
            ->whereIn('type', self::LINK_CONTENT_TYPES)
            ->where('active', true)
            ->orderBy('sort_order')
            ->get(['id', 'block_id', 'type', 'title', 'data', 'sort_order', 'active'])
            ->groupBy('block_id');

        foreach ($blocks as $block) {
            $block->setRelation(
                'contents',
                $contentsByBlockId->get($block->id, new Collection)->values()
            );
        }
    }
}
