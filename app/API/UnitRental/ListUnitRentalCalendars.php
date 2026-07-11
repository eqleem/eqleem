<?php

namespace App\API\UnitRental;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\UnitRentalCalendarResource;
use App\Models\Calendar;
use App\Models\Tenant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists rental-unit calendars for the dashboard.
 */
class ListUnitRentalCalendars
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return list<Calendar>
     */
    public function handle(Tenant $tenant, ?string $search = null): array
    {
        setCurrentTenant($tenant);

        $query = Calendar::query()
            ->where('type', 'rental-unit')
            ->orderByDesc('id');

        if (filled($search)) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->get()->all();
    }

    /**
     * @return list<Calendar>
     */
    public function asController(ActionRequest $request): array
    {
        $search = $request->query('search');

        return $this->handle(
            $this->currentDashboardTenant($request),
            is_string($search) ? $search : null,
        );
    }

    public function jsonResponse(array $calendars): AnonymousResourceCollection
    {
        return UnitRentalCalendarResource::collection($calendars);
    }
}
