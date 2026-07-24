<?php

namespace App\API\OnDemandServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\OnDemandServices\Concerns\ResolvesOnDemandService;
use App\Http\Resources\OnDemandServiceResource;
use App\Models\Content;
use App\Models\Tenant;
use App\Support\OnDemandUnit;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a draft on-demand service (title only).
 */
class CreateOnDemandService
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use ResolvesOnDemandService;

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
            'type' => $this->onDemandServiceType(),
            'title' => $title,
            'slug' => $this->uniqueOnDemandServiceSlug($this->slugifyTitle($title)),
            'status' => 'draft',
            'active' => false,
            'data' => [
                'unit_type' => OnDemandUnit::SquareMeter,
                'unit_label' => '',
                'price' => 0,
                'compare_price' => null,
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

    public function jsonResponse(Content $content): OnDemandServiceResource
    {
        return (new OnDemandServiceResource($content, [
            'slug_prefix' => $this->slugPrefix(),
            'unit_options' => $this->unitOptions(),
        ]))->additional([
            'message' => __('Saved'),
        ]);
    }
}
