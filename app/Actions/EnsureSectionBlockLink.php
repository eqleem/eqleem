<?php

namespace App\Actions;

use App\Models\Block;
use App\Support\BlockTypeRegistry;
use App\Support\CtaLink;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

class EnsureSectionBlockLink
{
    use AsAction;

    /**
     * @var list<string>
     */
    protected const EXCLUDED_SECTIONS = ['forms', 'pages'];

    public function handle(int $tenantId, string $contentTypeSlug, ?BlockTypeRegistry $blockTypes = null): ?Block
    {
        if (in_array($contentTypeSlug, self::EXCLUDED_SECTIONS, true)) {
            return null;
        }

        $indexRoute = config("cta-link-types.routes.{$contentTypeSlug}.index");

        if (! is_string($indexRoute) || ! filled($indexRoute)) {
            return null;
        }

        $blockTypes ??= app(BlockTypeRegistry::class);
        $blockLinkType = $blockTypes->find('block-link');

        if (! $blockLinkType) {
            return null;
        }

        $existing = Block::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('type', 'block-link')
            ->where('data->content_type', $contentTypeSlug)
            ->where('data->link_type', 'section')
            ->first();

        if ($existing) {
            return $existing;
        }

        $typeKey = 'section:'.$contentTypeSlug;
        $title = CtaLink::blockLinkTitleFromTypeKey($typeKey);
        $description = CtaLink::blockLinkDescriptionFromTypeKey($typeKey);

        $maxOrder = Block::query()
            ->withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('position', 'home')
            ->max('sort_order') ?? 0;

        return Block::query()->create([
            'tenant_id' => $tenantId,
            'uuid' => Str::uuid(),
            'component' => $blockLinkType->component,
            'type' => 'block-link',
            'title' => $title,
            'slug' => $contentTypeSlug.'-'.Str::lower(Str::random(8)),
            'data' => [
                'link_type' => 'section',
                'content_type' => $contentTypeSlug,
                'content_id' => null,
                'title' => $title,
                'description' => $description,
                'managed_section' => true,
            ],
            'sort_order' => $maxOrder + 1,
            'is_default' => false,
            'status' => 'published',
            'active' => true,
            'position' => 'home',
            'published_at' => now(),
        ]);
    }
}
