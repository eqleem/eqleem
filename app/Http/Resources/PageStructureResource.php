<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array<string, mixed>
 */
class PageStructureResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $payload */
        $payload = $this->resource;

        return [
            'top_blocks' => PageBlockResource::collection(collect($payload['top_blocks'] ?? [])),
            'user_blocks' => PageBlockResource::collection(collect($payload['user_blocks'] ?? [])),
            'bottom_blocks' => PageBlockResource::collection(collect($payload['bottom_blocks'] ?? [])),
            'block_types' => collect($payload['block_types'] ?? [])->values()->all(),
        ];
    }
}
