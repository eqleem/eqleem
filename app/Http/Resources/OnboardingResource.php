<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array<string, mixed> $resource
 */
class OnboardingResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'percentage' => $payload['percentage'],
            'completed_steps' => $payload['completed_steps'],
            'total_steps' => $payload['total_steps'],
            'current_step' => $payload['current_step'],
            'completed' => $payload['completed'],
            'steps' => $payload['steps'],
            'forms' => $payload['forms'],
            'industries' => $payload['industries'],
            'social_networks' => $payload['social_networks'],
            'fonts' => $payload['fonts'],
            'color_options' => $payload['color_options'],
            'radius_options' => $payload['radius_options'],
            'catalog_options' => $payload['catalog_options'],
        ];
    }
}
