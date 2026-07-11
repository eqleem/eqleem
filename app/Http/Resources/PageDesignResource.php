<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array<string, mixed>
 */
class PageDesignResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'themes' => collect($payload['themes'] ?? [])->values()->all(),
            'selected_theme_id' => $payload['selected_theme_id'] ?? null,
            'tenant_theme_id' => $payload['tenant_theme_id'] ?? null,
            'selected_theme' => $payload['selected_theme'] ?? null,
            'options_schema' => $payload['options_schema'] ?? (object) [],
            'options' => $payload['options'] ?? (object) [],
            'option_previews' => $payload['option_previews'] ?? (object) [],
        ];
    }
}
