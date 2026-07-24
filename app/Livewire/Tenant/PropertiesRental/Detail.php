<?php

namespace App\Livewire\Tenant\PropertiesRental;

use App\Models\Calendar;
use App\Models\Content;
use App\Services\CalendarSlotService;
use App\Services\CartService;
use Carbon\Carbon;
use Livewire\Component;

class Detail extends Component
{
    public Content $unit;

    public bool $addedToCart = false;

    /** @var array<int, array{id: int, name: string}> */
    public array $calendars = [];

    public ?int $calendarId = null;

    public string $checkIn = '';

    public string $checkOut = '';

    public function mount(string $slug): void
    {
        $this->unit = Content::query()
            ->type(contentTypeModel('unit-rental'))
            ->published()
            ->where('active', true)
            ->where('slug', $slug)
            ->with(['media', 'taxonomies', 'calendars'])
            ->firstOrFail();

        $this->checkIn = now()->toDateString();
        $this->checkOut = now()->addDay()->toDateString();
        $this->loadCalendars();
    }

    public function addToCart(CartService $cart): void
    {
        $this->validate([
            'calendarId' => ['required', 'integer'],
            'checkIn' => ['required', 'date'],
            'checkOut' => ['required', 'date', 'after:checkIn'],
        ], [
            'calendarId.required' => 'لا يوجد تقويم مرتبط بهذه الوحدة.',
            'checkIn.required' => 'يرجى اختيار تاريخ الوصول.',
            'checkOut.required' => 'يرجى اختيار تاريخ المغادرة.',
            'checkOut.after' => 'تاريخ المغادرة يجب أن يكون بعد تاريخ الوصول.',
        ]);

        $calendar = Calendar::query()->find($this->calendarId);

        if (! $calendar) {
            $this->addError('calendarId', 'التقويم المحدد غير متاح.');

            return;
        }

        $checkIn = Carbon::parse($this->checkIn)->startOfDay();
        $checkOut = Carbon::parse($this->checkOut)->startOfDay();
        $nights = max($checkIn->diffInDays($checkOut), 1);
        $pricePerNight = (int) data_get($this->unit->data, 'price', 0);
        $availableDates = app(CalendarSlotService::class)->availableDates($calendar);

        for ($date = $checkIn->copy(); $date->lt($checkOut); $date->addDay()) {
            if (! in_array($date->toDateString(), $availableDates, true)) {
                $this->addError('checkIn', 'التواريخ المحددة غير متاحة للحجز.');

                return;
            }
        }

        $bookingStartAt = $checkIn->toDateTimeString();
        $bookingEndAt = $checkOut->toDateTimeString();

        if (! $cart->bookingRangeIsAvailable((int) $this->calendarId, $bookingStartAt, $bookingEndAt)) {
            $this->addError('checkIn', 'الوحدة محجوزة في هذه الفترة.');

            return;
        }

        $cart->addBooking($this->unit, [
            'calendar_id' => (int) $this->calendarId,
            'calendar_name' => $calendar->name,
            'booking_date' => $checkIn->toDateString(),
            'booking_start_at' => $bookingStartAt,
            'booking_end_at' => $bookingEndAt,
            'duration_minutes' => 0,
            'unit_price' => $pricePerNight * $nights,
            'nights' => $nights,
            'check_in' => $checkIn->toDateString(),
            'check_out' => $checkOut->toDateString(),
        ]);

        $this->addedToCart = true;
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        $categories = $this->unit->taxonomiesOfType('unit_category');
        $images = collect($this->unit->unitImages())->pluck('url')->values()->all();

        return tenantView('properties-rental.detail', [
            'unit' => $this->unit,
            'categories' => $categories,
            'subtitle' => (string) data_get($this->unit->data, 'subtitle', ''),
            'body' => (string) data_get($this->unit->data, 'body', ''),
            'images' => $images,
            'imageUrl' => $images[0] ?? $this->unit->avatar,
            'pricePerNight' => (int) data_get($this->unit->data, 'price', 0),
            'calendars' => $this->calendars,
            'checkIn' => $this->checkIn,
            'checkOut' => $this->checkOut,
        ])->title($this->unit->title);
    }

    protected function loadCalendars(): void
    {
        $this->unit->loadMissing('calendars');

        $this->calendars = $this->unit->calendars
            ->where('active', true)
            ->map(fn (Calendar $calendar): array => [
                'id' => (int) $calendar->id,
                'name' => $calendar->name,
            ])
            ->values()
            ->all();

        $this->calendarId = $this->calendars[0]['id'] ?? null;
    }
}
