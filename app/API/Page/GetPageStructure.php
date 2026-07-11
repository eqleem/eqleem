<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Http\Resources\PageStructureResource;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
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
    public function handle(Tenant $tenant, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $grouped = $this->groupedBlocks($blockTypes);

        return [
            'top_blocks' => $grouped['top']->all(),
            'user_blocks' => $grouped['user']->all(),
            'bottom_blocks' => $grouped['bottom']->all(),
            'block_types' => $blockTypes->options(addableOnly: true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, BlockTypeRegistry $blockTypes): array
    {
        return $this->handle($this->currentDashboardTenant($request), $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PageStructureResource
    {
        return new PageStructureResource($payload);
    }
}
