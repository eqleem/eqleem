<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Http\Resources\PageStructureResource;
use App\Models\Block;
use App\Models\Tenant;
use App\Support\BlockTypeRegistry;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Reorders user page blocks for the current tenant.
 */
class ReorderPageBlocks
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['required', 'integer', 'distinct', 'min:1'],
        ];
    }

    /**
     * @param  list<int>  $order
     * @return array<string, mixed>
     */
    public function handle(Tenant $tenant, array $order, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        foreach (array_values($order) as $index => $blockId) {
            Block::queryForTenantRoots()
                ->userBlocks()
                ->whereKey($blockId)
                ->update(['sort_order' => $index + 1]);
        }

        return GetPageStructure::make()->handle($tenant, $blockTypes);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, BlockTypeRegistry $blockTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{order: list<int>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated['order'], $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): PageStructureResource
    {
        return (new PageStructureResource($payload))
            ->additional([
                'message' => __('Blocks reordered successfully.'),
            ]);
    }
}
