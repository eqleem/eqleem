<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard blog posts table.
 *
 * @mixin Content
 */
class BlogPostListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;
        $imagePath = data_get($content->data, 'image');

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'slug' => $content->slug,
            'active' => (bool) $content->active,
            'status' => $content->status,
            'status_label' => $content->active ? 'مفعّل' : 'معطّل',
            'published' => (bool) $content->active,
            'published_at' => $content->published_at?->toIso8601String(),
            'published_at_label' => $content->published_at?->translatedFormat('j F Y'),
            'image' => filled($imagePath) ? (contentImageUrl((string) $imagePath) ?? (string) $imagePath) : null,
        ];
    }
}
