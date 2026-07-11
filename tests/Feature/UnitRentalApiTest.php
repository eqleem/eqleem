<?php

use App\Models\Calendar;
use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForUnitRental(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'تأجير تجريبي',
        'handle' => 'unit-rental-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access unit rental endpoints', function () {
    $this->getJson('/api/unit-rental')->assertUnauthorized();
    $this->postJson('/api/unit-rental', ['title' => 'غرفة'])->assertUnauthorized();
    $this->getJson('/api/unit-rental/categories')->assertUnauthorized();
    $this->getJson('/api/unit-rental/calendars')->assertUnauthorized();
});

test('owner can create list update and delete unit rentals', function () {
    [$user, $tenant] = createUserWithTenantForUnitRental();

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'غرفة ١٠١',
        'type' => 'rental-unit',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $create = $this->actingAs($user)
        ->postJson('/api/unit-rental', ['title' => 'غرفة مزدوجة'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'غرفة مزدوجة');

    $uuid = (string) $create->json('data.uuid');

    setCurrentTenant($tenant);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'غرف',
        'type' => 'unit_category',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->putJson("/api/unit-rental/{$uuid}", [
            'title' => 'غرفة مزدوجة محدثة',
            'subtitle' => 'إطلالة بحرية',
            'body' => '<p>تفاصيل</p>',
            'slug' => 'double-room',
            'price' => 250,
            'category_ids' => [$leaf->id],
            'calendar_ids' => [$calendar->id],
            'published' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'غرفة مزدوجة محدثة')
        ->assertJsonPath('data.subtitle', 'إطلالة بحرية')
        ->assertJsonPath('data.calendar_ids.0', (string) $calendar->id);

    setCurrentTenant($tenant);

    $unit = Content::query()->where('uuid', $uuid)->first();

    expect($unit)->not->toBeNull()
        ->and(data_get($unit->data, 'price'))->toBe(money_minor(250))
        ->and($unit->calendars()->pluck('calendars.id')->all())->toBe([$calendar->id]);

    $this->actingAs($user)
        ->deleteJson('/api/unit-rental', ['ids' => [$unit->id]])
        ->assertSuccessful();
});

test('owner can manage rental unit calendars', function () {
    [$user, $tenant] = createUserWithTenantForUnitRental();

    $create = $this->actingAs($user)
        ->postJson('/api/unit-rental/calendars', [
            'name' => 'غرفة ١٠٢',
            'from' => '2026-01-01',
            'to' => '2026-12-31',
            'availabilities' => Calendar::defaultAvailabilities(),
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'غرفة ١٠٢')
        ->assertJsonPath('data.use_branch_hours', false);

    $id = (int) $create->json('data.id');

    $this->actingAs($user)
        ->getJson('/api/unit-rental/calendars')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $id);

    $this->actingAs($user)
        ->putJson("/api/unit-rental/calendars/{$id}", [
            'name' => 'غرفة ١٠٢ محدثة',
            'use_branch_hours' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'غرفة ١٠٢ محدثة')
        ->assertJsonPath('data.use_branch_hours', true)
        ->assertJsonPath('data.hours_mode_label', 'ساعات عمل الفرع');

    $this->actingAs($user)
        ->deleteJson("/api/unit-rental/calendars/{$id}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Calendar::query()->whereKey($id)->exists())->toBeFalse();
});

test('owner can get and update unit rental settings', function () {
    [$user] = createUserWithTenantForUnitRental();

    $this->actingAs($user)
        ->getJson('/api/unit-rental/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'تأجير الوحدات');

    $this->actingAs($user)
        ->putJson('/api/unit-rental/settings', [
            'section_title' => 'وحداتنا',
            'section_description' => 'احجز الآن',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'وحداتنا');

    expect(Setting::unitRentalSettings()['section_title'])->toBe('وحداتنا');
});

test('owner can upload unit rental gallery images', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForUnitRental();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/unit-rental', ['title' => 'وحدة صور'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/unit-rental/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('unit.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');
});
