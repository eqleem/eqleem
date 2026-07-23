<?php

use App\Actions\CreateDefaultBlocks;
use App\Livewire\Tenant\Blocks\Cta;
use App\Models\Block;
use App\Models\Booking;
use App\Models\Branch;
use App\Models\Calendar;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CtaLink;
use Carbon\Carbon;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);

    view()->prependNamespace('tenant-theme', public_path('themes/default'));
    view()->prependNamespace('default-tenant-theme', public_path('themes/default'));
});

/**
 * @return array{0: User, 1: Tenant, 2: Block, 3: Calendar}
 */
function createTenantWithBookingCalendar(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'حجز موعد',
        'handle' => 'cta-booking-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);
    CreateDefaultBlocks::run($tenant);

    $branch = Branch::query()->create([
        'tenant_id' => $tenant->id,
        'name' => Branch::localizedName('الفرع الرئيسي'),
        'city' => 'الرياض',
        'country' => 'SA',
        'active' => true,
        'order' => 1,
    ]);

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'تقويم الاستقبال',
        'type' => 'service-provider',
        'branch_id' => $branch->id,
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $block = Block::findSingleton('cta');

    return [$user->fresh(), $tenant->fresh(), $block, $calendar];
}

test('cta editor exposes booking link type and booking targets', function () {
    [$user, $tenant, $block] = createTenantWithBookingCalendar();

    setCurrentTenant($tenant);

    $this->actingAs($user)
        ->getJson('/api/page/blocks/'.$block->id)
        ->assertSuccessful()
        ->assertJsonPath('data.editor.type', 'cta')
        ->assertJsonFragment(['key' => 'booking', 'label' => 'حجز موعد'])
        ->assertJsonStructure([
            'data' => [
                'editor' => [
                    'booking_targets' => [
                        'branches',
                        'calendars',
                    ],
                ],
            ],
        ]);

    expect(CtaLink::allowedBlockLinkTypeKeys())->toContain('booking');
});

test('owner can create a booking cta link with calendars and duration', function () {
    [$user, $tenant, $block, $calendar] = createTenantWithBookingCalendar();

    setCurrentTenant($tenant);

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'booking',
            'label' => 'احجز موعداً',
            'calendar_ids' => [$calendar->id],
            'branch_ids' => [],
            'allow_client_choice' => true,
            'duration_minutes' => 45,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.label', 'احجز موعداً')
        ->assertJsonPath('data.data.link_type', 'booking')
        ->assertJsonPath('data.data.duration_minutes', 45)
        ->assertJsonPath('data.data.allow_client_choice', true)
        ->assertJsonPath('data.data.calendar_ids.0', $calendar->id);
});

test('booking cta link requires at least one branch or calendar', function () {
    [$user, $tenant, $block] = createTenantWithBookingCalendar();

    setCurrentTenant($tenant);

    $this->actingAs($user)
        ->postJson('/api/page/blocks/'.$block->id.'/links', [
            'link_type' => 'booking',
            'label' => 'احجز موعداً',
            'calendar_ids' => [],
            'branch_ids' => [],
            'duration_minutes' => 30,
        ])
        ->assertUnprocessable();
});

