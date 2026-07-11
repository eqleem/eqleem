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
function createUserWithTenantForShippingOptionsSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'ship-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access shipping options settings', function () {
    $this->getJson('/api/settings/shipping-options')->assertUnauthorized();
    $this->postJson('/api/settings/shipping-options/custom', [])->assertUnauthorized();
});

test('owner can list and update shipping methods', function () {
    [$user, $tenant] = createUserWithTenantForShippingOptionsSettings();

    $this->actingAs($user)
        ->getJson('/api/settings/shipping-options')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'methods',
                'custom_options',
            ],
        ]);

    $this->actingAs($user)
        ->putJson('/api/settings/shipping-options/methods/eqleem-ship/active', [
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.active', true);

    $this->actingAs($user)
        ->putJson('/api/settings/shipping-options/methods/eqleem-ship', [
            'label' => 'شحن سريع',
            'domestic_price' => 25,
            'gulf_price' => 40,
            'international_price' => 80,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.settings.label', 'شحن سريع')
        ->assertJsonPath('data.settings.domestic_price', 25)
        ->assertJsonPath('data.active', true);

    setCurrentTenant($tenant);
    expect(data_get(Setting::shippingMethod('eqleem-ship'), 'label'))->toBe('شحن سريع');
});

test('owner can create update toggle and delete custom shipping options', function () {
    [$user, $tenant] = createUserWithTenantForShippingOptionsSettings();

    $create = $this->actingAs($user)
        ->postJson('/api/settings/shipping-options/custom', [
            'name' => 'مندوب الرياض',
            'price' => 15,
            'country' => '*',
            'all_cities' => true,
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'مندوب الرياض')
        ->assertJsonStructure(['message']);

    $id = $create->json('data.id');

    $this->actingAs($user)
        ->putJson('/api/settings/shipping-options/custom/'.$id, [
            'name' => 'مندوب محدث',
            'price' => 20,
            'country' => '*',
            'all_cities' => true,
            'active' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'مندوب محدث')
        ->assertJsonPath('data.price', 20);

    $this->actingAs($user)
        ->putJson('/api/settings/shipping-options/custom/'.$id.'/active', [
            'active' => false,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.active', false);

    $this->actingAs($user)
        ->deleteJson('/api/settings/shipping-options/custom/'.$id)
        ->assertSuccessful()
        ->assertJsonStructure(['message']);

    setCurrentTenant($tenant);
    expect(Setting::customShippingOptions())->toBe([]);
});

test('custom shipping option validates name and price', function () {
    [$user] = createUserWithTenantForShippingOptionsSettings();

    $this->actingAs($user)
        ->postJson('/api/settings/shipping-options/custom', [
            'name' => '',
            'price' => -1,
            'country' => '*',
            'all_cities' => true,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'price']);
});

test('users without a tenant cannot access shipping options settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/shipping-options')
        ->assertForbidden();
});
