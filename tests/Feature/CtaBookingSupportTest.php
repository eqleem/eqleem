<?php

use App\Models\Booking;
use App\Models\Branch;
use App\Models\Calendar;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CtaBooking;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

it('resolves calendars from branch and calendar ids', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant',
        'handle' => 'cta-booking-unit-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $branch = Branch::query()->create([
        'tenant_id' => $tenant->id,
        'name' => Branch::localizedName('فرع'),
        'city' => 'الرياض',
        'country' => 'SA',
        'active' => true,
    ]);

    $calendarA = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'A',
        'type' => 'service-provider',
        'branch_id' => $branch->id,
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $calendarB = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'B',
        'type' => 'service-provider',
        'branch_id' => null,
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $fromBranch = CtaBooking::resolveCalendars([$branch->id], []);
    $fromCalendar = CtaBooking::resolveCalendars([], [$calendarB->id]);
    $combined = CtaBooking::resolveCalendars([$branch->id], [$calendarB->id]);

    expect($fromBranch->pluck('id')->all())->toContain($calendarA->id)
        ->and($fromCalendar->pluck('id')->all())->toBe([$calendarB->id])
        ->and($combined->pluck('id')->sort()->values()->all())->toBe(
            collect([$calendarA->id, $calendarB->id])->sort()->values()->all()
        );
});

it('returns nearest available slots sorted by start time', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant',
        'handle' => 'cta-slots-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Provider',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $slots = CtaBooking::nearestSlots(collect([$calendar]), 60, 3);

    expect($slots)->toHaveCount(3)
        ->and($slots[0]['start_at'] < $slots[1]['start_at'])->toBeTrue()
        ->and($slots[0]['calendar_id'])->toBe($calendar->id);
});

it('returns available time slots for a specific date', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant',
        'handle' => 'cta-day-slots-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Provider',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $slots = CtaBooking::slotsForDate(collect([$calendar]), '2026-07-06', 60);

    expect($slots)->not->toBeEmpty()
        ->and(collect($slots)->every(fn (array $slot): bool => $slot['date'] === '2026-07-06'))->toBeTrue()
        ->and(collect($slots)->contains(fn (array $slot): bool => ($slot['available'] ?? false)))->toBeTrue()
        ->and(CtaBooking::availableDates(collect([$calendar])))->toContain('2026-07-06', '2026-07-07');
});

it('keeps booked slots in the day list as unavailable', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Tenant',
        'handle' => 'cta-booked-slots-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Provider',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-12',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'confirmed',
    ]);

    $slots = collect(CtaBooking::slotsForDate(collect([$calendar]), '2026-07-06', 60));
    $booked = $slots->firstWhere('start', '09:00');

    expect($booked)->not->toBeNull()
        ->and($booked['available'])->toBeFalse()
        ->and($booked['unavailable_reason'])->toBe('booked');
});
