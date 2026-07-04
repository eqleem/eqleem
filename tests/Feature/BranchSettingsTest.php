<?php

use App\Models\Branch;
use App\Models\Calendar;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTenantWithUserForBranchSettings(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::create([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Tenant',
        'handle' => 'test-tenant-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);
    setCurrentTenant($tenant);

    return [$user, $tenant];
}

it('creates a branch from the settings form', function () {
    [$user, $tenant] = createTenantWithUserForBranchSettings();

    Livewire::actingAs($user)
        ->test('admin::settings.branches.branch-form')
        ->set('name', 'الفرع الرئيسي')
        ->set('country', 'SA')
        ->set('city', 'Riyadh')
        ->set('address', 'حي المروج')
        ->set('postalCode', '12345')
        ->set('email', 'branch@example.com')
        ->set('phonecode', '+966')
        ->set('phone', '512345678')
        ->set('isWarehouse', true)
        ->set('isPickup', false)
        ->call('submit')
        ->assertHasNoErrors()
        ->assertDispatched('updateBranchList');

    $branch = Branch::query()->first();

    expect($branch)->not->toBeNull()
        ->and($branch->tenant_id)->toBe($tenant->id)
        ->and($branch->display_name)->toBe('الفرع الرئيسي')
        ->and($branch->country)->toBe('SA')
        ->and($branch->city)->toBe('Riyadh')
        ->and($branch->is_warehouse)->toBeTrue()
        ->and($branch->is_pickup)->toBeFalse()
        ->and($branch->workingHours()['sunday']['enabled'])->toBeTrue()
        ->and($branch->workingHours()['friday']['enabled'])->toBeFalse();
});

it('updates and deletes a branch from the settings form', function () {
    [$user, $tenant] = createTenantWithUserForBranchSettings();

    $branch = Branch::query()->create([
        'tenant_id' => $tenant->id,
        'name' => Branch::localizedName('فرع قديم'),
        'country' => 'SA',
        'city' => 'Jeddah',
        'active' => true,
        'is_warehouse' => true,
        'is_pickup' => true,
        'order' => 1,
    ]);

    $branch->setWorkingHours(Calendar::defaultAvailabilities());
    $branch->save();

    Livewire::actingAs($user)
        ->test('admin::settings.branches.branch-form', ['branchId' => $branch->id])
        ->set('name', 'فرع محدّث')
        ->set('city', 'Riyadh')
        ->set('isPickup', false)
        ->call('submit')
        ->assertHasNoErrors();

    $branch->refresh();

    expect($branch->display_name)->toBe('فرع محدّث')
        ->and($branch->city)->toBe('Riyadh')
        ->and($branch->is_pickup)->toBeFalse();

    Livewire::actingAs($user)
        ->test('admin::settings.branches.branch-form', ['branchId' => $branch->id])
        ->call('deleteBranch')
        ->assertDispatched('updateBranchList');

    expect(Branch::query()->count())->toBe(0)
        ->and(Branch::withTrashed()->count())->toBe(1);
});

it('filters branches in the settings list', function () {
    [$user, $tenant] = createTenantWithUserForBranchSettings();

    Branch::query()->create([
        'tenant_id' => $tenant->id,
        'name' => Branch::localizedName('فرع الرياض'),
        'country' => 'SA',
        'city' => 'Riyadh',
        'order' => 1,
    ]);

    Branch::query()->create([
        'tenant_id' => $tenant->id,
        'name' => Branch::localizedName('فرع جدة'),
        'country' => 'SA',
        'city' => 'Jeddah',
        'order' => 2,
    ]);

    Livewire::actingAs($user)
        ->test('admin::settings.branches.branches')
        ->set('search', 'جدة')
        ->assertSee('فرع جدة')
        ->assertDontSee('فرع الرياض');
});

it('registers branches in settings config', function () {
    expect(config('settings.branches.slug'))->toBe('branches')
        ->and(config('settings.branches.name'))->toBe('الفروع')
        ->and(config('settings.branches.order'))->toBe(7)
        ->and(config('settings.branches.components.index'))->toBe('admin::settings.branches.branches');
});
