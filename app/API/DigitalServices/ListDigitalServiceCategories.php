<?php

namespace App\API\DigitalServices;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\DigitalServices\Concerns\MapsDigitalServiceCategories;
use App\Models\Tenant;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists store categories as a flat tree.
 */
class ListDigitalServiceCategories
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsDigitalServiceCategories;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array{categories: Collection<int, array<string, mixed>>, parent_options: list<array{id: string, label: string}>}
     */
    public function handle(Tenant $tenant, ?string $search = null): array
    {
        setCurrentTenant($tenant);

        return [
            'categories' => $this->mapCategoryTree($search),
            'parent_options' => $this->parentCategoryOptions(),
        ];
    }

    /**
     * @return array{categories: Collection<int, array<string, mixed>>, parent_options: list<array{id: string, label: string}>}
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{search?: string|null} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
        );
    }

    /**
     * @param  array{categories: Collection<int, array<string, mixed>>, parent_options: list<array{id: string, label: string}>}  $payload
     * @return array{data: array{categories: list<array<string, mixed>>, parent_options: list<array{id: string, label: string}>}}
     */
    public function jsonResponse(array $payload): array
    {
        return [
            'data' => [
                'categories' => $payload['categories']->values()->all(),
                'parent_options' => $payload['parent_options'],
            ],
        ];
    }
}
