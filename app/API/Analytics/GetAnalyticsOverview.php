<?php

namespace App\API\Analytics;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Http\Resources\AnalyticsOverviewResource;
use App\Models\Tenant;
use App\Services\TenantAnalyticsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Tenant-scoped analytics overview for the dashboard SPA.
 * Reuses me-shaon/laravel-request-analytics aggregation via TenantAnalyticsService.
 */
class GetAnalyticsOverview
{
    use AsAction;
    use AuthorizesDashboardTenant;

    public function __construct(private TenantAnalyticsService $analytics) {}

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'date_range' => ['sometimes', 'integer', 'min:1', 'max:365'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
            'request_category' => ['sometimes', 'nullable', 'string', 'in:web,api'],
            'fresh' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @param  array{
     *     date_range?: int,
     *     start_date?: string,
     *     end_date?: string,
     *     request_category?: string|null
     * }  $params
     * @return array{
     *     summary: array{views: int, visitors: int, bounce_rate: string, average_visit_time: string},
     *     chart: array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>},
     *     top_pages: list<array<string, mixed>>,
     *     top_referrers: list<array<string, mixed>>,
     *     browsers: list<array<string, mixed>>,
     *     devices: list<array<string, mixed>>,
     *     countries: list<array<string, mixed>>,
     *     operating_systems: list<array<string, mixed>>,
     *     date_range: array{start: Carbon, end: Carbon, days: int, key: string}
     * }
     */
    public function handle(Tenant $tenant, array $params = [], bool $fresh = false): array
    {
        setCurrentTenant($tenant);

        $dateRange = $this->analytics->getDateRange($params);
        $category = $params['request_category'] ?? 'all';
        $cacheKey = "analytics.overview.{$tenant->id}.{$dateRange['key']}.{$category}";
        $ttl = max(1, (int) config('request-analytics.cache.ttl', 1));

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        /** @var array{
         *     summary: array{views: int, visitors: int, bounce_rate: string, average_visit_time: string},
         *     chart: array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>},
         *     top_pages: list<array<string, mixed>>,
         *     top_referrers: list<array<string, mixed>>,
         *     browsers: list<array<string, mixed>>,
         *     devices: list<array<string, mixed>>,
         *     countries: list<array<string, mixed>>,
         *     operating_systems: list<array<string, mixed>>,
         *     date_range: array{start: Carbon, end: Carbon, days: int, key: string}
         * } $payload
         */
        $payload = Cache::remember(
            $cacheKey,
            now()->addMinutes($ttl),
            fn (): array => $this->analytics->overviewForTenant($params),
        );

        return $payload;
    }

    /**
     * @return array{
     *     summary: array{views: int, visitors: int, bounce_rate: string, average_visit_time: string},
     *     chart: array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>},
     *     top_pages: list<array<string, mixed>>,
     *     top_referrers: list<array<string, mixed>>,
     *     browsers: list<array<string, mixed>>,
     *     devices: list<array<string, mixed>>,
     *     countries: list<array<string, mixed>>,
     *     operating_systems: list<array<string, mixed>>,
     *     date_range: array{start: Carbon, end: Carbon, days: int, key: string}
     * }
     */
    public function asController(ActionRequest $request): array
    {
        $tenant = $this->currentDashboardTenant($request);

        /** @var array{
         *     date_range?: int,
         *     start_date?: string,
         *     end_date?: string,
         *     request_category?: string|null,
         *     fresh?: bool
         * } $validated
         */
        $validated = $request->validated();

        $fresh = (bool) ($validated['fresh'] ?? false);
        unset($validated['fresh']);

        if (! isset($validated['date_range']) && ! isset($validated['start_date'])) {
            $validated['date_range'] = 30;
        }

        return $this->handle($tenant, $validated, $fresh);
    }

    /**
     * @param  array{
     *     summary: array{views: int, visitors: int, bounce_rate: string, average_visit_time: string},
     *     chart: array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>},
     *     top_pages: list<array<string, mixed>>,
     *     top_referrers: list<array<string, mixed>>,
     *     browsers: list<array<string, mixed>>,
     *     devices: list<array<string, mixed>>,
     *     countries: list<array<string, mixed>>,
     *     operating_systems: list<array<string, mixed>>,
     *     date_range: array{start: Carbon, end: Carbon, days: int, key: string}
     * }  $overview
     */
    public function jsonResponse(array $overview): AnalyticsOverviewResource
    {
        return new AnalyticsOverviewResource($overview);
    }
}
