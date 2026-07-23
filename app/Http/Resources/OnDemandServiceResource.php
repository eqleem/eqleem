<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Support\Money;
use App\Support\OnDemandUnit;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full on-demand service payload for the detail editor.
 *
 * @mixin Content
 */
class OnDemandServiceResource extends JsonResource
{
    /**
     * @param  array<string, mixed>  $additional
     */
    public function __construct($resource, protected array $extra = [])
    {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;
        $unitType = (string) data_get($content->data, 'unit_type', OnDemandUnit::SquareMeter);
        $unitLabel = (string) data_get($content->data, 'unit_label', '');

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'subtitle' => (string) data_get($content->data, 'subtitle', ''),
            'slug' => $content->slug,
            'body' => (string) data_get($content->data, 'body', ''),
            'editor_mode' => (string) data_get($content->data, 'editor_mode', 'html'),
            'price' => $this->decimalFromMinor(data_get($content->data, 'price')),
            'compare_price' => $this->decimalFromMinor(data_get($content->data, 'compare_price')),
            'unit_type' => $unitType,
            'unit_label' => $unitLabel,
            'unit_display' => OnDemandUnit::label($unitType, $unitLabel),
            'status' => $content->status,
            'active' => (bool) $content->active,
            'published' => (bool) $content->active,
            'published_at' => $content->published_at?->toIso8601String(),
            'images' => $content->onDemandServiceImages(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'unit_options' => $this->extra['unit_options'] ?? [],
        ];
    }

    private function decimalFromMinor(mixed $minor): string
    {
        if ($minor === null || $minor === '' || (int) $minor === 0) {
            return '';
        }

        return (string) Money::fromMinor((int) $minor);
    }
}
