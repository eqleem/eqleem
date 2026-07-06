<?php

use App\Models\Calendar;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use App\Support\ContentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

/**
 * @return array{user: User, tenant: Tenant, service: Content}
 */
function createServiceDetailAdminContext(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Service Admin Tenant',
        'handle' => 'service-admin-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    setCurrentTenant($tenant);

    $service = Content::withoutGlobalScope('tenant')->create([
        'tenant_id' => $tenant->id,
        'type' => contentTypeModel('services'),
        'title' => 'خدمة تصوير',
        'slug' => 'photo-service',
        'status' => 'draft',
        'active' => true,
        'data' => [
            'price' => 15000,
            'duration_minutes' => 60,
        ],
    ]);

    return compact('user', 'tenant', 'service');
}

function serviceContentTypeConfig(): array
{
    return ContentType::fromConfig('services', config('content-types.services'))->toArray();
}

it('lists only service provider calendars in service detail', function () {
    ['user' => $user, 'service' => $service] = createServiceDetailAdminContext();

    $provider = Calendar::query()->create([
        'tenant_id' => currentTenantId(),
        'name' => 'سمية',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unitCalendar = Calendar::query()->create([
        'tenant_id' => currentTenantId(),
        'name' => 'غرفة 101',
        'type' => 'rental-unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    Livewire::actingAs($user)
        ->test('admin::page.content.services.detail', [
            'contentType' => serviceContentTypeConfig(),
            'itemId' => $service->uuid,
        ])
        ->assertSee('سمية')
        ->assertDontSee('غرفة 101');
});

it('rejects non service provider calendars during validation', function () {
    ['user' => $user, 'service' => $service] = createServiceDetailAdminContext();

    $provider = Calendar::query()->create([
        'tenant_id' => currentTenantId(),
        'name' => 'سمية',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unitCalendar = Calendar::query()->create([
        'tenant_id' => currentTenantId(),
        'name' => 'غرفة 101',
        'type' => 'rental-unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    Livewire::actingAs($user)
        ->test('admin::page.content.services.detail', [
            'contentType' => serviceContentTypeConfig(),
            'itemId' => $service->uuid,
        ])
        ->set('calendarIds', [(string) $provider->id, (string) $unitCalendar->id])
        ->call('save')
        ->assertHasErrors(['calendarIds.1']);
});

it('removes previously linked non provider calendars when saving', function () {
    ['user' => $user, 'service' => $service] = createServiceDetailAdminContext();

    $provider = Calendar::query()->create([
        'tenant_id' => currentTenantId(),
        'name' => 'سمية',
        'type' => 'service-provider',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $unitCalendar = Calendar::query()->create([
        'tenant_id' => currentTenantId(),
        'name' => 'غرفة 101',
        'type' => 'rental-unit',
        'from' => '2026-07-06',
        'to' => '2026-07-20',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $service->calendars()->attach($unitCalendar->id);

    Livewire::actingAs($user)
        ->test('admin::page.content.services.detail', [
            'contentType' => serviceContentTypeConfig(),
            'itemId' => $service->uuid,
        ])
        ->set('calendarIds', [(string) $provider->id])
        ->call('save')
        ->assertHasNoErrors();

    $linkedCalendarIds = $service->fresh()->calendars()->pluck('calendars.id')->all();

    expect($linkedCalendarIds)->toBe([$provider->id]);
});
