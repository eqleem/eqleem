<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full portfolio project payload for the detail editor.
 *
 * @mixin Content
 */
class PortfolioProjectResource extends JsonResource
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
            'subtitle' => (string) data_get($content->data, 'subtitle', ''),
            'body' => (string) data_get($content->data, 'body', ''),
            'editor_mode' => (string) data_get($content->data, 'editor_mode', 'html'),
            'status' => $content->status,
            'active' => (bool) $content->active,
            'published' => (bool) $content->active,
            'published_at' => $content->published_at?->toIso8601String(),
            'category_ids' => $content->taxonomiesOfType('portfolio_category')
                ->pluck('id')
                ->map(fn (mixed $id): string => (string) $id)
                ->values()
                ->all(),
            'images' => $content->portfolioImages(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'category_options' => $this->extra['category_options'] ?? [],
        ];
    }
}
