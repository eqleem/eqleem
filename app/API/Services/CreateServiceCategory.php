<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Services\Concerns\MapsServiceCategories;
use App\Models\Taxonomy;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Creates a store category.
 */
class CreateServiceCategory
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsServiceCategories;

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
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('taxonomies', 'id')->where(function ($query): void {
                    $query->where('type', 'service_category');

                    if ($tenantId = currentTenantId()) {
                        $query->where('tenant_id', $tenantId);
                    }
                }),
            ],
        ];
    }

    /**
     * @param  array{name: string, parent_id?: int|null}  $data
     * @return array{category: array<string, mixed>, categories: list<array<string, mixed>>}
     */
    public function handle(Tenant $tenant, array $data): array
    {
        setCurrentTenant($tenant);

        $parentId = filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null;

        $category = Taxonomy::query()->create([
            'tenant_id' => $tenant->id,
            'name' => $data['name'],
            'type' => 'service_category',
            'parent_id' => $parentId,
            'sort_order' => (int) Taxonomy::query()
                ->type('service_category')
                ->where('parent_id', $parentId)
                ->max('sort_order') + 1,
        ]);

        return [
            'category' => $this->mapCategory($category->fresh()),
            'categories' => $this->mapCategoryTree()->values()->all(),
        ];
    }

    /**
     * @return array{category: array<string, mixed>, categories: list<array<string, mixed>>}
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{name: string, parent_id?: int|null} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated);
    }

    /**
     * @param  array{category: array<string, mixed>, categories: list<array<string, mixed>>}  $result
     * @return array{data: array{category: array<string, mixed>, categories: list<array<string, mixed>>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
