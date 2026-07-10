<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForDomainSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'my-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot update tenant handle', function () {
    $this->putJson('/api/settings/domain/handle', [
        'handle' => 'new-handle',
    ])->assertUnauthorized();
});

test('guests cannot update custom domain', function () {
    $this->putJson('/api/settings/domain/custom', [
        'custom_domain' => 'shop.example.com',
    ])->assertUnauthorized();
});

test('owner can update the free subdomain handle', function () {
    [$user, $tenant] = createUserWithTenantForDomainSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/domain/handle', [
            'handle' => 'new-shop',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.handle', 'new-shop')
        ->assertJsonStructure(['message']);

    expect($tenant->fresh()->handle)->toBe('new-shop');
});

test('handle update validates uniqueness and format', function () {
    [, $other] = createUserWithTenantForDomainSettings(['handle' => 'taken-handle']);
    [$user] = createUserWithTenantForDomainSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/domain/handle', [
            'handle' => 'taken-handle',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['handle']);

    $this->actingAs($user)
        ->putJson('/api/settings/domain/handle', [
            'handle' => 'Invalid Handle!',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['handle']);

    expect($other->fresh()->handle)->toBe('taken-handle');
});

test('owner can set a custom domain to pending', function () {
    [$user, $tenant] = createUserWithTenantForDomainSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/domain/custom', [
            'custom_domain' => 'https://Shop.Example.com/',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.custom_domain', 'shop.example.com')
        ->assertJsonPath('data.custom_domain_status', 'pending');

    expect($tenant->fresh())
        ->custom_domain->toBe('shop.example.com')
        ->custom_domain_status->toBe('pending');
});

test('owner can clear the custom domain', function () {
    [$user, $tenant] = createUserWithTenantForDomainSettings([
        'custom_domain' => 'shop.example.com',
        'custom_domain_status' => 'pending',
    ]);

    $this->actingAs($user)
        ->putJson('/api/settings/domain/custom', [
            'custom_domain' => '',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.custom_domain', null)
        ->assertJsonPath('data.custom_domain_status', null);

    expect($tenant->fresh())
        ->custom_domain->toBeNull()
        ->custom_domain_status->toBeNull();
});

test('custom domain rejects taken domains', function () {
    createUserWithTenantForDomainSettings([
        'custom_domain' => 'taken.example.com',
        'custom_domain_status' => 'pending',
    ]);
    [$user] = createUserWithTenantForDomainSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/domain/custom', [
            'custom_domain' => 'taken.example.com',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['custom_domain']);
});

test('users without a managed tenant cannot update domain settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->putJson('/api/settings/domain/handle', ['handle' => 'nope'])
        ->assertForbidden();

    $this->actingAs($user)
        ->putJson('/api/settings/domain/custom', ['custom_domain' => 'nope.example.com'])
        ->assertForbidden();
});
