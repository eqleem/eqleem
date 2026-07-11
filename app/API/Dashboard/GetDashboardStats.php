<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\DashboardStatsResource;
use App\Models\Tenant;
use App\Support\DashboardStats;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns all home dashboard stat widgets (orders, sales, visits, clients).
 */
class GetDashboardStats
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'fresh' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array{
     *     range_days: int,
     *     orders: array{value: int, growth: int},
     *     sales: array{value: int, growth: int, value_formatted: string, currency: string},
     *     visits: array{value: int, growth: int},
     *     clients: array{value: int, growth: int}
     * }
     */
    public function handle(Tenant $tenant, bool $fresh = false): array
    {
        return DashboardStats::forTenant($tenant, $fresh);
    }

    /**
     * @return array{
     *     range_days: int,
     *     orders: array{value: int, growth: int},
     *     sales: array{value: int, growth: int, value_formatted: string, currency: string},
     *     visits: array{value: int, growth: int},
     *     clients: array{value: int, growth: int}
     * }
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{fresh?: bool} $validated */
        $validated = $request->validated();

        return $this->handle($tenant, (bool) ($validated['fresh'] ?? false));
    }

    /**
     * @param  array{
     *     range_days: int,
     *     orders: array{value: int, growth: int},
     *     sales: array{value: int, growth: int, value_formatted: string, currency: string},
     *     visits: array{value: int, growth: int},
     *     clients: array{value: int, growth: int}
     * }  $stats
     */
    public function jsonResponse(array $stats): DashboardStatsResource
    {
        return new DashboardStatsResource($stats);
    }
}
