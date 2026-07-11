<?php

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForBranchesSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'branch-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access branches settings', function () {
    $this->getJson('/api/settings/branches')->assertUnauthorized();
    $this->postJson('/api/settings/branches', [])->assertUnauthorized();
});

test('owner can list create update and delete branches', function () {
    [$user, $tenant] = createUserWithTenantForBranchesSettings();

    $this->actingAs($user)
        ->getJson('/api/settings/branches')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data',
            'meta' => ['countries', 'cities', 'weekday_labels'],
        ]);

    $create = $this->actingAs($user)
        ->postJson('/api/settings/branches', [
            'name' => 'الفرع الرئيسي',
            'country' => 'SA',
            'city' => 'الرياض',
            'address' => 'شارع التحلية',
            'postal_code' => '12345',
            'email' => 'branch@example.com',
            'phonecode' => '+966',
            'phone' => '512345678',
            'active' => true,
            'is_warehouse' => true,
            'is_pickup' => false,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'الفرع الرئيسي')
        ->assertJsonStructure(['message']);

    $branchId = $create->json('data.id');

    setCurrentTenant($tenant);
    expect(Branch::query()->count())->toBe(1);

    $this->actingAs($user)
        ->putJson('/api/settings/branches/'.$branchId, [
            'name' => 'فرع محدث',
            'country' => 'SA',
            'city' => 'جدة',
            'active' => true,
            'is_warehouse' => false,
            'is_pickup' => true,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'فرع محدث')
        ->assertJsonPath('data.city', 'جدة');

    $this->actingAs($user)
        ->deleteJson('/api/settings/branches/'.$branchId)
        ->assertSuccessful()
        ->assertJsonStructure(['message']);

    setCurrentTenant($tenant);
    expect(Branch::query()->count())->toBe(0);
});

test('branch create validates required fields', function () {
    [$user] = createUserWithTenantForBranchesSettings();

    $this->actingAs($user)
        ->postJson('/api/settings/branches', [
            'name' => '',
            'country' => 'SA',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'city']);
});

test('users without a tenant cannot access branches settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/branches')
        ->assertForbidden();
});
