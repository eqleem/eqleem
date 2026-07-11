<?php

namespace App\API\Portfolio;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Portfolio\Concerns\MapsPortfolioCategories;
use App\Models\Taxonomy;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Reorders portfolio categories (sort_order per sibling group).
 */
class ReorderPortfolioCategories
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsPortfolioCategories;

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
            'order' => ['required', 'array', 'min:1'],
            'order.*' => ['integer'],
        ];
    }

    /**
     * @param  list<int>  $order
     * @return array{categories: list<array<string, mixed>>}
     */
    public function handle(Tenant $tenant, array $order): array
    {
        setCurrentTenant($tenant);

        $orderedIds = collect($order)
            ->map(fn (mixed $id): int => (int) $id)
            ->values()
            ->all();

        $categories = Taxonomy::query()
            ->type('portfolio_category')
            ->whereIn('id', $orderedIds)
            ->get()
            ->keyBy('id');

        $siblingCounters = [];

        foreach ($orderedIds as $id) {
            $category = $categories->get($id);

            if (! $category instanceof Taxonomy) {
                continue;
            }

            $parentKey = (string) ($category->parent_id ?? 'root');
            $sortOrder = $siblingCounters[$parentKey] ?? 0;

            $category->update(['sort_order' => $sortOrder]);

            $siblingCounters[$parentKey] = $sortOrder + 1;
        }

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
