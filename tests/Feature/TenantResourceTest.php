<?php

use App\Filament\Resources\Tenants\Pages\ListTenants;
use App\Filament\Resources\Tenants\Pages\ViewTenant;
use App\Filament\Resources\Tenants\TenantResource;
use App\Models\Content;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTenantForResource(User $owner, string $handle = 'demo-page'): Tenant
{
    $id = DB::table('tenants')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'إقليم '.$handle,
        'handle' => $handle,
        'user_id' => $owner->id,
        'active' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return Tenant::query()->findOrFail($id);
}

it('lists tenants with owner content count and page link', function () {
    $user = User::factory()->create(['name' => 'مالك الصفحة']);
    $tenant = createTenantForResource($user, 'owner-page');

    Content::query()->withoutGlobalScopes()->create([
        'uuid' => (string) Str::uuid(),
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'type' => 'page',
        'title' => 'محتوى تجريبي',
        'slug' => 'demo-content',
        'active' => true,
        'status' => 'published',
    ]);

    $this->actingAs($user);

    Livewire::test(ListTenants::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$tenant])
        ->assertSee('إقليم owner-page')
        ->assertSee('مالك الصفحة')
        ->assertSee('owner-page')
        ->assertSee('1');
});

it('toggles tenant active status from the table', function () {
    $user = User::factory()->create();
    $tenant = createTenantForResource($user, 'toggle-page');

    $this->actingAs($user);

    Livewire::test(ListTenants::class)
        ->assertSuccessful()
        ->call('updateTableColumnState', 'active', (string) $tenant->getKey(), false);

    expect($tenant->fresh()->active)->toBeFalse();
});

it('can view a tenant but cannot create edit or delete', function () {
    $user = User::factory()->create();
    $tenant = createTenantForResource($user, 'view-page');

    $this->actingAs($user);

    expect(TenantResource::canCreate())->toBeFalse()
        ->and(TenantResource::canEdit($tenant))->toBeFalse()
        ->and(TenantResource::canDelete($tenant))->toBeFalse();

    Livewire::test(ViewTenant::class, ['record' => $tenant->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('إقليم view-page')
        ->assertSee('view-page');
});
