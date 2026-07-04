<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Calendar;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;

class CalendarSlotService
{
    /**
     * @return list<string>
     */
    public function availableDates(Calendar $calendar, int $lookaheadDays = 60): array
    {
        $today = now()->startOfDay();
        $start = $calendar->from ? $calendar->from->copy()->startOfDay() : $today->copy();

        if ($start->lt($today)) {
            $start = $today->copy();
        }

        $maxEnd = $today->copy()->addDays($lookaheadDays);

        if ($calendar->forever) {
            $end = $maxEnd;
        } elseif ($calendar->to) {
            $end = $calendar->to->copy()->startOfDay();

            if ($end->gt($maxEnd)) {
                $end = $maxEnd;
            }
        } else {
            $end = $maxEnd;
        }

        if ($end->lt($start)) {
            return [];
        }

        $offDates = collect($calendar->off_dates ?? [])
            ->map(fn (mixed $date): string => Carbon::parse((string) $date)->format('Y-m-d'))
            ->all();

        $dates = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $dateString = $date->format('Y-m-d');

            if (in_array($dateString, $offDates, true)) {
                continue;
            }

            if ($this->dayWindow($calendar, $date) !== null) {
                $dates[] = $dateString;
            }
        }

        return $dates;
    }

    /**
     * @return list<array{start: string, end: string, label: string, start_at: string, end_at: string, available: bool, unavailable_reason: ?string}>
     */
    public function availableTimeSlots(
        Calendar $calendar,
        string $date,
        int $durationMinutes = 60,
        string $mode = 'slot',
    ): array {
        $day = Carbon::parse($date)->startOfDay();
        $window = $this->dayWindow($calendar, $day);

        if ($window === null) {
            return [];
        }

        $windowStart = $day->copy()->setTimeFromTimeString($window['start']);
        $windowEnd = $day->copy()->setTimeFromTimeString($window['end']);

        if ($windowEnd->lte($windowStart)) {
            return [];
        }

        $existingBookings = Booking::query()
            ->where('calendar_id', $calendar->id)
            ->whereDate('start_at', $date)
            ->where('status', '!=', 'cancelled')
            ->get(['start_at', 'end_at']);

        if ($mode === 'day') {
            return $this->buildDaySlot($windowStart, $windowEnd, $existingBookings);
        }

        $durationMinutes = max(1, $durationMinutes);
        $slots = [];
        $slotStart = $windowStart->copy();

        while ($slotStart->copy()->addMinutes($durationMinutes)->lte($windowEnd)) {
            $slotEnd = $slotStart->copy()->addMinutes($durationMinutes);
            $slots[] = $this->buildSlot($slotStart, $slotEnd, $existingBookings);
            $slotStart->addMinutes($durationMinutes);
        }

        return $slots;
    }

    /**
     * @return array{start: string, end: string}|null
     */
    public function dayWindow(Calendar $calendar, Carbon $date): ?array
    {
        $dateString = $date->format('Y-m-d');
        $specialDates = $calendar->special_dates ?? [];

        if (isset($specialDates[$dateString]) && is_array($specialDates[$dateString])) {
            $special = $specialDates[$dateString];

            if (isset($special['start'], $special['end'])) {
                if (! ($special['enabled'] ?? true)) {
                    return null;
                }

                return [
                    'start' => (string) $special['start'],
                    'end' => (string) $special['end'],
                ];
            }
        }

        $weekday = strtolower($date->englishDayOfWeek);
        $availability = Calendar::normalizeAvailabilities($calendar->availabilities)[$weekday] ?? null;

        if ($availability === null || ! ($availability['enabled'] ?? false)) {
            return null;
        }

        return [
            'start' => (string) $availability['start'],
            'end' => (string) $availability['end'],
        ];
    }

    /**
     * @param  Collection<int, Booking>  $existingBookings
     * @return list<array{start: string, end: string, label: string, start_at: string, end_at: string, available: bool, unavailable_reason: ?string}>
     */
    protected function buildDaySlot(Carbon $windowStart, Carbon $windowEnd, $existingBookings): array
    {
        return [$this->buildSlot($windowStart, $windowEnd, $existingBookings)];
    }

    /**
     * @param  Collection<int, Booking>  $existingBookings
     * @return array{start: string, end: string, label: string, start_at: string, end_at: string, available: bool, unavailable_reason: ?string}
     */
    protected function buildSlot(Carbon $slotStart, Carbon $slotEnd, $existingBookings): array
    {
        $unavailableReason = null;

        if ($slotStart->lt(now())) {
            $unavailableReason = 'past';
        } elseif ($this->slotIsBooked($slotStart, $slotEnd, $existingBookings)) {
            $unavailableReason = 'booked';
        }

        return $this->formatSlot($slotStart, $slotEnd, $unavailableReason === null, $unavailableReason);
    }

    /**
     * @param  Collection<int, Booking>  $existingBookings
     */
    protected function slotIsBooked(Carbon $slotStart, Carbon $slotEnd, $existingBookings): bool
    {
        return $existingBookings->contains(function (Booking $booking) use ($slotStart, $slotEnd): bool {
            return $slotStart->lt($booking->end_at) && $slotEnd->gt($booking->start_at);
        });
    }

    /**
     * @return array{start: string, end: string, label: string, start_at: string, end_at: string, available: bool, unavailable_reason: ?string}
     */
    protected function formatSlot(
        Carbon $slotStart,
        Carbon $slotEnd,
        bool $available,
        ?string $unavailableReason = null,
    ): array {
        return [
            'start' => $slotStart->format('H:i'),
            'end' => $slotEnd->format('H:i'),
            'label' => $slotStart->format('H:i').' - '.$slotEnd->format('H:i'),
            'start_at' => $slotStart->toDateTimeString(),
            'end_at' => $slotEnd->toDateTimeString(),
            'available' => $available,
            'unavailable_reason' => $unavailableReason,
        ];
    }
}
