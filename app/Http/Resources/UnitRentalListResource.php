<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard unit rental table.
 *
 * @mixin Content
 */
class UnitRentalListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;
        $priceMinor = data_get($content->data, 'price');

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
            'price_label' => filled($priceMinor) && (int) $priceMinor > 0
                ? money_format_plain((int) $priceMinor)
                : null,
            'image' => $this->thumbnailUrl($content),
        ];
    }

    private function thumbnailUrl(Content $content): ?string
    {
        $first = $content->getFirstMedia('unit-media');

        if ($first instanceof Media) {
            return $first->getUrl();
        }

        $legacy = data_get($content->data, 'images.0') ?? data_get($content->data, 'image');

        if (filled($legacy)) {
            return contentImageUrl((string) $legacy) ?? (string) $legacy;
        }

        return $content->avatar ?? null;
    }
}
