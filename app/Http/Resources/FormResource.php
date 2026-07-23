<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Support\FormField;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full form payload for the detail editor.
 *
 * @mixin Content
 */
class FormResource extends JsonResource
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
            'description' => (string) data_get($content->data, 'description', ''),
            'fields' => FormField::normalize(data_get($content->data, 'fields')),
            'slug' => $content->slug,
            'status' => $content->status,
            'active' => (bool) $content->active,
            'published' => (bool) $content->active,
            'published_at' => $content->published_at?->toIso8601String(),
            'submit_label' => (string) data_get($content->data, 'submit_label', 'إرسال'),
            'success_message' => (string) data_get($content->data, 'success_message', ''),
            'slug_prefix' => $this->extra['slug_prefix'] ?? null,
            'field_type_options' => $this->extra['field_type_options'] ?? FormField::typeOptions(),
        ];
    }
}
