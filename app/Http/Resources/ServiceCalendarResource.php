<?php

namespace App\Http\Resources;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Service provider calendar payload for the dashboard.
 *
 * @mixin Calendar
 */
class ServiceCalendarResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Calendar $calendar */
        $calendar = $this->resource;

        return [
            'id' => $calendar->id,
            'name' => $calendar->name,
            'type' => $calendar->type,
            'type_label' => $calendar->type_label,
            'active' => (bool) $calendar->active,
            'from' => $calendar->from?->format('Y-m-d'),
            'to' => $calendar->to?->format('Y-m-d'),
            'from_label' => $calendar->from?->translatedFormat('j F Y'),
            'to_label' => $calendar->to?->translatedFormat('j F Y'),
            'availabilities' => Calendar::normalizeAvailabilities($calendar->availabilities),
            'weekday_labels' => Calendar::weekdayLabels(),
        ];
    }
}
