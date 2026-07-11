<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full unit rental payload for the detail editor.
 *
 * @mixin Content
 */
class UnitRentalResource extends JsonResource
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

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'subtitle' => (string) data_get($content->data, 'subtitle', ''),
            'slug' => $content->slug,
            'body' => (string) data_get($content->data, 'body', ''),
            'editor_mode' => (string) data_get($content->data, 'editor_mode', 'html'),
            'price' => $this->decimalFromMinor(data_get($content->data, 'price')),
            'status' => $content->status,
            'published' => $content->status === 'published',
            'published_at' => $content->published_at?->toIso8601String(),
            'category_ids' => $content->taxonomiesOfType('unit_category')
                ->pluck('id')
                ->map(fn (mixed $id): string => (string) $id)
                ->values()
                ->all(),
            'calendar_ids' => $content->calendars()
                ->where('calendars.type', 'rental-unit')
                ->pluck('calendars.id')
                ->map(fn (mixed $id): string => (string) $id)
                ->values()
                ->all(),
            'images' => $content->unitImages(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'category_options' => $this->extra['category_options'] ?? [],
            'calendar_options' => $this->extra['calendar_options'] ?? [],
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
