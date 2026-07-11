<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\DashboardStatResource;
use App\Models\Tenant;
use App\Support\DashboardStats;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns a single home dashboard stat widget (orders|sales|visits|clients).
 * Shares the same cached payload as GetDashboardStats.
 */
class GetDashboardStat
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
     * @return array{metric: string, value: int, growth: int, value_formatted?: string, currency?: string}
     */
    public function handle(Tenant $tenant, string $metric, bool $fresh = false): array
    {
        $payload = DashboardStats::metricForTenant($tenant, $metric, $fresh);

        return [
            'metric' => $metric,
            ...$payload,
        ];
    }

    /**
     * @return array{metric: string, value: int, growth: int, value_formatted?: string, currency?: string}
     */
    public function asController(ActionRequest $request, string $metric): array
    {
        if (! in_array($metric, DashboardStats::METRICS, true)) {
            throw new NotFoundHttpException;
        }

        $tenant = $this->currentDashboardTenant($request);

        /** @var array{fresh?: bool} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            $metric,
            (bool) ($validated['fresh'] ?? false),
        );
    }

    /**
     * @param  array{metric: string, value: int, growth: int, value_formatted?: string, currency?: string}  $stat
     */
    public function jsonResponse(array $stat): DashboardStatResource
    {
        return new DashboardStatResource($stat);
    }
}
