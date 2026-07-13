<?php

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForGeneralInfoSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'info-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access general info settings', function () {
    $this->getJson('/api/settings/general-info')->assertUnauthorized();
    $this->putJson('/api/settings/general-info/basic', [])->assertUnauthorized();
});

test('owner can get general info settings', function () {
    [$user] = createUserWithTenantForGeneralInfoSettings();

    $this->actingAs($user)
        ->getJson('/api/settings/general-info')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'name',
                'logo',
                'brand_mark' => ['type', 'value', 'color', 'url'],
                'contact',
                'social_links',
                'social_networks',
            ],
        ]);
});

test('owner can update general info basic and contact', function () {
    [$user, $tenant] = createUserWithTenantForGeneralInfoSettings();

    Storage::fake('spaces');

    $this->actingAs($user)
        ->post('/api/settings/general-info/basic', [
            'name' => 'صفحة جديدة',
            'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'صفحة جديدة')
        ->assertJsonPath('data.brand_mark.type', 'image')
        ->assertJsonStructure(['message', 'data' => ['logo', 'brand_mark']]);

    expect($tenant->fresh()->name)->toBe('صفحة جديدة')
        ->and((bool) data_get($tenant->fresh()->meta, 'logo_saved'))->toBeTrue();

    $this->actingAs($user)
        ->putJson('/api/settings/general-info/contact', [
            'phone' => '0500000000',
            'email' => 'hello@example.com',
            'whatsapp' => '966500000000',
            'country' => 'السعودية',
            'city' => 'الرياض',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.contact.email', 'hello@example.com');
});

test('owner can save an emoji brand mark via general info basic', function () {
    [$user, $tenant] = createUserWithTenantForGeneralInfoSettings();

    $this->actingAs($user)
        ->postJson('/api/settings/general-info/basic', [
            'name' => 'صفحة بالإيموجي',
            'brand_mark_type' => 'emoji',
            'brand_mark_value' => '🚀',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.brand_mark.type', 'emoji')
        ->assertJsonPath('data.brand_mark.value', '🚀');

    expect(data_get($tenant->fresh()->meta, 'brand_mark.type'))->toBe('emoji')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark.value'))->toBe('🚀');
});

test('owner can save an icon brand mark via general info basic', function () {
    [$user, $tenant] = createUserWithTenantForGeneralInfoSettings();

    $this->actingAs($user)
        ->postJson('/api/settings/general-info/basic', [
            'name' => 'صفحة بالأيقونة',
            'brand_mark_type' => 'icon',
            'brand_mark_value' => 'tabler:chart-line',
            'brand_mark_color' => '#DC2626',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.brand_mark.type', 'icon')
        ->assertJsonPath('data.brand_mark.value', 'tabler:chart-line')
        ->assertJsonPath('data.brand_mark.color', '#dc2626');

    expect(data_get($tenant->fresh()->meta, 'brand_mark.type'))->toBe('icon')
        ->and(data_get($tenant->fresh()->meta, 'brand_mark.value'))->toBe('tabler:chart-line');
});

test('owner can remove brand mark via general info basic', function () {
    [$user, $tenant] = createUserWithTenantForGeneralInfoSettings();

    app(TenantProfileService::class)->saveBrandMark($tenant, [
        'type' => 'emoji',
        'value' => '🔥',
    ]);

    $this->actingAs($user)
        ->postJson('/api/settings/general-info/basic', [
            'name' => 'بدون شعار',
            'brand_mark_type' => 'none',
            'remove_logo' => true,
        ])
        ->assertSuccessful();

    expect(app(TenantProfileService::class)->hasLogo($tenant->fresh()))->toBeFalse();
});

test('owner can add and delete social links', function () {
    [$user, $tenant] = createUserWithTenantForGeneralInfoSettings();

    setCurrentTenant($tenant);
    $before = app(TenantProfileService::class)->socialLinks($tenant)->count();

    $response = $this->actingAs($user)
        ->postJson('/api/settings/general-info/social', [
            'network' => 'twitter',
            'url' => 'https://twitter.com/eqleem',
        ])
        ->assertSuccessful();

    $links = $response->json('data.social_links');
    expect($links)->toHaveCount($before + 1);

    $id = collect($links)->firstWhere('url', 'https://twitter.com/eqleem')['id'];

    $this->actingAs($user)
        ->deleteJson('/api/settings/general-info/social/'.$id)
        ->assertSuccessful();

    setCurrentTenant($tenant);
    expect(app(TenantProfileService::class)->socialLinks($tenant->fresh()))->toHaveCount($before);
});

test('general info basic validates name', function () {
    [$user] = createUserWithTenantForGeneralInfoSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/general-info/basic', [
            'name' => 'a',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('users without a tenant cannot access general info settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/general-info')
        ->assertForbidden();
});
