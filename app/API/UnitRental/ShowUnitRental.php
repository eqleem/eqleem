<?php

namespace App\API\UnitRental;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\UnitRental\Concerns\ResolvesUnitRental;
use App\Http\Resources\UnitRentalResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Shows a store project for editing.
 */
class ShowUnitRental
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesUnitRental;

    public function handle(Tenant $tenant, string $uuid): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findUnitRental($uuid);

        return $content->fresh(['media']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        return $this->handle($this->currentDashboardTenant($request), $uuid);
    }

    public function jsonResponse(Content $content): UnitRentalResource
    {
        return new UnitRentalResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
            'calendar_options' => $this->calendarOptions()->values()->all(),
        ]);
    }
}
