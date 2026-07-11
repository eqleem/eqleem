<?php

namespace App\Http\Resources;

use App\Models\Calendar;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Rental-unit calendar payload for the dashboard.
 *
 * @mixin Calendar
 */
class UnitRentalCalendarResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Calendar $calendar */
        $calendar = $this->resource;
        $useBranchHours = (bool) data_get($calendar->meta, 'use_branch_hours', false);

        return [
            'id' => $calendar->id,
            'name' => $calendar->name,
            'type' => $calendar->type,
            'type_label' => $calendar->type_label,
            'active' => (bool) $calendar->active,
            'use_branch_hours' => $useBranchHours,
            'hours_mode_label' => $useBranchHours ? 'ساعات عمل الفرع' : 'مخصصة',
            'from' => $calendar->from?->format('Y-m-d'),
            'to' => $calendar->to?->format('Y-m-d'),
            'from_label' => $calendar->from?->translatedFormat('j F Y'),
            'to_label' => $calendar->to?->translatedFormat('j F Y'),
            'availabilities' => Calendar::normalizeAvailabilities($calendar->availabilities),
            'weekday_labels' => Calendar::weekdayLabels(),
        ];
    }
}
