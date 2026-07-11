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
 * Updates a rental-unit calendar.
 */
class UpdateUnitRentalCalendar
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
            'use_branch_hours' => ['sometimes', 'boolean'],
            'availabilities' => ['nullable', 'array'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('use_branch_hours')) {
            $request->merge([
                'use_branch_hours' => filter_var($request->input('use_branch_hours'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    /**
     * @param  array{
     *     name: string,
     *     from?: string|null,
     *     to?: string|null,
     *     use_branch_hours?: bool,
     *     availabilities?: array<string, mixed>|null
     * }  $data
     */
    public function handle(Tenant $tenant, int $id, array $data): Calendar
    {
        setCurrentTenant($tenant);

        $calendar = Calendar::query()
            ->where('type', 'rental-unit')
            ->whereKey($id)
            ->first();

        if (! $calendar instanceof Calendar) {
            throw new NotFoundHttpException;
        }

        $useBranchHours = (bool) ($data['use_branch_hours'] ?? data_get($calendar->meta, 'use_branch_hours', false));
        $meta = $calendar->meta ?? [];
        $meta['use_branch_hours'] = $useBranchHours;

        $calendar->update([
            'name' => $data['name'],
            'from' => $useBranchHours ? null : (filled($data['from'] ?? null) ? $data['from'] : null),
            'to' => $useBranchHours ? null : (filled($data['to'] ?? null) ? $data['to'] : null),
            'availabilities' => $useBranchHours
                ? null
                : Calendar::normalizeAvailabilities($data['availabilities'] ?? null),
            'meta' => $meta,
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
         *     use_branch_hours?: bool,
         *     availabilities?: array<string, mixed>|null
         * } $validated
         */
        $validated = $request->validated();

        return $this->handle($this->currentDashboardTenant($request), $id, $validated);
    }

    public function jsonResponse(Calendar $calendar): UnitRentalCalendarResource
    {
        return (new UnitRentalCalendarResource($calendar))->additional([
            'message' => __('Saved'),
        ]);
    }
}
