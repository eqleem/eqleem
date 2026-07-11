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
 * Deletes a CTA/footer link belonging to a page block.
 */
class DeletePageBlockLink
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

    public function handle(Tenant $tenant, int $blockId, int $linkId): void
    {
        setCurrentTenant($tenant);

        $block = $this->findTenantRootBlock($blockId);

        if (! $block || ! in_array($block->type, ['cta', 'footer'], true)) {
            throw new NotFoundHttpException;
        }

        $contentType = $block->type === 'footer' ? 'footer-link' : 'cta-link';

        $link = Content::query()
            ->where('block_id', $block->id)
            ->type($contentType)
            ->whereKey($linkId)
            ->first();

        if (! $link) {
            throw new NotFoundHttpException;
        }

        $link->delete();
    }

    public function asController(ActionRequest $request, int $id, int $linkId): void
    {
        $this->handle($this->currentDashboardTenant($request), $id, $linkId);
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json([
            'message' => __('Settings updated successfully.'),
        ]);
    }
}
