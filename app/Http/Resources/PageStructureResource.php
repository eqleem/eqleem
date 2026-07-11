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
            'cta_block' => isset($payload['cta_block'])
                ? new PageBlockResource($payload['cta_block'])
                : null,
            'user_blocks' => PageBlockResource::collection(collect($payload['user_blocks'] ?? [])),
            'bottom_blocks' => PageBlockResource::collection(collect($payload['bottom_blocks'] ?? [])),
            'float_links_block' => isset($payload['float_links_block'])
                ? new PageBlockResource($payload['float_links_block'])
                : null,
            'block_types' => collect($payload['block_types'] ?? [])->values()->all(),
        ];
    }
}
