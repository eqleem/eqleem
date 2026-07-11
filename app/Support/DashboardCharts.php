<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RequestAnalytics;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use SaKanjo\EasyMetrics\Metrics\Trend;
use SaKanjo\EasyMetrics\Result;

/**
 * Home dashboard trend charts (orders, sales, visits, clients).
 *
 * Each chart is cached independently (Cache::flexible) so widgets can load
 * in parallel without blocking each other. Mirrors HasChartWidget options.
 */
class DashboardCharts
{
    public const FRESH_SECONDS = 180;

    public const STALE_SECONDS = 360;

    /** @var list<string> */
    public const CHARTS = ['orders', 'sales', 'visits', 'clients'];

    /**
     * @return array{
     *     chart: string,
     *     title: string,
     *     label: string,
     *     range_days: int,
     *     options: array<string, mixed>
     * }
     */
    public static function forTenant(Tenant $tenant, string $chart, bool $fresh = false): array
    {
        if (! in_array($chart, self::CHARTS, true)) {
            throw new \InvalidArgumentException("Unknown dashboard chart [{$chart}].");
        }

        setCurrentTenant($tenant);

        $cacheKey = self::cacheKey($tenant->id, $chart);

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        /** @var array{
         *     chart: string,
         *     title: string,
         *     label: string,
         *     range_days: int,
         *     options: array<string, mixed>
         * } $payload
         */
        $payload = Cache::flexible(
            $cacheKey,
            [self::FRESH_SECONDS, self::STALE_SECONDS],
            fn (): array => self::compute($tenant, $chart),
        );

        return $payload;
    }

    public static function forget(Tenant $tenant): void
    {
        foreach (self::CHARTS as $chart) {
            Cache::forget(self::cacheKey($tenant->id, $chart));
        }
    }

    private static function cacheKey(int $tenantId, string $chart): string
    {
        return "dashboard.charts.{$tenantId}.{$chart}";
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
    private static function compute(Tenant $tenant, string $chart): array
    {
        setCurrentTenant($tenant);

        $rangeDays = DashboardStats::monthSummaryRangeDays();
        $meta = self::meta($chart);
        $trend = self::trend($tenant, $chart, $rangeDays);

        return [
            'chart' => $chart,
            'title' => $meta['title'],
            'label' => $meta['label'],
            'range_days' => $rangeDays,
            'options' => self::chartOptions($meta['label'], $trend->getData(), $trend->getLabels()),
        ];
    }

    private static function trend(Tenant $tenant, string $chart, int $rangeDays): Result
    {
        return match ($chart) {
            'orders' => Trend::make(Order::query()->withoutGlobalScopes()->where('tenant_id', $tenant->id))
                ->ranges([$rangeDays])
                ->countByDays(),
            'sales' => Trend::make(
                Payment::query()
                    ->withoutGlobalScopes()
                    ->forTenant()
                    ->where('tenant_id', $tenant->id)
            )
                ->ranges([$rangeDays])
                ->sumByDays('amount'),
            'visits' => Trend::make(RequestAnalytics::query()->withoutGlobalScopes()->where('tenant_id', $tenant->id))
                ->dateColumn('visited_at')
                ->ranges([$rangeDays])
                ->countByDays(),
            'clients' => Trend::make(self::clientsQuery($tenant))
                ->ranges([$rangeDays])
                ->countByDays(),
            default => throw new \InvalidArgumentException("Unknown dashboard chart [{$chart}]."),
        };
    }

    /**
     * @return array{title: string, label: string}
     */
    private static function meta(string $chart): array
    {
        return match ($chart) {
            'orders' => ['title' => 'الطلبات', 'label' => 'العدد'],
            'sales' => ['title' => 'المبيعات', 'label' => 'المبيعات'],
            'visits' => ['title' => 'الزيارات', 'label' => 'العدد'],
            'clients' => ['title' => 'العملاء', 'label' => 'العدد'],
            default => throw new \InvalidArgumentException("Unknown dashboard chart [{$chart}]."),
        };
    }

    /**
     * Mirrors App\Traits\HasChartWidget::options().
     *
     * @param  list<float|int>  $data
     * @param  list<string>  $labels
     * @return array<string, mixed>
     */
    private static function chartOptions(string $label, array $data, array $labels): array
    {
        return [
            'type' => 'line',
            'rtl' => true,
            'locale' => 'ar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => $label,
                    'data' => array_map(
                        static fn (float|int $value): float => (float) $value,
                        $data,
                    ),
                    'borderWidth' => 2,
                    'fill' => 'start',
                    'backgroundColor' => '#9FD1F5',
                    'borderColor' => '#36A2EB',
                    'tension' => 0.2,
                ]],
            ],
        ];
    }

    private static function clientsQuery(Tenant $tenant): Builder
    {
        return Client::query()
            ->withoutGlobalScope('tenantable')
            ->whereExists(function ($sub) use ($tenant): void {
                $sub->selectRaw('1')
                    ->from('tenantables')
                    ->whereColumn('tenantables.tenantable_id', 'clients.id')
                    ->where('tenantables.tenantable_type', (new Client)->getMorphClass())
                    ->where('tenantables.tenant_id', $tenant->id);
            });
    }
}
