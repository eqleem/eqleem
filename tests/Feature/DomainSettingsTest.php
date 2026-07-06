<?php

use App\Actions\SubscribeTenantToPlan;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function seedPlansWithDomainFeature(): void
{
    (new PlanSeeder)->run();
}

function createTenantForDomainSettings(string $handle = 'domain-tenant'): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Domain Tenant',
        'handle' => $handle,
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    return [$user, $tenant];
}

it('updates the free subdomain handle', function () {
    [$user, $tenant] = createTenantForDomainSettings('old-handle');

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->set('handle', 'new-handle')
        ->call('submit')
        ->assertHasNoErrors();

    expect($tenant->fresh()->handle)->toBe('new-handle');
});

it('saves a custom domain with pending status', function () {
    [$user, $tenant] = createTenantForDomainSettings('my-shop');

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->set('customDomain', 'shop.example.com')
        ->call('submitCustomDomain')
        ->assertHasNoErrors();

    $tenant->refresh();

    expect($tenant->custom_domain)->toBe('shop.example.com')
        ->and($tenant->custom_domain_status)->toBe('pending');
});

it('normalizes custom domain input before saving', function () {
    [$user, $tenant] = createTenantForDomainSettings('my-shop');

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->set('customDomain', 'https://Shop.Example.COM/')
        ->call('submitCustomDomain')
        ->assertHasNoErrors();

    expect($tenant->fresh()->custom_domain)->toBe('shop.example.com');
});

it('shows cname target based on tenant handle', function () {
    [$user] = createTenantForDomainSettings('my-shop');

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->set('customDomain', 'shop.example.com')
        ->assertSee('my-shop.'.config('app.domain'))
        ->assertSee('CNAME');
});

it('rejects invalid custom domain format', function () {
    [$user] = createTenantForDomainSettings();

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->set('customDomain', 'not a domain')
        ->call('submitCustomDomain')
        ->assertHasErrors(['customDomain']);
});

it('rejects duplicate custom domains across tenants', function () {
    [$user, $tenant] = createTenantForDomainSettings('tenant-a');

    $otherTenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Other Tenant',
        'handle' => 'tenant-b',
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $otherTenant->update([
        'custom_domain' => 'shop.example.com',
        'custom_domain_status' => 'pending',
    ]);

    setCurrentTenant($tenant);

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->set('customDomain', 'shop.example.com')
        ->call('submitCustomDomain')
        ->assertHasErrors(['customDomain']);
});

it('clears custom domain when input is empty', function () {
    [$user, $tenant] = createTenantForDomainSettings();

    $tenant->update([
        'custom_domain' => 'shop.example.com',
        'custom_domain_status' => 'pending',
    ]);

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->set('customDomain', '')
        ->call('submitCustomDomain')
        ->assertHasNoErrors();

    $tenant->refresh();

    expect($tenant->custom_domain)->toBeNull()
        ->and($tenant->custom_domain_status)->toBeNull();
});

it('grants custom domain access to basic plan subscribers', function () {
    seedPlansWithDomainFeature();

    [$user, $tenant] = createTenantForDomainSettings('basic-shop');

    $plan = Plan::query()->where('slug', 'basic-monthly')->firstOrFail();

    SubscribeTenantToPlan::make()->handle($tenant, $plan);

    $tenant = $tenant->fresh();

    expect($tenant->hasFeature('domain'))->toBeTrue()
        ->and($tenant->missingFeature('domain'))->toBeFalse();

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->assertDontSee('ترقية الباقة');
});

it('blocks custom domain access for free plan subscribers', function () {
    seedPlansWithDomainFeature();

    [$user, $tenant] = createTenantForDomainSettings('free-shop');

    SubscribeTenantToPlan::make()->subscribeToFreePlan($tenant);

    expect($tenant->fresh()->missingFeature('domain'))->toBeTrue();

    Livewire::actingAs($user)
        ->test('admin::settings.info.domain')
        ->assertSee('ترقية الباقة');
});
