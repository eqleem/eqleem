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
 * Reorders user blocks on a content page.
 */
class ReorderPageBlocks
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsContentPageBlocks;
    use ResolvesPage;

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
    public function handle(Tenant $tenant, string $uuid, array $order, BlockTypeRegistry $blockTypes): array
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);

        foreach (array_values($order) as $index => $blockId) {
            Block::queryForContent($content->id)
                ->userBlocks()
                ->whereKey($blockId)
                ->update(['sort_order' => $index + 1]);
        }

        return ListPageBlocks::make()->handle($tenant, $uuid, $blockTypes);
    }

    /**
     * @return array<string, mixed>
     */
    public function asController(ActionRequest $request, string $uuid, BlockTypeRegistry $blockTypes): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{order: list<int>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, $validated['order'], $blockTypes);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function jsonResponse(array $payload): AnonymousResourceCollection
    {
        return PageBlockResource::collection($payload['blocks'] ?? [])
            ->additional([
                'block_types' => $payload['block_types'] ?? [],
                'message' => __('Blocks reordered successfully.'),
            ]);
    }
}
