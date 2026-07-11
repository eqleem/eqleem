<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array<string, mixed> $resource
 */
class LanguageCurrencySettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'default_language' => $payload['default_language'],
            'default_currency' => $payload['default_currency'],
            'available_languages' => $payload['available_languages'],
            'available_currencies' => $payload['available_currencies'],
            'languages' => $payload['languages'],
            'currencies' => $payload['currencies'],
        ];
    }
}
