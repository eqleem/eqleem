<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full blog post payload for the detail editor.
 *
 * @mixin Content
 */
class BlogPostResource extends JsonResource
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
        $imagePath = data_get($content->data, 'image');

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'subtitle' => (string) data_get($content->data, 'subtitle', ''),
            'slug' => $content->slug,
            'body' => (string) data_get($content->data, 'body', ''),
            'editor_mode' => (string) data_get($content->data, 'editor_mode', 'html'),
            'featured_image' => filled($imagePath) ? (contentImageUrl((string) $imagePath) ?? (string) $imagePath) : null,
            'status' => $content->status,
            'published' => $content->status === 'published',
            'published_at' => $content->published_at?->toIso8601String(),
            'category_ids' => $content->taxonomiesOfType('blog_category')
                ->pluck('id')
                ->map(fn (mixed $id): string => (string) $id)
                ->values()
                ->all(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'category_options' => $this->extra['category_options'] ?? [],
        ];
    }
}
