<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('lists users with profile details and current tenant link', function () {
    $user = User::factory()->create([
        'name' => 'أحمد التجريبي',
        'email' => 'ahmad@example.com',
        'phone' => '0500000001',
        'active' => true,
    ]);

    $tenantId = DB::table('tenants')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجر أحمد',
        'handle' => 'ahmad-store',
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $user->update(['current_tenant_id' => $tenantId]);

    $this->actingAs($user);

    Livewire::test(ListUsers::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$user->fresh()])
        ->assertSee('أحمد التجريبي')
        ->assertSee('ahmad@example.com')
        ->assertSee('0500000001')
        ->assertSee('متجر أحمد');
});

it('toggles user active status from the table', function () {
    $user = User::factory()->create(['active' => true]);

    $this->actingAs($user);

    Livewire::test(ListUsers::class)
        ->assertSuccessful()
        ->call('updateTableColumnState', 'active', (string) $user->getKey(), false);

    expect($user->fresh()->active)->toBeFalse();
});

it('can view a user but cannot create edit or delete', function () {
    $user = User::factory()->create(['name' => 'مستخدم للعرض']);

    $this->actingAs($user);

    expect(UserResource::canCreate())->toBeFalse()
        ->and(UserResource::canEdit($user))->toBeFalse()
        ->and(UserResource::canDelete($user))->toBeFalse();

    Livewire::test(ViewUser::class, ['record' => $user->getRouteKey()])
        ->assertSuccessful()
        ->assertSee('مستخدم للعرض');
});
