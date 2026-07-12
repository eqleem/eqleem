<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin array<string, mixed>
 */
class PageBlockResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $block */
        $block = $this->resource;

        $payload = [
            'id' => $block['id'],
            'uuid' => $block['uuid'] ?? null,
            'title' => $block['title'],
            'type' => $block['type'],
            'sort_order' => $block['sort_order'],
            'is_default' => (bool) ($block['is_default'] ?? false),
            'editable' => (bool) ($block['editable'] ?? false),
            'active' => (bool) ($block['active'] ?? true),
            'icon' => $block['icon'] ?? null,
            'icon_url' => $block['icon_url'] ?? null,
            'content_manage_url' => $block['content_manage_url'] ?? null,
            'content_manage_label' => $block['content_manage_label'] ?? null,
        ];

        if (array_key_exists('editor', $block)) {
            $payload['editor'] = $block['editor'];
        }

        return $payload;
    }
}
