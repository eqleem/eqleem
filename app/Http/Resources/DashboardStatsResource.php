<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array{
 *     range_days: int,
 *     orders: array{value: int, growth: int},
 *     sales: array{value: int, growth: int, value_formatted: string, currency: string},
 *     visits: array{value: int, growth: int},
 *     clients: array{value: int, growth: int}
 * } $resource
 */
class DashboardStatsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{
         *     range_days: int,
         *     orders: array{value: int, growth: int},
         *     sales: array{value: int, growth: int, value_formatted: string, currency: string},
         *     visits: array{value: int, growth: int},
         *     clients: array{value: int, growth: int}
         * } $stats
         */
        $stats = $this->resource;

        return [
            'range_days' => $stats['range_days'],
            'orders' => $stats['orders'],
            'sales' => $stats['sales'],
            'visits' => $stats['visits'],
            'clients' => $stats['clients'],
        ];
    }
}
