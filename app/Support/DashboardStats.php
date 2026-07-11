<?php

namespace App\Support;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Models\RequestAnalytics;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use SaKanjo\EasyMetrics\Metrics\Value;
use SaKanjo\EasyMetrics\ValueResult;

/**
 * Month-summary dashboard widgets (orders, sales, visits, clients).
 *
 * EasyMetrics for value + growth; Cache::flexible (3–6 min) so the home page
 * rarely hits the DB. Call forget() after writes to refresh early.
 */
class DashboardStats
{
    public const FRESH_SECONDS = 180;

    public const STALE_SECONDS = 360;

    /** @var list<string> */
    public const METRICS = ['orders', 'sales', 'visits', 'clients'];

    /**
     * @return array{
     *     range_days: int,
     *     orders: array{value: int, growth: int},
     *     sales: array{value: int, growth: int, value_formatted: string, currency: string},
     *     visits: array{value: int, growth: int},
     *     clients: array{value: int, growth: int}
     * }
     */
    public static function forTenant(Tenant $tenant, bool $fresh = false): array
    {
        setCurrentTenant($tenant);

        $cacheKey = self::cacheKey($tenant->id);

        if ($fresh) {
            Cache::forget($cacheKey);
        }

        /** @var array{
         *     range_days: int,
         *     orders: array{value: int, growth: int},
         *     sales: array{value: int, growth: int, value_formatted: string, currency: string},
         *     visits: array{value: int, growth: int},
         *     clients: array{value: int, growth: int}
         * } $stats
         */
        $stats = Cache::flexible(
            $cacheKey,
            [self::FRESH_SECONDS, self::STALE_SECONDS],
            fn (): array => self::compute($tenant),
        );

        return $stats;
    }

    /**
     * @return array{value: int, growth: int}|array{value: int, growth: int, value_formatted: string, currency: string}
     */
    public static function metricForTenant(Tenant $tenant, string $metric, bool $fresh = false): array
    {
        if (! in_array($metric, self::METRICS, true)) {
            throw new \InvalidArgumentException("Unknown dashboard metric [{$metric}].");
        }

        $stats = self::forTenant($tenant, $fresh);

        /** @var array{value: int, growth: int}|array{value: int, growth: int, value_formatted: string, currency: string} */
        return $stats[$metric];
    }

    public static function forget(Tenant $tenant): void
    {
        Cache::forget(self::cacheKey($tenant->id));
        DashboardCharts::forget($tenant);
    }

    public static function monthSummaryRangeDays(): int
    {
        $from = now()->copy()->startOfMonth()->subDays(3);

        return max(1, (int) now()->diffInDays($from, true));
    }

    private static function cacheKey(int $tenantId): string
    {
        return "dashboard.stats.{$tenantId}";
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
    private static function compute(Tenant $tenant): array
    {
        setCurrentTenant($tenant);

        $rangeDays = self::monthSummaryRangeDays();

        [$ordersValue, $ordersGrowth] = self::metricPair(
            Value::make(Order::query()->withoutGlobalScopes()->where('tenant_id', $tenant->id))
                ->withGrowthRate()
                ->ranges([$rangeDays])
                ->count()
        );

        [$salesValue, $salesGrowth] = self::metricPair(
            Value::make(
                Payment::query()
                    ->withoutGlobalScopes()
                    ->forTenant()
                    ->where('tenant_id', $tenant->id)
            )
                ->withGrowthRate()
                ->ranges([$rangeDays])
                ->sum('amount')
        );

        [$visitsValue, $visitsGrowth] = self::metricPair(
            Value::make(RequestAnalytics::query()->withoutGlobalScopes()->where('tenant_id', $tenant->id))
                ->dateColumn('visited_at')
                ->withGrowthRate()
                ->ranges([$rangeDays])
                ->count()
        );

        [$clientsValue, $clientsGrowth] = self::metricPair(
            Value::make(self::clientsQuery($tenant))
                ->withGrowthRate()
                ->ranges([$rangeDays])
                ->count()
        );

        $salesMinor = (int) $salesValue;
        $currency = Money::defaultCurrencyCode();

        return [
            'range_days' => $rangeDays,
            'orders' => [
                'value' => (int) $ordersValue,
                'growth' => (int) round($ordersGrowth),
            ],
            'sales' => [
                'value' => $salesMinor,
                'growth' => (int) round($salesGrowth),
                'value_formatted' => Money::formatWithCurrency($salesMinor, $currency),
                'currency' => $currency,
            ],
            'visits' => [
                'value' => (int) $visitsValue,
                'growth' => (int) round($visitsGrowth),
            ],
            'clients' => [
                'value' => (int) $clientsValue,
                'growth' => (int) round($clientsGrowth),
            ],
        ];
    }

    /**
     * @return array{0: float, 1: float}
     */
    private static function metricPair(float|ValueResult $result): array
    {
        if ($result instanceof ValueResult) {
            return [
                $result->getValue(),
                (float) ($result->getGrowthRate() ?? 0),
            ];
        }

        return [$result, 0.0];
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
