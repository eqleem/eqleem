<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Branch;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Soft-deletes a branch for the current dashboard tenant.
 */
class DeleteBranch
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, int $id): void
    {
        setCurrentTenant($tenant);

        $branch = Branch::query()->find($id);

        if (! $branch) {
            throw new NotFoundHttpException;
        }

        $branch->delete();
    }

    public function asController(ActionRequest $request, int $id): void
    {
        $this->handle($this->currentDashboardTenant($request), $id);
    }

    public function jsonResponse(): JsonResponse
    {
        return response()->json([
            'message' => __('Settings updated successfully.'),
        ]);
    }
}
