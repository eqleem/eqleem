<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array<string, mixed> $resource
 */
class VerificationSettingsResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'identity_type' => $payload['identity_type'],
            'identity_number' => $payload['identity_number'],
            'country' => $payload['country'],
            'identity_file' => $payload['identity_file'],
            'identity_file_url' => $payload['identity_file_url'],
            'is_confirmed' => $payload['is_confirmed'],
            'confirm_status' => $payload['confirm_status'],
            'types' => $payload['types'],
            'countries' => $payload['countries'],
        ];
    }
}
