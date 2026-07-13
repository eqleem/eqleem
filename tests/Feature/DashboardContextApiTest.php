<?php

use App\Actions\SubscribeTenantToPlan;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createDashboardUserWithTenant(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'أحمد الأحمدي',
        'email' => 'owner@example.com',
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

test('guests cannot fetch dashboard context', function () {
    $this->getJson('/api/dashboard/context')
        ->assertUnauthorized();
});

test('authenticated owner receives dashboard context with tenant and permissions', function () {
    [$user, $tenant] = createDashboardUserWithTenant();

    $this->actingAs($user)
        ->getJson('/api/dashboard/context')
        ->assertSuccessful()
        ->assertJsonPath('data.user.id', $user->id)
        ->assertJsonPath('data.user.email', $user->email)
        ->assertJsonPath('data.tenant.id', $tenant->id)
        ->assertJsonPath('data.tenant.name', $tenant->name)
        ->assertJsonPath('data.tenant.handle', $tenant->handle)
        ->assertJsonStructure([
            'data' => [
                'tenant' => [
                    'logo',
                    'brand_mark' => ['type', 'value', 'color', 'url'],
                ],
            ],
        ])
        ->assertJsonPath('data.permissions.can_access_dashboard', true)
        ->assertJsonPath('data.permissions.can_manage_tenant', true)
        ->assertJsonPath('data.app.name', config('app.name'));
});

test('dashboard context exposes emoji brand mark for the tenant', function () {
    [$user, $tenant] = createDashboardUserWithTenant();

    app(TenantProfileService::class)->saveBrandMark($tenant, [
        'type' => 'emoji',
        'value' => '🚀',
    ]);

    $this->actingAs($user)
        ->getJson('/api/dashboard/context')
        ->assertSuccessful()
        ->assertJsonPath('data.tenant.brand_mark.type', 'emoji')
        ->assertJsonPath('data.tenant.brand_mark.value', '🚀');
});

test('inactive tenant denies dashboard access but still reports ownership', function () {
    [$user] = createDashboardUserWithTenant(['active' => false]);

    $this->actingAs($user)
        ->getJson('/api/dashboard/context')
        ->assertSuccessful()
        ->assertJsonPath('data.permissions.can_manage_tenant', true)
        ->assertJsonPath('data.permissions.can_access_dashboard', false);
});

test('foreign current tenant is not leaked and access is denied', function () {
    [$owner] = createDashboardUserWithTenant();
    $intruder = User::factory()->create(['uuid' => (string) Str::uuid()]);
    $intruder->update(['current_tenant_id' => $owner->current_tenant_id]);

    $this->actingAs($intruder->fresh())
        ->getJson('/api/dashboard/context')
        ->assertSuccessful()
        ->assertJsonPath('data.user.id', $intruder->id)
        ->assertJsonPath('data.tenant', null)
        ->assertJsonPath('data.permissions.can_access_dashboard', false)
        ->assertJsonPath('data.permissions.can_manage_tenant', false);
});

test('authenticated owner receives arabic plan name in dashboard context', function () {
    $this->seed(PlanSeeder::class);

    [$user, $tenant] = createDashboardUserWithTenant();

    $freePlan = Plan::query()->where('slug', 'free')->firstOrFail();
    SubscribeTenantToPlan::run($tenant, $freePlan);

    $this->actingAs($user)
        ->getJson('/api/dashboard/context')
        ->assertSuccessful()
        ->assertJsonPath('data.tenant.plan', 'بداية');
});

test('user without current tenant gets null tenant and no access', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/dashboard/context')
        ->assertSuccessful()
        ->assertJsonPath('data.tenant', null)
        ->assertJsonPath('data.permissions.can_access_dashboard', false)
        ->assertJsonPath('data.permissions.can_manage_tenant', false);
});
