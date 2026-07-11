<?php

namespace App\API\Page;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use App\Services\TenantProfileService;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Deletes a social link from the header block editor.
 */
class DeletePageHeaderSocialLink
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, string $id): array
    {
        setCurrentTenant($tenant);

        app(TenantProfileService::class)->deleteSocialLink($tenant, $id);

        return app(TenantProfileService::class)->socialLinks($tenant->fresh())->values()->all();
    }

    public function asController(ActionRequest $request, string $id): array
    {
        return $this->handle($this->currentDashboardTenant($request), $id);
    }

    /**
     * @param  list<array<string, mixed>>  $links
     */
    public function jsonResponse(array $links): JsonResponse
    {
        return response()->json([
            'data' => $links,
            'message' => __('Settings updated successfully.'),
        ]);
    }
}
