<?php

namespace App\Support;

use App\Models\Branch;
use App\Models\Calendar;
use App\Services\CalendarSlotService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CtaBooking
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{
     *     branch_ids: list<int>,
     *     calendar_ids: list<int>,
     *     allow_client_choice: bool,
     *     duration_minutes: int
     * }
     */
    public static function configFromData(array $data): array
    {
        return [
            'branch_ids' => self::normalizeIds($data['branch_ids'] ?? []),
            'calendar_ids' => self::normalizeIds($data['calendar_ids'] ?? []),
            'allow_client_choice' => (bool) ($data['allow_client_choice'] ?? true),
            'duration_minutes' => max(1, (int) ($data['duration_minutes'] ?? 30)),
        ];
    }

    /**
     * @param  list<int>  $branchIds
     * @param  list<int>  $calendarIds
     * @return Collection<int, Calendar>
     */
    public static function resolveCalendars(array $branchIds, array $calendarIds): Collection
    {
        $branchIds = self::normalizeIds($branchIds);
        $calendarIds = self::normalizeIds($calendarIds);

        if ($branchIds === [] && $calendarIds === []) {
            return collect();
        }

        return Calendar::query()
            ->with('branch:id,name,city')
            ->where('active', true)
            ->where(function ($query) use ($branchIds, $calendarIds): void {
                if ($calendarIds !== []) {
                    $query->whereIn('id', $calendarIds);
                }

                if ($branchIds !== []) {
                    $method = $calendarIds !== [] ? 'orWhereIn' : 'whereIn';
                    $query->{$method}('branch_id', $branchIds);
                }
            })
            ->orderBy('name')
            ->get();
    }

    /**
     * @return list<array{id: int, name: string, city: string|null}>
     */
    public static function branchOptions(): array
    {
        return Branch::query()
            ->where('active', true)
            ->orderBy('order')
            ->orderBy('id')
            ->get(['id', 'name', 'city'])
            ->map(fn (Branch $branch): array => [
                'id' => (int) $branch->id,
                'name' => $branch->display_name,
                'city' => $branch->city,
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{id: int, name: string, type: string, type_label: string, branch_id: int|null, branch_name: string|null}>
     */
    public static function calendarOptions(): array
    {
        return Calendar::query()
            ->with('branch:id,name')
            ->where('active', true)
            ->orderBy('name')
            ->get()
            ->map(fn (Calendar $calendar): array => [
                'id' => (int) $calendar->id,
                'name' => $calendar->name,
                'type' => (string) $calendar->type,
                'type_label' => Calendar::typeOptions()[$calendar->type] ?? (string) $calendar->type,
                'branch_id' => $calendar->branch_id ? (int) $calendar->branch_id : null,
                'branch_name' => $calendar->branch?->display_name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Calendar>  $calendars
     * @return list<array{
     *     calendar_id: int,
     *     calendar_name: string,
     *     branch_id: int|null,
     *     branch_name: string|null,
     *     start: string,
     *     end: string,
     *     label: string,
     *     start_at: string,
     *     end_at: string,
     *     date: string,
     *     date_label: string
     * }>
     */
    public static function nearestSlots(Collection $calendars, int $durationMinutes, int $limit = 12): array
    {
        if ($calendars->isEmpty()) {
            return [];
        }

        $slotService = app(CalendarSlotService::class);
        $durationMinutes = max(1, $durationMinutes);
        $candidates = [];

        foreach ($calendars as $calendar) {
            $dates = $slotService->availableDates($calendar);
            $foundForCalendar = 0;

            foreach ($dates as $date) {
                if ($foundForCalendar >= $limit) {
                    break;
                }

                $slots = $slotService->availableTimeSlots($calendar, $date, $durationMinutes, 'slot');

                foreach ($slots as $slot) {
                    if (! ($slot['available'] ?? false)) {
                        continue;
                    }

                    $startAt = (string) ($slot['start_at'] ?? '');

                    if ($startAt === '' || $startAt < now()->toDateTimeString()) {
                        continue;
                    }

                    $candidates[] = [
                        'calendar_id' => (int) $calendar->id,
                        'calendar_name' => $calendar->name,
                        'branch_id' => $calendar->branch_id ? (int) $calendar->branch_id : null,
                        'branch_name' => $calendar->branch?->display_name,
                        'start' => (string) ($slot['start'] ?? ''),
                        'end' => (string) ($slot['end'] ?? ''),
                        'label' => (string) ($slot['label'] ?? ''),
                        'start_at' => $startAt,
                        'end_at' => (string) ($slot['end_at'] ?? ''),
                        'date' => $date,
                        'date_label' => Carbon::parse($date)->translatedFormat('l j F Y'),
                    ];

                    $foundForCalendar++;

                    if ($foundForCalendar >= $limit) {
                        break;
                    }
                }
            }
        }

        usort($candidates, fn (array $a, array $b): int => strcmp($a['start_at'], $b['start_at']));

        return array_values(array_slice($candidates, 0, $limit));
    }

    /**
     * @param  Collection<int, Calendar>  $calendars
     * @return list<array{
     *     calendar_id: int,
     *     calendar_name: string,
     *     branch_id: int|null,
     *     branch_name: string|null,
     *     start: string,
     *     end: string,
     *     label: string,
     *     start_at: string,
     *     end_at: string,
     *     date: string,
     *     date_label: string,
     *     available: bool,
     *     unavailable_reason: string|null
     * }>
     */
    public static function slotsForDate(Collection $calendars, string $date, int $durationMinutes): array
    {
        if ($calendars->isEmpty() || $date === '') {
            return [];
        }

        $slotService = app(CalendarSlotService::class);
        $durationMinutes = max(1, $durationMinutes);
        $candidates = [];

        foreach ($calendars as $calendar) {
            $slots = $slotService->availableTimeSlots($calendar, $date, $durationMinutes, 'slot');

            foreach ($slots as $slot) {
                $startAt = (string) ($slot['start_at'] ?? '');

                if ($startAt === '') {
                    continue;
                }

                $candidates[] = [
                    'calendar_id' => (int) $calendar->id,
                    'calendar_name' => $calendar->name,
                    'branch_id' => $calendar->branch_id ? (int) $calendar->branch_id : null,
                    'branch_name' => $calendar->branch?->display_name,
                    'start' => (string) ($slot['start'] ?? ''),
                    'end' => (string) ($slot['end'] ?? ''),
                    'label' => (string) ($slot['label'] ?? ''),
                    'start_at' => $startAt,
                    'end_at' => (string) ($slot['end_at'] ?? ''),
                    'date' => $date,
                    'date_label' => Carbon::parse($date)->translatedFormat('l j F Y'),
                    'available' => (bool) ($slot['available'] ?? false),
                    'unavailable_reason' => isset($slot['unavailable_reason'])
                        ? (string) $slot['unavailable_reason']
                        : null,
                ];
            }
        }

        usort($candidates, fn (array $a, array $b): int => strcmp($a['start_at'], $b['start_at']));

        return array_values($candidates);
    }

    /**
     * @param  Collection<int, Calendar>  $calendars
     * @return list<string>
     */
    public static function availableDates(Collection $calendars, int $lookaheadDays = 60): array
    {
        if ($calendars->isEmpty()) {
            return [];
        }

        $slotService = app(CalendarSlotService::class);
        $dates = [];

        foreach ($calendars as $calendar) {
            foreach ($slotService->availableDates($calendar, $lookaheadDays) as $date) {
                $dates[$date] = true;
            }
        }

        $sorted = array_keys($dates);
        sort($sorted);

        return $sorted;
    }

    /**
     * @return list<int>
     */
    public static function normalizeIds(mixed $ids): array
    {
        if (! is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
