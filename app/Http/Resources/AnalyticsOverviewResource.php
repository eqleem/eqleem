<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @property-read array{
 *     summary: array{views: int, visitors: int, bounce_rate: string, average_visit_time: string},
 *     chart: array{labels: list<string>, datasets: list<array{label: string, data: list<int>}>},
 *     top_pages: list<array<string, mixed>>,
 *     top_referrers: list<array<string, mixed>>,
 *     browsers: list<array<string, mixed>>,
 *     devices: list<array<string, mixed>>,
 *     countries: list<array<string, mixed>>,
 *     operating_systems: list<array<string, mixed>>,
 *     date_range: array{start: Carbon, end: Carbon, days: int, key: string}
 * } $resource
 */
class AnalyticsOverviewResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
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
         * } $overview
         */
        $overview = $this->resource;

        $dateRange = $overview['date_range'];

        return [
            'summary' => $overview['summary'],
            'chart' => $overview['chart'],
            'top_pages' => $overview['top_pages'],
            'top_referrers' => $overview['top_referrers'],
            'browsers' => $overview['browsers'],
            'devices' => $overview['devices'],
            'countries' => $overview['countries'],
            'operating_systems' => $overview['operating_systems'],
            'date_range' => [
                'start' => $dateRange['start'] instanceof Carbon
                    ? $dateRange['start']->toIso8601String()
                    : $dateRange['start'],
                'end' => $dateRange['end'] instanceof Carbon
                    ? $dateRange['end']->toIso8601String()
                    : $dateRange['end'],
                'days' => $dateRange['days'],
                'key' => $dateRange['key'],
            ],
        ];
    }
}