test('tenant can submit a cta booking after choosing date then time slot', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    [, $tenant, $block, $calendar] = createTenantWithBookingCalendar();

    setCurrentTenant($tenant);

    $link = Content::query()->create([
        'tenant_id' => $tenant->id,
        'block_id' => $block->id,
        'type' => 'cta-link',
        'title' => 'احجز موعداً',
        'slug' => 'cta-booking-'.Str::lower(Str::random(6)),
        'data' => [
            'link_type' => 'booking',
            'label' => 'احجز موعداً',
            'branch_ids' => [],
            'calendar_ids' => [$calendar->id],
            'allow_client_choice' => false,
            'duration_minutes' => 60,
        ],
        'sort_order' => 1,
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    $component = Livewire::test('tenant.bookings.cta-submit', [
        'linkId' => $link->id,
        'blockId' => $block->id,
        'branchIds' => [],
        'calendarIds' => [$calendar->id],
        'allowClientChoice' => false,
        'durationMinutes' => 60,
    ])
        ->assertSuccessful()
        ->assertSet('timeSlots', [])
        ->assertSee('اليوم', false)
        ->assertSee('غداً', false)
        ->assertSee('بعد غد', false)
        ->assertSee('تاريخ محدد', false)
        ->call('selectDatePreset', 'today')
        ->assertSet('selectedDate', '2026-07-06')
        ->assertSet('timeSlots', fn ($slots) => count($slots) > 0);

    $slot = $component->get('timeSlots');
    $availableSlot = collect($slot)->first(fn (array $candidate): bool => ($candidate['available'] ?? false));

    expect($availableSlot)->not->toBeNull();

    $component
        ->set('guestName', 'أحمد')
        ->set('guestPhone', '0500000000')
        ->call('selectSlot', $availableSlot['start_at'], $availableSlot['end_at'], $availableSlot['calendar_id'])
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('submitted', true);

    $booking = Booking::query()->latest('id')->first();

    expect($booking)->not->toBeNull()
        ->and($booking->calendar_id)->toBe($calendar->id)
        ->and(data_get($booking->data, 'guest_name'))->toBe('أحمد')
        ->and(data_get($booking->data, 'guest_phone'))->toBe('0500000000')
        ->and(data_get($booking->data, 'source'))->toBe('cta_booking')
        ->and($booking->start_at?->toDateTimeString())->toBe($availableSlot['start_at']);
});

test('cta booking keeps booked slots visible but disabled', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    [, $tenant, $block, $calendar] = createTenantWithBookingCalendar();

    setCurrentTenant($tenant);

    Booking::query()->create([
        'tenant_id' => $tenant->id,
        'calendar_id' => $calendar->id,
        'start_at' => '2026-07-06 09:00:00',
        'end_at' => '2026-07-06 10:00:00',
        'status' => 'confirmed',
    ]);

    $component = Livewire::test('tenant.bookings.cta-submit', [
        'linkId' => 1,
        'blockId' => $block->id,
        'branchIds' => [],
        'calendarIds' => [$calendar->id],
        'allowClientChoice' => false,
        'durationMinutes' => 60,
    ])
        ->call('selectDatePreset', 'today')
        ->assertSet('selectedDate', '2026-07-06');

    $slots = collect($component->get('timeSlots'));
    $booked = $slots->first(fn (array $slot): bool => ($slot['start'] ?? '') === '09:00');
    $open = $slots->first(fn (array $slot): bool => ($slot['start'] ?? '') === '10:00');

    expect($booked)->not->toBeNull()
        ->and($booked['available'])->toBeFalse()
        ->and($booked['unavailable_reason'])->toBe('booked')
        ->and($open)->not->toBeNull()
        ->and($open['available'])->toBeTrue();

    $component->assertSee('محجوز', false);
});

test('cta booking requires phone or email with name', function () {
    Carbon::setTestNow('2026-07-06 08:00:00');

    [, $tenant, $block, $calendar] = createTenantWithBookingCalendar();

    setCurrentTenant($tenant);

    $component = Livewire::test('tenant.bookings.cta-submit', [
        'linkId' => 1,
        'blockId' => $block->id,
        'branchIds' => [],
        'calendarIds' => [$calendar->id],
        'allowClientChoice' => false,
        'durationMinutes' => 60,
    ])
        ->call('selectDatePreset', 'today');

    $slot = collect($component->get('timeSlots'))->first(
        fn (array $candidate): bool => ($candidate['available'] ?? false)
    );

    $component
        ->set('guestName', 'أحمد')
        ->call('selectSlot', $slot['start_at'], $slot['end_at'], $slot['calendar_id'])
        ->call('submit')
        ->assertHasErrors(['guestPhone', 'guestEmail']);
});

test('cta block renders booking button for booking links', function () {
    [, $tenant, $block, $calendar] = createTenantWithBookingCalendar();

    setCurrentTenant($tenant);

    Content::query()
        ->where('block_id', $block->id)
        ->type('cta-link')
        ->delete();

    Content::query()->create([
        'tenant_id' => $tenant->id,
        'block_id' => $block->id,
        'type' => 'cta-link',
        'title' => 'احجز موعداً',
        'slug' => 'cta-booking-'.Str::lower(Str::random(6)),
        'data' => [
            'link_type' => 'booking',
            'label' => 'احجز موعداً',
            'calendar_ids' => [$calendar->id],
            'branch_ids' => [],
            'allow_client_choice' => true,
            'duration_minutes' => 30,
        ],
        'sort_order' => 1,
        'active' => true,
        'status' => 'published',
        'published_at' => now(),
    ]);

    Livewire::test(Cta::class)
        ->assertSuccessful()
        ->assertSee('احجز موعداً', false)
        ->assertSee('cta-booking-', false);
});
