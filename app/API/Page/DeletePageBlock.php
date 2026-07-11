<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Page\Concerns\MapsPageBlocks;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a user page block for the current tenant.
 */
class DeletePageBlock
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPageBlocks;

    public function handle(Tenant $tenant, int $id): void
    {
        setCurrentTenant($tenant);

        $block = $this->findUserBlock($id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        $block->delete();
    }

    public function asController(ActionRequest $request, int $id): void
    {
        $this->handle($this->currentDashboardTenant($request), $id);
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json([
            'message' => __('Block deleted successfully.'),
        ]);
    }
}
