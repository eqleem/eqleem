<?php

namespace App\API\Dashboard;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\DashboardChartResource;
use App\Models\Tenant;
use App\Support\DashboardCharts;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Returns one home dashboard trend chart (orders|sales|visits|clients).
 * Each chart is cached and loaded independently.
 */
class GetDashboardChart
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
     *     chart: string,
     *     title: string,
     *     label: string,
     *     range_days: int,
     *     options: array<string, mixed>
     * }
     */
    public function handle(Tenant $tenant, string $chart, bool $fresh = false): array
    {
        return DashboardCharts::forTenant($tenant, $chart, $fresh);
    }

    /**
     * @return array{
     *     chart: string,
     *     title: string,
     *     label: string,
     *     range_days: int,
     *     options: array<string, mixed>
     * }
     */
    public function asController(ActionRequest $request, string $chart): array
    {
        if (! in_array($chart, DashboardCharts::CHARTS, true)) {
            throw new NotFoundHttpException;
        }

        $tenant = $this->currentDashboardTenant($request);

        /** @var array{fresh?: bool} $validated */
        $validated = $request->validated();

        return $this->handle(
            $tenant,
            $chart,
            (bool) ($validated['fresh'] ?? false),
        );
    }

    /**
     * @param  array{
     *     chart: string,
     *     title: string,
     *     label: string,
     *     range_days: int,
     *     options: array<string, mixed>
     * }  $chart
     */
    public function jsonResponse(array $chart): DashboardChartResource
    {
        return new DashboardChartResource($chart);
    }
}
