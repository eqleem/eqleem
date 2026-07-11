<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read array<string, mixed> $resource
 */
class WelcomeWidgetResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'greeting' => $payload['greeting'],
            'user_name' => $payload['user_name'],
            'page_url' => $payload['page_url'],
            'share_text' => $payload['share_text'],
            'percentage' => $payload['percentage'],
            'completed_steps' => $payload['completed_steps'],
            'total_steps' => $payload['total_steps'],
            'steps' => $payload['steps'],
            'next_step' => $payload['next_step'],
            'forms' => $payload['forms'],
        ];
    }
}
