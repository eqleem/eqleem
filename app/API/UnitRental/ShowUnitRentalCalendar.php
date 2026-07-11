<?php

namespace App\API\UnitRental;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\UnitRentalCalendarResource;
use App\Models\Calendar;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Shows a rental-unit calendar for editing.
 */
class ShowUnitRentalCalendar
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function handle(Tenant $tenant, int $id): Calendar
    {
        setCurrentTenant($tenant);

        $calendar = Calendar::query()
            ->where('type', 'rental-unit')
            ->whereKey($id)
            ->first();

        if (! $calendar instanceof Calendar) {
            throw new NotFoundHttpException;
        }

        return $calendar;
    }

    public function asController(ActionRequest $request, int $id): Calendar
    {
        return $this->handle($this->currentDashboardTenant($request), $id);
    }

    public function jsonResponse(Calendar $calendar): UnitRentalCalendarResource
    {
        return new UnitRentalCalendarResource($calendar);
    }
}
