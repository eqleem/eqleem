<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array<string, mixed>
 */
class PlanCatalogResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'billing_period' => $payload['billing_period'] ?? 'monthly',
            'current_plan_id' => $payload['current_plan_id'] ?? null,
            'app_name' => $payload['app_name'] ?? config('app.name'),
            'plans' => collect($payload['plans'] ?? [])->values()->all(),
            'faqs' => collect($payload['faqs'] ?? [])->values()->all(),
        ];
    }
}
