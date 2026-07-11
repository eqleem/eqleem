<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\ServiceCalendarResource;
use App\Models\Calendar;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Updates a service-provider calendar.
 */
class UpdateServiceCalendar
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

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'availabilities' => ['nullable', 'array'],
        ];
    }

    /**
     * @param  array{
     *     name: string,
     *     from?: string|null,
     *     to?: string|null,
     *     availabilities?: array<string, mixed>|null
     * }  $data
     */
    public function handle(Tenant $tenant, int $id, array $data): Calendar
    {
        setCurrentTenant($tenant);

        $calendar = Calendar::query()
            ->where('type', 'service-provider')
            ->whereKey($id)
            ->first();

        if (! $calendar instanceof Calendar) {
            throw new NotFoundHttpException;
        }

        $calendar->update([
            'name' => $data['name'],
            'from' => filled($data['from'] ?? null) ? $data['from'] : null,
            'to' => filled($data['to'] ?? null) ? $data['to'] : null,
            'availabilities' => Calendar::normalizeAvailabilities($data['availabilities'] ?? null),
            'active' => true,
        ]);

        return $calendar->fresh();
    }

    public function asController(ActionRequest $request, int $id): Calendar
    {
        /** @var array{
         *     name: string,
         *     from?: string|null,
         *     to?: string|null,
         *     availabilities?: array<string, mixed>|null
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($this->currentDashboardTenant($request), $id, $validated);
    }

    public function jsonResponse(Calendar $calendar): ServiceCalendarResource
    {
        return (new ServiceCalendarResource($calendar))->additional([
            'message' => __('Saved'),
        ]);
    }
}
