<?php

use App\Models\Booking;
use App\Models\Calendar;
use App\Services\CalendarSlotService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns available dates based on calendar weekday availability', function () {
    Carbon::setTestNow('2026-07-06 10:00:00'); // Monday

    $calendar = Calendar::query()->create([
        'name' => 'Provider A',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => [
            'monday' => ['enabled' => true, 'start' => '09:00', 'end' => '17:00'],
            'tuesday' => ['enabled' => true, 'start' => '09:00', 'end' => '17:00'],
            'wednesday' => ['enabled' => false, 'start' => '09:00', 'end' => '17:00'],
        ],
        'off_dates' => ['2026-07-08'],
    ]);

    $dates = app(CalendarSlotService::class)->availableDates($calendar);

    expect($dates)->toContain('2026-07-06', '2026-07-07')
        ->and($dates)->not->toContain('2026-07-08', '2026-07-09');
});

it('returns all time slots and marks booked ones as unavailable', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    $calendar = Calendar::query()->create([
        'name' => 'Provider A',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    Booking::query()->create([
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 10:00:00',
        'end_at' => '2026-07-06 11:00:00',
        'status' => 'confirmed',
    ]);

    $slots = app(CalendarSlotService::class)->availableTimeSlots(
        $calendar,
        '2026-07-06',
        60,
        'slot',
    );

    $bookedSlot = collect($slots)->firstWhere('start', '10:00');
    $availableSlot = collect($slots)->firstWhere('start', '09:00');

    expect($bookedSlot)->not->toBeNull()
        ->and($bookedSlot['available'])->toBeFalse()
        ->and($bookedSlot['unavailable_reason'])->toBe('booked')
        ->and($availableSlot)->not->toBeNull()
        ->and($availableSlot['available'])->toBeTrue();
});

it('marks reserved slots as unavailable when not yet persisted', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    $calendar = Calendar::query()->create([
        'name' => 'Provider A',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $slots = app(CalendarSlotService::class)->availableTimeSlots(
        $calendar,
        '2026-07-06',
        60,
        'slot',
        [
            [
                'start_at' => '2026-07-06 09:00:00',
                'end_at' => '2026-07-06 10:00:00',
            ],
        ],
    );

    $reservedSlot = collect($slots)->firstWhere('start', '09:00');
    $availableSlot = collect($slots)->firstWhere('start', '10:00');

    expect($reservedSlot)->not->toBeNull()
        ->and($reservedSlot['available'])->toBeFalse()
        ->and($reservedSlot['unavailable_reason'])->toBe('booked')
        ->and($availableSlot)->not->toBeNull()
        ->and($availableSlot['available'])->toBeTrue();
});

it('returns a day slot for rental units and marks it unavailable when booked', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    $calendar = Calendar::query()->create([
        'name' => 'Room 101',
        'type' => 'rental-unit',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => [
            'monday' => ['enabled' => true, 'start' => '14:00', 'end' => '18:00'],
        ],
    ]);

    Booking::query()->create([
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 14:00:00',
        'end_at' => '2026-07-06 18:00:00',
        'status' => 'confirmed',
    ]);

    $slots = app(CalendarSlotService::class)->availableTimeSlots(
        $calendar,
        '2026-07-06',
        60,
        'day',
    );

    expect($slots)->toHaveCount(1)
        ->and($slots[0]['start'])->toBe('14:00')
        ->and($slots[0]['end'])->toBe('18:00')
        ->and($slots[0]['available'])->toBeFalse()
        ->and($slots[0]['unavailable_reason'])->toBe('booked');
});

it('returns an available day slot for rental units when window is free', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    $calendar = Calendar::query()->create([
        'name' => 'Room 101',
        'type' => 'rental-unit',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => [
            'monday' => ['enabled' => true, 'start' => '14:00', 'end' => '18:00'],
        ],
    ]);

    $slots = app(CalendarSlotService::class)->availableTimeSlots(
        $calendar,
        '2026-07-06',
        60,
        'day',
    );

    expect($slots)->toHaveCount(1)
        ->and($slots[0]['start'])->toBe('14:00')
        ->and($slots[0]['end'])->toBe('18:00')
        ->and($slots[0]['available'])->toBeTrue();
});
