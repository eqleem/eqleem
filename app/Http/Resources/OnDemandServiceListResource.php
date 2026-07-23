<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Models\Media;
use App\Support\OnDemandUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard on-demand services table.
 *
 * @mixin Content
 */
class OnDemandServiceListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;
        $priceMinor = data_get($content->data, 'price');
        $unitType = (string) data_get($content->data, 'unit_type', '');
        $unitLabel = (string) data_get($content->data, 'unit_label', '');

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
            'price_label' => OnDemandUnit::priceLabel($priceMinor, $unitType, $unitLabel),
            'unit_display' => OnDemandUnit::label($unitType, $unitLabel),
            'image' => $this->thumbnailUrl($content),
        ];
    }

    private function thumbnailUrl(Content $content): ?string
    {
        $first = $content->getFirstMedia('on-demand-service-media');

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
