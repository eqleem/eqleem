<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array<string, mixed> $resource
 */
class GeneralInfoSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'name' => $payload['name'],
            'logo' => $payload['logo'],
            'contact' => $payload['contact'],
            'social_links' => $payload['social_links'],
            'social_networks' => $payload['social_networks'],
        ];
    }
}
