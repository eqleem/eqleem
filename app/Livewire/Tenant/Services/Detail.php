<?php

namespace App\Livewire\Tenant\Services;

use App\Models\Calendar;
use App\Models\Content;
use App\Services\CalendarSlotService;
use App\Services\CartService;
use Livewire\Component;

class Detail extends Component
{
    public Content $service;

    public bool $addedToCart = false;

    /** @var array<int, array{id: int, name: string}> */
    public array $calendars = [];

    public ?int $calendarId = null;

    /** @var list<string> */
    public array $availableDates = [];

    public string $bookingDate = '';

    /** @var list<array{start: string, end: string, label: string, start_at: string, end_at: string, available: bool, unavailable_reason: ?string}> */
    public array $timeSlots = [];

    public ?string $bookingStartAt = null;

    public ?string $bookingEndAt = null;

    public function mount(string $slug): void
    {
        $this->service = Content::query()
            ->type(contentTypeModel('services'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->firstOrFail();

        $this->loadCalendars();
    }

    public function openBookingModal(): void
    {
        $this->addedToCart = false;
        $this->resetBookingSelection();
        $this->loadCalendars();
        $this->dispatch('open-modal', name: 'service-booking-modal');
    }

    public function updatedCalendarId(): void
    {
        $this->loadBookingAvailability();
    }

    public function updatedBookingDate(): void
    {
        $this->loadBookingTimeSlots();
    }

    public function selectTimeSlot(string $startAt, string $endAt): void
    {
        $slot = collect($this->timeSlots)
            ->first(fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $startAt
                && ($candidate['end_at'] ?? '') === $endAt);

        if (! is_array($slot) || ! ($slot['available'] ?? false)) {
            return;
        }

        $this->bookingStartAt = $startAt;
        $this->bookingEndAt = $endAt;
    }

    public function addToCart(CartService $cart): void
    {
        $this->validate([
            'calendarId' => ['required', 'integer'],
            'bookingDate' => ['required', 'date'],
            'bookingStartAt' => ['required', 'string'],
            'bookingEndAt' => ['required', 'string'],
        ], [
            'calendarId.required' => 'يجب اختيار التقويم.',
            'bookingDate.required' => 'يجب اختيار تاريخ الحجز.',
            'bookingStartAt.required' => 'يجب اختيار وقت الحجز.',
        ]);

        $calendar = Calendar::query()->find($this->calendarId);

        if (! $calendar) {
            $this->addError('calendarId', 'التقويم المحدد غير متاح.');

            return;
        }

        $durationMinutes = max(1, (int) data_get($this->service->data, 'duration_minutes', 60));

        $slot = collect(app(CalendarSlotService::class)->availableTimeSlots(
            $calendar,
            $this->bookingDate,
            $durationMinutes,
            'slot',
            $cart->reservedBookingSlots($this->calendarId, $this->bookingDate),
        ))->first(fn (array $candidate): bool => ($candidate['start_at'] ?? '') === $this->bookingStartAt
            && ($candidate['end_at'] ?? '') === $this->bookingEndAt);

        if (! is_array($slot) || ! ($slot['available'] ?? false)) {
            $this->addError('bookingStartAt', 'الوقت المحدد غير متاح.');
            $this->loadBookingTimeSlots();

            return;
        }

        $cart->addServiceBooking($this->service, [
            'calendar_id' => $this->calendarId,
            'calendar_name' => $calendar->name,
            'booking_date' => $this->bookingDate,
            'booking_start_at' => $this->bookingStartAt,
            'booking_end_at' => $this->bookingEndAt,
            'duration_minutes' => $durationMinutes,
        ]);

        $this->addedToCart = true;
        $this->dispatch('cart-updated');
        $this->dispatch('close-modal', name: 'service-booking-modal');
        $this->resetBookingSelection();
    }

    public function render()
    {
        $categories = $this->service->taxonomiesOfType('service_category');
        $images = $this->service->serviceImageUrls();

        return tenantView('services.detail', [
            'service' => $this->service,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->service->data, 'subtitle', ''),
            'body' => (string) data_get($this->service->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->service->avatar,
            'price' => (int) data_get($this->service->data, 'price', 0),
            'durationMinutes' => (int) data_get($this->service->data, 'duration_minutes', 0),
        ])->title($this->service->title);
    }

    protected function loadCalendars(): void
    {
        $this->calendars = $this->service->calendars()
            ->where('calendars.active', true)
            ->get()
            ->map(fn (Calendar $calendar): array => [
                'id' => (int) $calendar->id,
                'name' => $calendar->name,
            ])
            ->values()
            ->all();

        if (count($this->calendars) === 1) {
            $this->calendarId = $this->calendars[0]['id'];
            $this->loadBookingAvailability();

            return;
        }

        $this->calendarId = null;
        $this->availableDates = [];
        $this->bookingDate = '';
        $this->timeSlots = [];
        $this->bookingStartAt = null;
        $this->bookingEndAt = null;
    }

    protected function loadBookingAvailability(): void
    {
        $calendarId = (int) ($this->calendarId ?? 0);

        if ($calendarId <= 0) {
            $this->availableDates = [];
            $this->bookingDate = '';
            $this->timeSlots = [];
            $this->bookingStartAt = null;
            $this->bookingEndAt = null;

            return;
        }

        $calendar = Calendar::query()->find($calendarId);

        if (! $calendar) {
            return;
        }

        $this->availableDates = app(CalendarSlotService::class)->availableDates($calendar);
        $this->bookingDate = '';
        $this->timeSlots = [];
        $this->bookingStartAt = null;
        $this->bookingEndAt = null;
    }

    protected function loadBookingTimeSlots(): void
    {
        $calendarId = (int) ($this->calendarId ?? 0);
        $bookingDate = $this->bookingDate;

        $this->bookingStartAt = null;
        $this->bookingEndAt = null;
        $this->timeSlots = [];

        if ($calendarId <= 0 || $bookingDate === '') {
            return;
        }

        $calendar = Calendar::query()->find($calendarId);

        if (! $calendar) {
            return;
        }

        $durationMinutes = max(1, (int) data_get($this->service->data, 'duration_minutes', 60));

        $this->timeSlots = app(CalendarSlotService::class)->availableTimeSlots(
            $calendar,
            $bookingDate,
            $durationMinutes,
            'slot',
            app(CartService::class)->reservedBookingSlots($calendarId, $bookingDate),
        );
    }

    protected function resetBookingSelection(): void
    {
        $this->bookingDate = '';
        $this->timeSlots = [];
        $this->bookingStartAt = null;
        $this->bookingEndAt = null;
        $this->resetValidation();
    }
}
