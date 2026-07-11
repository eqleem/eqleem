<?php

namespace App\API\Services;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Services\Concerns\MapsServiceCategories;
use App\Models\Taxonomy;
use App\Models\Tenant;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Updates a store category.
 */
class UpdateServiceCategory
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
        $categoryId = (int) request()->route('id');

        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('taxonomies', 'slug')
                    ->where(function ($query): void {
                        $query->where('type', 'service_category');

                        if ($tenantId = currentTenantId()) {
                            $query->where('tenant_id', $tenantId);
                        }
                    })
                    ->ignore($categoryId),
            ],
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
     * @param  array{name: string, slug: string, parent_id?: int|null}  $data
     * @return array{category: array<string, mixed>, categories: list<array<string, mixed>>}
     */
    public function handle(Tenant $tenant, int $id, array $data): array
    {
        setCurrentTenant($tenant);

        $category = Taxonomy::query()
            ->type('service_category')
            ->whereKey($id)
            ->first();

        if (! $category instanceof Taxonomy) {
            throw new NotFoundHttpException;
        }

        $parentId = filled($data['parent_id'] ?? null) ? (int) $data['parent_id'] : null;

        if ($parentId !== null && in_array($parentId, $this->descendantCategoryIds($id), true)) {
            abort(422, __('A category cannot be nested under itself or its descendants.'));
        }

        $category->update([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'type' => 'service_category',
            'parent_id' => $parentId,
        ]);

        return [
            'category' => $this->mapCategory($category->fresh()),
            'categories' => $this->mapCategoryTree()->values()->all(),
        ];
    }

    /**
     * @return array{category: array<string, mixed>, categories: list<array<string, mixed>>}
     */
    public function asController(ActionRequest $request, int $id): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{name: string, slug: string, parent_id?: int|null} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $id, $validated);
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
