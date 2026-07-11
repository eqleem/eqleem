<?php

namespace App\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard forms table.
 *
 * @mixin Content
 */
class FormListResource extends JsonResource
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
            'avatar' => $content->avatar,
            'active' => (bool) $content->active,
            'status' => $content->status,
            'status_label' => $content->status_label,
            'form_submissions_count' => (int) ($content->form_submissions_count ?? 0),
            'updated_at' => $content->updated_at?->toIso8601String(),
            'updated_at_label' => $content->updated_at?->translatedFormat('d M Y'),
        ];
    }
}
