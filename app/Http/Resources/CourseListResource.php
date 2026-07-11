<?php

namespace App\Http\Resources;

use App\Models\Content;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lean payload for the dashboard courses table.
 *
 * @mixin Content
 */
class CourseListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Content $content */
        $content = $this->resource;
        $priceMinor = data_get($content->data, 'price');
        $hours = (float) data_get($content->data, 'hours', 0);
        $lessonsCount = $content->courseLessonCount();

        return [
            'id' => $content->id,
            'uuid' => $content->uuid,
            'title' => $content->title,
            'slug' => $content->slug,
            'status' => $content->status,
            'status_label' => $content->status === 'published' ? 'منشور' : 'مسودة',
            'published_at' => $content->published_at?->toIso8601String(),
            'published_at_label' => $content->published_at?->translatedFormat('j F Y'),
            'price_label' => filled($priceMinor) && (int) $priceMinor > 0
                ? money_format_plain((int) $priceMinor)
                : null,
            'level_label' => $content->courseLevelLabel(),
            'hours_label' => $hours > 0 ? (string) $hours : null,
            'lessons_count' => $lessonsCount,
            'image' => $this->thumbnailUrl($content),
        ];
    }

    private function thumbnailUrl(Content $content): ?string
    {
        $first = $content->getFirstMedia('course-media');

        if ($first instanceof Media) {
            return $first->getUrl();
        }

        return $content->avatar ?? null;
    }
}
