<?php

use App\Models\Calendar;
use App\Models\Content;
use App\Models\Setting;
use App\Models\Taxonomy;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Money;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForServices(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'خدمات تجريبية',
        'handle' => 'services-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access services endpoints', function () {
    $this->getJson('/api/services')->assertUnauthorized();
    $this->postJson('/api/services', ['title' => 'خدمة'])->assertUnauthorized();
    $this->getJson('/api/services/categories')->assertUnauthorized();
    $this->getJson('/api/services/calendars')->assertUnauthorized();
});

test('owner can create list update and delete services', function () {
    [$user, $tenant] = createUserWithTenantForServices();

    $calendar = Calendar::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'سمية',
        'type' => 'service-provider',
        'active' => true,
        'availabilities' => Calendar::defaultAvailabilities(),
    ]);

    $create = $this->actingAs($user)
        ->postJson('/api/services', ['title' => 'استشارة'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'استشارة')
        ->assertJsonPath('data.duration_minutes', '60')
        ->assertJsonPath('data.currency_symbol', Money::SAR_SYMBOL);

    $uuid = (string) $create->json('data.uuid');

    setCurrentTenant($tenant);

    $leaf = Taxonomy::query()->create([
        'tenant_id' => $tenant->id,
        'name' => 'استشارات',
        'type' => 'service_category',
        'sort_order' => 0,
    ]);

    $this->actingAs($user)
        ->putJson("/api/services/{$uuid}", [
            'title' => 'استشارة محدثة',
            'subtitle' => 'جلسة ساعة',
            'body' => '<p>تفاصيل</p>',
            'slug' => 'consultation',
            'price' => 150,
            'duration_minutes' => 60,
            'category_ids' => [$leaf->id],
            'calendar_ids' => [$calendar->id],
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'استشارة محدثة')
        ->assertJsonPath('data.subtitle', 'جلسة ساعة')
        ->assertJsonPath('data.duration_minutes', '60')
        ->assertJsonPath('data.calendar_ids.0', (string) $calendar->id)
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true);

    setCurrentTenant($tenant);

    $service = Content::query()->where('uuid', $uuid)->first();

    expect($service)->not->toBeNull()
        ->and(data_get($service->data, 'duration_minutes'))->toBe(60)
        ->and($service->calendars()->pluck('calendars.id')->all())->toBe([$calendar->id]);

    $this->actingAs($user)
        ->deleteJson('/api/services', ['ids' => [$service->id]])
        ->assertSuccessful();
});

test('owner can manage service calendars', function () {
    [$user, $tenant] = createUserWithTenantForServices();

    $create = $this->actingAs($user)
        ->postJson('/api/services/calendars', [
            'name' => 'أحمد',
            'from' => '2026-01-01',
            'to' => '2026-12-31',
            'availabilities' => Calendar::defaultAvailabilities(),
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'أحمد');

    $id = (int) $create->json('data.id');

    $this->actingAs($user)
        ->getJson('/api/services/calendars')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', $id);

    $this->actingAs($user)
        ->putJson("/api/services/calendars/{$id}", [
            'name' => 'أحمد محدث',
            'availabilities' => Calendar::defaultAvailabilities(),
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'أحمد محدث');

    $this->actingAs($user)
        ->deleteJson("/api/services/calendars/{$id}")
        ->assertSuccessful();

    setCurrentTenant($tenant);

    expect(Calendar::query()->whereKey($id)->exists())->toBeFalse();
});

test('owner can get and update service settings', function () {
    [$user] = createUserWithTenantForServices();

    $this->actingAs($user)
        ->getJson('/api/services/settings')
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'الخدمات');

    $this->actingAs($user)
        ->putJson('/api/services/settings', [
            'section_title' => 'خدماتنا',
            'section_description' => 'احجز الآن',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.section_title', 'خدماتنا');

    expect(Setting::serviceSettings()['section_title'])->toBe('خدماتنا');
});

test('owner can upload service gallery images', function () {
    Storage::fake(config('media-library.disk_name'));

    [$user] = createUserWithTenantForServices();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/services', ['title' => 'خدمة صور'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->post("/api/services/{$uuid}/images", [
            'file' => UploadedFile::fake()->image('service.jpg'),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.images');
});

test('service detail active state stays in sync after table toggle and detail update', function () {
    [$user] = createUserWithTenantForServices();

    $uuid = (string) $this->actingAs($user)
        ->postJson('/api/services', ['title' => 'خدمة مزامنة'])
        ->json('data.uuid');

    $this->actingAs($user)
        ->putJson("/api/services/{$uuid}/active", ['active' => false])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false);

    $this->actingAs($user)
        ->getJson("/api/services/{$uuid}")
        ->assertSuccessful()
        ->assertJsonPath('data.active', false)
        ->assertJsonPath('data.published', false);

    $this->actingAs($user)
        ->putJson("/api/services/{$uuid}", [
            'title' => 'خدمة مزامنة',
            'slug' => 'sync-service',
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true)
        ->assertJsonPath('data.published', true);

    $this->actingAs($user)
        ->getJson('/api/services')
        ->assertSuccessful()
        ->assertJsonPath('data.0.active', true);
});
