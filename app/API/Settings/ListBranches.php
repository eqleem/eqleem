<?php

namespace App\API\Settings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use App\Models\Calendar;
use App\Models\Tenant;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Lists branches for the current dashboard tenant.
 */
class ListBranches
{
    use AsAction;
    use AuthorizesDashboardTenant;

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
     * @return Collection<int, Branch>
     */
    public function handle(Tenant $tenant, ?string $search = null): Collection
    {
        setCurrentTenant($tenant);

        $branches = Branch::query()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($search !== null && $search !== '') {
            $term = mb_strtolower($search);

            $branches = $branches->filter(function (Branch $branch) use ($term): bool {
                return str_contains(mb_strtolower($branch->display_name), $term)
                    || str_contains(mb_strtolower((string) $branch->city), $term)
                    || str_contains(mb_strtolower((string) $branch->address), $term)
                    || str_contains(mb_strtolower((string) $branch->email), $term)
                    || str_contains(mb_strtolower((string) $branch->phone), $term);
            })->values();
        }

        return $branches;
    }

    /**
     * @return Collection<int, Branch>
     */
    public function asController(ActionRequest $request): Collection
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{search?: string|null} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            isset($validated['search']) ? trim((string) $validated['search']) : null,
        );
    }

    public function jsonResponse(Collection $branches): AnonymousResourceCollection
    {
        return BranchResource::collection($branches)->additional([
            'meta' => [
                'countries' => config('verification.countries', []),
                'cities' => config('branches.cities', []),
                'weekday_labels' => Calendar::weekdayLabels(),
            ],
        ]);
    }
}
