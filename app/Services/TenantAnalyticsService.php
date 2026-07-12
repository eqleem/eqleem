<?php

namespace App\Services;

use App\Models\RequestAnalytics;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MeShaon\RequestAnalytics\Services\AnalyticsService;

/**
 * Tenant-scoped wrapper around the package AnalyticsService.
 *
 * Uses App\Models\RequestAnalytics (BelongsToTenant) and scopes bounce-rate
 * calculations to the current tenant — the package query does not.
 */
class TenantAnalyticsService extends AnalyticsService
{
    public function getBaseQuery(array $dateRange, ?string $requestCategory = null): Builder
    {
        return RequestAnalytics::query()
            ->whereBetween('visited_at', [$dateRange['start'], $dateRange['end']])
            ->when($requestCategory, fn (Builder $query, string $category) => $query->where('request_category', $category));
    }

    public function getSummary($query, array $dateRange): array
    {
        $totalViews = (clone $query)->count();
        $uniqueVisitors = $this->getUniqueVisitorCount($query);

        $tableName = config('request-analytics.database.table', 'request_analytics');
        $connection = config('request-analytics.database.connection');
        $tenantId = currentTenantId();

        $sessionsWithSinglePageView = DB::connection($connection)->table(function ($sub) use ($dateRange, $tableName, $tenantId): void {
            $sub->from($tableName)
                ->select('session_id')
                ->whereBetween('visited_at', [$dateRange['start'], $dateRange['end']])
                ->when($tenantId, fn ($q) => $q->where('tenant_id', $tenantId))
                ->groupBy('session_id')
                ->havingRaw('COUNT(*) = 1');
        }, 'single_page_sessions')->count();

        $bounceRate = $uniqueVisitors > 0
            ? round(($sessionsWithSinglePageView / $uniqueVisitors) * 100, 1)
            : 0;

        $durationExpression = $this->getDurationExpression('visited_at');
        $sessionTimes = (clone $query)
            ->select(
                'session_id',
                DB::raw("{$durationExpression} as duration")
            )
            ->groupBy('session_id')
            ->havingRaw("{$durationExpression} > 0")
            ->pluck('duration')
            ->toArray();

        $avgVisitTime = count($sessionTimes) > 0
            ? round(array_sum($sessionTimes) / count($sessionTimes), 1)
            : 0;

        return [
            'views' => $totalViews,
            'visitors' => $uniqueVisitors,
            'bounce_rate' => $bounceRate.'%',
            'average_visit_time' => $this->formatTimeWithCarbon($avgVisitTime),
        ];
    }

    /**
     * @param  array{
     *     date_range?: int,
     *     start_date?: string,
     *     end_date?: string,
     *     request_category?: string|null,
     *     with_percentages?: bool
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
    public function overviewForTenant(array $params): array
    {
        $params['with_percentages'] = true;
        $dateRange = $this->getDateRange($params);
        $data = $this->getOverviewData($params);

        return [
            ...$data,
            'date_range' => $dateRange,
        ];
    }
}
