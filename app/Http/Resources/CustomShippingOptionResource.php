<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array<string, mixed> $resource
 */
class CustomShippingOptionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'id' => $payload['id'],
            'name' => $payload['name'],
            'price' => $payload['price'],
            'country' => $payload['country'],
            'all_cities' => (bool) ($payload['all_cities'] ?? false),
            'city_ids' => $payload['city_ids'] ?? [],
            'active' => (bool) ($payload['active'] ?? true),
        ];
    }
}
