<?php

namespace App\API\Page;

use App\Actions\EnsureSectionBlockLink;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Http\Resources\PageStructureResource;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use App\Support\ContentType;
use App\Support\ContentTypeRegistry;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns the page structure (system + user blocks) and addable block types.
 */
class GetPageStructure
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

    /**
     * @return array<string, mixed>
     */
    public function handle(
        Tenant $tenant,
        BlockTypeRegistry $blockTypes,
        ?ContentTypeRegistry $contentTypes = null,
    ): array {
        setCurrentTenant($tenant);
        $contentTypes ??= app(ContentTypeRegistry::class);

        $contentTypes->all($tenant)
            ->filter(fn (ContentType $contentType): bool => $contentType->section === 'sell')
            ->each(fn (ContentType $contentType): ?Block => EnsureSectionBlockLink::run(
                $tenant->id,
                $contentType->slug,
                $blockTypes,
            ));

        $grouped = $this->groupedBlocks($blockTypes);

        return [
            'top_blocks' => $grouped['top']->all(),
            'cta_block' => $grouped['cta'],
            'user_blocks' => $grouped['user']->all(),
            'bottom_blocks' => $grouped['bottom']->all(),
            'float_links_block' => $grouped['float_links'],
            'block_types' => $blockTypes->options(addableOnly: true),
            'block_link_editor' => $this->blockLinkEditorPayload([]),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(
        ActionRequest $request,
        BlockTypeRegistry $blockTypes,
        ContentTypeRegistry $contentTypes,
    ): array {
        return $this->handle(
            $this->currentDashboardTenant($request),
            $blockTypes,
            $contentTypes,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PageStructureResource
    {
        return new PageStructureResource($payload);
    }
}
