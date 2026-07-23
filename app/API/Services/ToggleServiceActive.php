<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Services\Concerns\ResolvesService;
use App\Http\Resources\ServiceListResource;
use App\Models\Content;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Toggles active (published) state for a service.
 */
class ToggleServiceActive
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesService;

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

        $content = $this->findService($uuid);
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

    public function jsonResponse(Content $content): ServiceListResource
    {
        return (new ServiceListResource($content))
            ->additional([
                'message' => $content->active ? 'تم تفعيل الخدمة.' : 'تم تعطيل الخدمة.',
            ]);
    }
}
