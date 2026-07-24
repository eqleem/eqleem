<?php

namespace App\API\Blog;

use App\API\Blog\Concerns\MapsBlogCategories;
use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Reorders blog categories (sort_order per sibling group).
 */
class ReorderBlogCategories
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsBlogCategories;

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
        return $this->orderRules();
    }

    /**
     * @param  list<int>  $order
     * @return array{categories: list<array<string, mixed>>}
     */
    public function handle(Tenant $tenant, array $order): array
    {
        setCurrentTenant($tenant);

        $this->reorderSiblingCategories($order);

        return [
            'categories' => $this->mapCategoryTree()->values()->all(),
        ];
    }

    /**
     * @return array{categories: list<array<string, mixed>>}
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{order: list<int>} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, $validated['order']);
    }

    /**
     * @param  array{categories: list<array<string, mixed>>}  $result
     * @return array{data: array{categories: list<array<string, mixed>>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Saved'),
        ];
    }
}
