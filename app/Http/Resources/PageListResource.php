<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard pages table.
 *
 * @mixin Content
 */
class PageListResource extends JsonResource
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
            'template' => $content->template,
            'template_label' => filled($content->template)
                ? (Content::pageTemplateOptions()[$content->template] ?? $content->template)
                : null,
            'is_system_page' => $content->isSystemPage(),
            'active' => (bool) $content->active,
            'status' => $content->status,
            'status_label' => $content->status_label,
            'published_at' => $content->published_at?->toIso8601String(),
            'published_at_label' => $content->published_at?->translatedFormat('d M Y'),
            'image' => filled($imagePath) ? (contentImageUrl((string) $imagePath) ?? (string) $imagePath) : null,
            'avatar' => $content->avatar,
        ];
    }
}
