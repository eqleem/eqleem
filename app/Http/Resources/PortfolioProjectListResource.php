<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard portfolio projects table.
 *
 * @mixin Content
 */
class PortfolioProjectListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'slug' => $content->slug,
            'status' => $content->status,
            'published_at' => $content->published_at?->toIso8601String(),
            'published_at_label' => $content->published_at?->translatedFormat('j F Y'),
            'image' => $this->thumbnailUrl($content),
        ];
    }

    private function thumbnailUrl(Content $content): ?string
    {
        $first = $content->getFirstMedia('portfolio-media');

        if ($first instanceof Media) {
            return $first->getUrl();
        }

        $legacy = data_get($content->data, 'images.0') ?? data_get($content->data, 'image');

        if (filled($legacy)) {
            return contentImageUrl((string) $legacy) ?? (string) $legacy;
        }

        return null;
    }
}
