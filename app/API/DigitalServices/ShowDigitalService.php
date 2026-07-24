<?php

namespace App\API\DigitalServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalServices\Concerns\ResolvesDigitalService;
use App\Http\Resources\DigitalServiceResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a store project for editing.
 */
class ShowDigitalService
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesDigitalService;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findDigitalService($uuid);
        $content->loadMissing(['media', 'taxonomies']);

        return $content;
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): DigitalServiceResource
    {
        return new DigitalServiceResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
        ]);
    }
}
