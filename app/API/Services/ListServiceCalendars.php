<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\ServiceCalendarResource;
use App\Models\Calendar;
use App\Models\Tenant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists service-provider calendars for the dashboard.
 */
class ListServiceCalendars
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
            ->where('type', 'service-provider')
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
        return ServiceCalendarResource::collection($calendars);
    }
}
