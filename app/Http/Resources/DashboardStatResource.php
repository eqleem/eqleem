<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array{metric: string, value: int, growth: int, value_formatted?: string, currency?: string} $resource
 */
class DashboardStatResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array{metric: string, value: int, growth: int, value_formatted?: string, currency?: string} $stat */
        $stat = $this->resource;

        return array_filter([
            'metric' => $stat['metric'],
            'value' => $stat['value'],
            'growth' => $stat['growth'],
            'value_formatted' => $stat['value_formatted'] ?? null,
            'currency' => $stat['currency'] ?? null,
        ], fn (mixed $value): bool => $value !== null);
    }
}
