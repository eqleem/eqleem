<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array<string, mixed> $resource
 */
class ShippingMethodResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'slug' => $payload['slug'],
            'name' => $payload['name'],
            'description' => $payload['description'],
            'icon' => $payload['icon'],
            'icon_url' => $payload['icon_url'] ?? null,
            'active' => (bool) ($payload['active'] ?? false),
            'settings' => $payload['settings'] ?? [],
            'order' => $payload['order'] ?? 0,
        ];
    }
}
