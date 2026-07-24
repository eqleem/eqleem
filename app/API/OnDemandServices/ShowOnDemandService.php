<?php

namespace App\API\OnDemandServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\OnDemandServices\Concerns\ResolvesOnDemandService;
use App\Http\Resources\OnDemandServiceResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows an on-demand service for editing.
 */
class ShowOnDemandService
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesOnDemandService;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findOnDemandService($uuid);
        $content->loadMissing(['media', 'taxonomies']);

        return $content;
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): OnDemandServiceResource
    {
        return new OnDemandServiceResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'unit_options' => $this->unitOptions(),
        ]);
    }
}
