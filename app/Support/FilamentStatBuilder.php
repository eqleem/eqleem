<?php

namespace App\Support;

use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class FilamentStatBuilder
{
    /**
     * @param  Builder<Model>  $query
     */
    public static function count(
        string $label,
        Builder $query,
        int $periodDays = 30,
        string $dateColumn = 'created_at',
        ?string $url = null,
    ): Stat {
        $total = (clone $query)->count();

        return self::make(
            label: $label,
            value: Number::format($total),
            currentPeriodValue: (clone $query)->where($dateColumn, '>=', now()->subDays($periodDays))->count(),
            previousPeriodValue: (clone $query)
                ->where($dateColumn, '>=', now()->subDays($periodDays * 2))
                ->where($dateColumn, '<', now()->subDays($periodDays))
                ->count(),
            chart: self::dailyCounts($query, $periodDays, $dateColumn),
            url: $url,
        );
    }

    /**
     * @param  Builder<Model>  $query
     * @param  callable(int): string  $formatValue
     */
    public static function sum(
        string $label,
        Builder $query,
        string $column,
        callable $formatValue,
        int $periodDays = 30,
        string $dateColumn = 'created_at',
        ?string $url = null,
    ): Stat {
        $total = (int) (clone $query)->sum($column);

        return self::make(
            label: $label,
            value: $formatValue($total),
            currentPeriodValue: (int) (clone $query)->where($dateColumn, '>=', now()->subDays($periodDays))->sum($column),
            previousPeriodValue: (int) (clone $query)
                ->where($dateColumn, '>=', now()->subDays($periodDays * 2))
                ->where($dateColumn, '<', now()->subDays($periodDays))
                ->sum($column),
            chart: self::dailySums($query, $column, $periodDays, $dateColumn),
            url: $url,
        );
    }

    /**
     * @param  array<int, int|float>  $chart
     */
    public static function make(
        string $label,
        string $value,
        int|float $currentPeriodValue,
        int|float $previousPeriodValue,
        array $chart,
        ?string $url = null,
    ): Stat {
        $percentageChange = self::percentageChange($currentPeriodValue, $previousPeriodValue);
        $isIncrease = $percentageChange >= 0;
        $absoluteChange = abs($percentageChange);

        $description = $absoluteChange === 0.0
            ? 'بدون تغيير عن الفترة السابقة'
            : Number::format($absoluteChange, maxPrecision: 1).'% '.($isIncrease ? 'زيادة' : 'انخفاض');

        $stat = Stat::make($label, $value)
            ->description($description)
            ->descriptionIcon(
                $isIncrease ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down',
                IconPosition::Before,
            )
            ->chart($chart)
            ->color($isIncrease ? 'success' : 'danger');

        if (filled($url)) {
            $stat->url($url, shouldOpenInNewTab: false);
        }

        return $stat;
    }

    /**
     * @param  Builder<Model>  $query
     * @return array<int, int>
     */
    public static function dailyCounts(Builder $query, int $periodDays, string $dateColumn = 'created_at'): array
    {
        $startDate = now()->subDays($periodDays - 1)->startOfDay();

        $countsByDate = (clone $query)
            ->where($dateColumn, '>=', $startDate)
            ->get([$dateColumn])
            ->countBy(fn ($model): string => $model->{$dateColumn}->toDateString());

        return collect(range(0, $periodDays - 1))
            ->map(function (int $offset) use ($startDate, $countsByDate): int {
                $date = $startDate->copy()->addDays($offset)->toDateString();

                return (int) ($countsByDate[$date] ?? 0);
            })
            ->all();
    }

    /**
     * @param  Builder<Model>  $query
     * @return array<int, int>
     */
    public static function dailySums(Builder $query, string $column, int $periodDays, string $dateColumn = 'created_at'): array
    {
        $startDate = now()->subDays($periodDays - 1)->startOfDay();

        $sumsByDate = (clone $query)
            ->where($dateColumn, '>=', $startDate)
            ->get([$dateColumn, $column])
            ->groupBy(fn ($model): string => $model->{$dateColumn}->toDateString())
            ->map(fn ($group): int => (int) $group->sum($column));

        return collect(range(0, $periodDays - 1))
            ->map(function (int $offset) use ($startDate, $sumsByDate): int {
                $date = $startDate->copy()->addDays($offset)->toDateString();

                return (int) ($sumsByDate[$date] ?? 0);
            })
            ->all();
    }

    public static function percentageChange(int|float $current, int|float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
