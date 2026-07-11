<?php

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForAnalyticsSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'analytics-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access analytics settings', function () {
    $this->getJson('/api/settings/analytics')->assertUnauthorized();
    $this->putJson('/api/settings/analytics', [])->assertUnauthorized();
});

test('owner can get analytics settings', function () {
    [$user] = createUserWithTenantForAnalyticsSettings();

    $this->actingAs($user)
        ->getJson('/api/settings/analytics')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'integrations',
                'providers',
            ],
        ]);
});

test('owner can update analytics settings', function () {
    [$user, $tenant] = createUserWithTenantForAnalyticsSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/analytics', [
            'integrations' => [
                'google_analytics' => [
                    'identifier' => 'G-ABCDEF123',
                    'active' => true,
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.integrations.google_analytics.identifier', 'G-ABCDEF123')
        ->assertJsonPath('data.integrations.google_analytics.active', true)
        ->assertJsonStructure(['message']);

    setCurrentTenant($tenant);

    $saved = Setting::forGroup('analytics')->get('google_analytics');

    expect(data_get($saved, 'settings.identifier'))->toBe('G-ABCDEF123')
        ->and((bool) data_get($saved, 'active'))->toBeTrue();
});

test('analytics update requires identifier when active', function () {
    [$user] = createUserWithTenantForAnalyticsSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/analytics', [
            'integrations' => [
                'google_analytics' => [
                    'identifier' => '',
                    'active' => true,
                ],
            ],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['integrations.google_analytics.identifier']);
});

test('users without a tenant cannot access analytics settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/analytics')
        ->assertForbidden();
});
