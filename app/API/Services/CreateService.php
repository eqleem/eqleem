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
 * Creates a draft service (title only).
 */
class CreateService
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
            'title' => ['required', 'string', 'min:1', 'max:255'],
        ];
    }

    public function handle(Tenant $tenant, string $title): Content
    {
        setCurrentTenant($tenant);

        return Content::query()->create([
            'tenant_id' => $tenant->id,
            'type' => $this->serviceType(),
            'title' => $title,
            'slug' => $this->uniqueServiceSlug($this->slugifyTitle($title)),
            'status' => 'draft',
            'active' => false,
            'data' => [
                'duration_minutes' => 60,
            ],
        ]);
    }

    public function asController(ActionRequest $request): Content
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{title: string} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, trim($validated['title']));
    }

    public function jsonResponse(Content $content): ServiceResource
    {
        return (new ServiceResource($content->fresh(), [
            'slug_prefix' => $this->slugPrefix(),
            'category_options' => $this->categoryOptions()->values()->all(),
            'calendar_options' => $this->calendarOptions()->values()->all(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
