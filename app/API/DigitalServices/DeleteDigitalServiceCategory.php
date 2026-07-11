<?php

namespace App\API\DigitalServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalServices\Concerns\MapsDigitalServiceCategories;
use App\Models\Taxonomy;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Deletes a store category.
 */
class DeleteDigitalServiceCategory
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsDigitalServiceCategories;

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
     * @return array{categories: list<array<string, mixed>>}
     */
    public function handle(Tenant $tenant, int $id): array
    {
        setCurrentTenant($tenant);

        $category = Taxonomy::query()
            ->type('digital_service_category')
            ->whereKey($id)
            ->first();

        if (! $category instanceof Taxonomy) {
            throw new NotFoundHttpException;
        }

        $category->delete();

        return [
            'categories' => $this->mapCategoryTree()->values()->all(),
        ];
    }

    /**
     * @return array{categories: list<array<string, mixed>>}
     */
    public function asController(ActionRequest $request, int $id): array
    {
        return $this->handle($this->currentDashboardTenant($request), $id);
    }

    /**
     * @param  array{categories: list<array<string, mixed>>}  $result
     * @return array{data: array{categories: list<array<string, mixed>>}, message: string}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
            'message' => __('Item(s) deleted successfully.'),
        ];
    }
}
