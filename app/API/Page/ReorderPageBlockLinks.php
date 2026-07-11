<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Models\Content;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Reorders CTA/footer links for a page block.
 */
class ReorderPageBlockLinks
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
     */
    public function handle(Tenant $tenant, int $blockId, array $order): void
    {
        setCurrentTenant($tenant);

        $block = $this->findTenantRootBlock($blockId);

        if (! $block || ! in_array($block->type, ['cta', 'footer'], true)) {
            throw new NotFoundHttpException;
        }

        $contentType = $block->type === 'footer' ? 'footer-link' : 'cta-link';

        foreach (array_values($order) as $index => $linkId) {
            Content::query()
                ->where('block_id', $block->id)
                ->type($contentType)
                ->whereKey($linkId)
                ->update(['sort_order' => $index + 1]);
        }
    }

    public function asController(ActionRequest $request, int $id): void
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{order: list<int>} $validated */
        $validated = $request->validated();

        $this->handle($tenant, $id, $validated['order']);
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json([
            'message' => __('Blocks reordered successfully.'),
        ]);
    }
}
