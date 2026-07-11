<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Calendar;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a service-provider calendar.
 */
class DeleteServiceCalendar
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return list<string>
     */
    public function getControllerMiddleware(): array
    {
        return [
            'auth:sanctum',
            'throttle:30,1',
        ];
    }

    public function handle(Tenant $tenant, int $id): void
    {
        setCurrentTenant($tenant);

        $calendar = Calendar::query()
            ->where('type', 'service-provider')
            ->whereKey($id)
            ->first();

        if (! $calendar instanceof Calendar) {
            throw new NotFoundHttpException;
        }

        $calendar->delete();
    }

    public function asController(ActionRequest $request, int $id): void
    {
        $this->handle($this->currentDashboardTenant($request), $id);
    }

    /**
     * @return array{data: array{deleted: int}, message: string}
     */
    public function jsonResponse(): array
    {
        return [
            'data' => ['deleted' => 1],
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
