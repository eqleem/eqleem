<?php

namespace App\API\UnitRental;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\UnitRental\Concerns\ResolvesUnitRental;
use App\Http\Resources\UnitRentalListResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Toggles active (published) state for a unit rental type.
 */
class ToggleUnitRentalActive
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesUnitRental;

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
            'active' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        if ($request->exists('active')) {
            $request->merge([
                'active' => filter_var($request->input('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    public function handle(Tenant $tenant, string $uuid, bool $active): Content
    {
        setCurrentTenant($tenant);

        $content = $this->findUnitRental($uuid);
        $content->update([
            'active' => $active,
            'status' => $active ? 'published' : 'draft',
            'published_at' => $active
                ? ($content->published_at ?? now())
                : null,
        ]);

        return $content->fresh(['media']);
    }

    public function asController(ActionRequest $request, string $uuid): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{active: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $uuid, (bool) $validated['active']);
    }

    public function jsonResponse(Content $content): UnitRentalListResource
    {
        return (new UnitRentalListResource($content))
            ->additional([
                'message' => $content->active ? 'تم تفعيل نوع الوحدة.' : 'تم تعطيل نوع الوحدة.',
            ]);
    }
}
