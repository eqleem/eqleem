<?php

namespace App\API\Bookings;

use App\API\Concerns\AuthorizesDashboardTenant;
use App\Models\Booking;
use App\Models\Calendar;
use App\Models\Content;
use App\Models\Order;
use App\Models\Tenant;
use App\Services\CalendarSlotService;
use App\Support\Money;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Returns calendars / available dates / time slots for booking creation.
 */
class GetBookingAvailability
{
    use AsAction;
    use AuthorizesDashboardTenant;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'content_id' => ['required', 'integer'],
            'calendar_id' => ['sometimes', 'nullable', 'integer'],
            'date' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
        ];
    }

    /**
     * @return array{
     *     content: array{id: int, title: string, type: string, type_label: string, unit_price: float, duration_minutes: int},
     *     calendars: list<array{id: int, name: string, type: string, type_label: string}>,
     *     available_dates: list<string>,
     *     booked_ranges: list<array{start_at: string, end_at: string, start_date: string, end_date: string}>,
     *     time_slots: list<array{start: string, end: string, label: string, start_at: string, end_at: string, available: bool, unavailable_reason: ?string}>
     * }
     */
    public function handle(Tenant $tenant, int $contentId, ?int $calendarId = null, ?string $date = null): array
    {
        setCurrentTenant($tenant);

        $content = Content::query()
            ->whereKey($contentId)
            ->where('tenant_id', $tenant->id)
            ->first();

        if (! $content instanceof Content || ! Order::isBookingItemType($content->orderItemType())) {
            throw ValidationException::withMessages([
                'content_id' => [__('The selected content is invalid.')],
            ]);
        }

        $type = $content->orderItemType();
        $priceMinor = (int) (data_get($content->data, 'price') ?: $content->price ?: 0);
        $durationMinutes = max(1, (int) data_get($content->data, 'duration_minutes', 60));

        $calendars = $content->calendars()
            ->where('calendars.active', true)
            ->orderBy('calendars.name')
            ->get(['calendars.id', 'calendars.name', 'calendars.type'])
            ->map(fn (Calendar $calendar): array => [
                'id' => (int) $calendar->id,
                'name' => $calendar->name,
                'type' => (string) $calendar->type,
                'type_label' => Calendar::typeOptions()[$calendar->type] ?? (string) $calendar->type,
            ])
            ->values()
            ->all();

        $availableDates = [];
        $bookedRanges = [];
        $timeSlots = [];

        if ($calendarId !== null && $calendarId > 0) {
            $calendar = $content->calendars()
                ->where('calendars.id', $calendarId)
                ->where('calendars.active', true)
                ->first();

            if (! $calendar instanceof Calendar) {
                throw ValidationException::withMessages([
                    'calendar_id' => [__('The selected calendar is invalid.')],
                ]);
            }

            $slotService = app(CalendarSlotService::class);
            $availableDates = $slotService->availableDates($calendar);

            $bookedRanges = Booking::query()
                ->where('calendar_id', $calendar->id)
                ->where('status', '!=', 'cancelled')
                ->where('end_at', '>', now()->startOfDay())
                ->orderBy('start_at')
                ->get(['start_at', 'end_at'])
                ->map(fn (Booking $booking): array => [
                    'start_at' => $booking->start_at->toDateTimeString(),
                    'end_at' => $booking->end_at->toDateTimeString(),
                    'start_date' => $booking->start_at->toDateString(),
                    'end_date' => $booking->end_at->toDateString(),
                ])
                ->values()
                ->all();

            if (filled($date)) {
                $mode = $type === 'unit_rental' ? 'day' : 'slot';
                $timeSlots = $slotService->availableTimeSlots(
                    $calendar,
                    $date,
                    $durationMinutes,
                    $mode,
                );
            }
        }

        return [
            'content' => [
                'id' => $content->id,
                'title' => $content->title,
                'type' => $type,
                'type_label' => Order::itemTypeOptions()[$type] ?? $type,
                'unit_price' => Money::fromMinor($priceMinor),
                'duration_minutes' => $durationMinutes,
            ],
            'calendars' => $calendars,
            'available_dates' => $availableDates,
            'booked_ranges' => $bookedRanges,
            'time_slots' => $timeSlots,
            'selected_date' => $date,
            'selected_calendar_id' => $calendarId,
        ];
    }

    /**
     * @return array{
     *     content: array{id: int, title: string, type: string, type_label: string, unit_price: float, duration_minutes: int},
     *     calendars: list<array{id: int, name: string, type: string, type_label: string}>,
     *     available_dates: list<string>,
     *     time_slots: list<array{start: string, end: string, label: string, start_at: string, end_at: string, available: bool, unavailable_reason: ?string}>
     * }
     */
    public function asController(ActionRequest $request): array
    {
        /** @var array{content_id: int, calendar_id?: int|null, date?: string|null} $validated */
        $validated = $request->validated();

        return $this->handle(
            $this->currentDashboardTenant($request),
            (int) $validated['content_id'],
            isset($validated['calendar_id']) ? (int) $validated['calendar_id'] : null,
            isset($validated['date']) && filled($validated['date']) ? (string) $validated['date'] : null,
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{data: array<string, mixed>}
     */
    public function jsonResponse(array $payload): array
    {
        return ['data' => $payload];
    }
}
