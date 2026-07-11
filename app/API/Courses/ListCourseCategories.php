<?php

namespace App\API\Courses;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\API\Courses\Concerns\MapsCourseCategories;
use App\Models\Tenant;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists course categories.
 */
class ListCourseCategories
{
    use AsAction;
    use AuthorizesDashboardTenant;
    use MapsCourseCategories;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array{categories: list<array<string, mixed>>, parent_options: list<array{id: string, label: string}>}
     */
    public function handle(Tenant $tenant, ?string $search = null): array
    {
        setCurrentTenant($tenant);

        return [
            'categories' => $this->mapCategoryTree($search)->values()->all(),
            'parent_options' => $this->parentCategoryOptions(),
        ];
    }

    /**
     * @return array{categories: list<array<string, mixed>>, parent_options: list<array{id: string, label: string}>}
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
     * @param  array{categories: list<array<string, mixed>>, parent_options: list<array{id: string, label: string}>}  $result
     * @return array{data: array{categories: list<array<string, mixed>>, parent_options: list<array{id: string, label: string}>}}
     */
    public function jsonResponse(array $result): array
    {
        return [
            'data' => $result,
        ];
    }
}
