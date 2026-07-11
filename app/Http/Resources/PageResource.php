<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full page payload for the detail editor.
 *
 * @mixin Content
 */
class PageResource extends JsonResource
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
            'body' => (string) data_get($content->data, 'body', ''),
            'editor_mode' => (string) data_get($content->data, 'editor_mode', 'html'),
            'slug' => $content->slug,
            'template' => $content->template,
            'template_label' => filled($content->template)
                ? (Content::pageTemplateOptions()[$content->template] ?? $content->template)
                : null,
            'is_system_page' => $content->isSystemPage(),
            'active' => (bool) $content->active,
            'status' => $content->status,
            'published' => $content->status === 'published',
            'published_at' => $content->published_at?->toIso8601String(),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
        ];
    }
}
