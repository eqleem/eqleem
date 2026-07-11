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
function createUserWithTenantForLanguageCurrencySettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'lang-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access language currency settings', function () {
    $this->getJson('/api/settings/language-currency')->assertUnauthorized();
    $this->putJson('/api/settings/language-currency', [])->assertUnauthorized();
});

test('owner can get language currency settings', function () {
    [$user] = createUserWithTenantForLanguageCurrencySettings();

    $this->actingAs($user)
        ->getJson('/api/settings/language-currency')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'default_language',
                'default_currency',
                'available_languages',
                'available_currencies',
                'languages',
                'currencies',
            ],
        ]);
});

test('owner can update language currency settings', function () {
    [$user, $tenant] = createUserWithTenantForLanguageCurrencySettings();

    $this->actingAs($user)
        ->putJson('/api/settings/language-currency', [
            'default_language' => 'ar',
            'default_currency' => 'SAR',
            'available_languages' => ['ar', 'en'],
            'available_currencies' => ['SAR', 'USD'],
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.default_language', 'ar')
        ->assertJsonPath('data.available_languages', ['ar', 'en'])
        ->assertJsonStructure(['message']);

    setCurrentTenant($tenant);

    expect(Setting::localeCurrencySettings())
        ->default_language->toBe('ar')
        ->available_currencies->toBe(['SAR', 'USD']);
});

test('language currency update validates defaults are in available arrays', function () {
    [$user] = createUserWithTenantForLanguageCurrencySettings();

    $this->actingAs($user)
        ->putJson('/api/settings/language-currency', [
            'default_language' => 'en',
            'default_currency' => 'SAR',
            'available_languages' => ['ar'],
            'available_currencies' => ['SAR'],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['default_language']);
});

test('users without a tenant cannot access language currency settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/language-currency')
        ->assertForbidden();
});
