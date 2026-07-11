<?php

namespace App\API\Pages;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Pages\Concerns\MapsContentPageBlocks;
use App\API\Pages\Concerns\ResolvesPage;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a user block from a content page.
 */
class DeletePageBlock
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsContentPageBlocks;
    use ResolvesPage;

    public function handle(Tenant $tenant, string $uuid, int $id): void
    {
        setCurrentTenant($tenant);

        $content = $this->findPage($uuid);
        $block = $this->findContentUserBlock($content->id, $id);

        if (! $block) {
            throw new NotFoundHttpException;
        }

        $block->delete();
    }

    public function asController(ActionRequest $request, string $uuid, int $id): void
    {
        $this->handle($this->currentDashboardTenant($request), $uuid, $id);
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json([
            'message' => __('Block deleted successfully.'),
        ]);
    }
}
