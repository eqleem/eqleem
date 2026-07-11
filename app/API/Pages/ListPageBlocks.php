<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\MapsContentPageBlocks;
use App\API\Pages\Concerns\ResolvesPage;
use App\Http\Resources\PageBlockResource;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists user blocks for a content page.
 */
class ListPageBlocks
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsContentPageBlocks;
    use ResolvesPage;

    /**
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, string $uuid, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);

        $blocks = Block::queryForContent($content->id)
            ->userBlocks()
            ->orderBy('sort_order')
            ->get(['id', 'uuid', 'title', 'type', 'sort_order', 'active']);

        return [
            'blocks' => $this->mapContentPageBlocks($blocks, $blockTypes)->values()->all(),
            'block_types' => $blockTypes->options(addableOnly: true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $uuid, BlockTypeRegistry $blockTypes): array
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid, $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): AnonymousResourceCollection
    {
        return PageBlockResource::collection($payload['blocks'] ?? [])
            ->additional([
                'block_types' => $payload['block_types'] ?? [],
            ]);
    }
}
