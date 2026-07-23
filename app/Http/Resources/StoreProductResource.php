<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full store product payload for the detail editor.
 *
 * @mixin Content
 */
class StoreProductResource extends JsonResource
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
            'slug' => $content->slug,
            'body' => (string) data_get($content->data, 'body', ''),
            'editor_mode' => (string) data_get($content->data, 'editor_mode', 'html'),
            'price' => $this->decimalFromMinor(data_get($content->data, 'price')),
            'compare_price' => $this->decimalFromMinor(data_get($content->data, 'compare_price')),
            'weight' => (string) (data_get($content->data, 'weight') ?? ''),
            'currency_code' => Money::defaultCurrencyCode(),
            'currency_symbol' => Money::symbolFor(),
            'status' => $content->status,
            'active' => (bool) $content->active,
            'published' => (bool) $content->active,
            'published_at' => $content->published_at?->toIso8601String(),
            'category_ids' => $content->taxonomiesOfType('store_category')
                ->pluck('id')
                ->map(fn (mixed $id): string => (string) $id)
                ->values()
                ->all(),
            'images' => $content->storeImages(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'category_options' => $this->extra['category_options'] ?? [],
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
