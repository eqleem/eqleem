<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array{
 *     chart: string,
 *     title: string,
 *     label: string,
 *     range_days: int,
 *     options: array<string, mixed>
 * } $resource
 */
class DashboardChartResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{
         *     chart: string,
         *     title: string,
         *     label: string,
         *     range_days: int,
         *     options: array<string, mixed>
         * } $chart
         */
        $chart = $this->resource;

        return [
            'chart' => $chart['chart'],
            'title' => $chart['title'],
            'label' => $chart['label'],
            'range_days' => $chart['range_days'],
            'options' => $chart['options'],
        ];
    }
}
