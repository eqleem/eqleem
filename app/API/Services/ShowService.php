<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Services\Concerns\ResolvesService;
use App\Http\Resources\ServiceResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a store project for editing.
 */
class ShowService
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesService;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findService($uuid);
        $content->loadMissing(['media', 'taxonomies', 'calendars']);

        return $content;
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): ServiceResource
    {
        return new ServiceResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
            'calendar_options' => $this->calendarOptions()->values()->all(),
        ]);
    }
}
