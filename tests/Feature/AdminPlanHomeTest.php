<?php

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);
});

function createTenantWithUserForPlanHome(): array
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

it('renders subscription faq section on plan home page', function () {
    [$user] = createTenantWithUserForPlanHome();

    $this->actingAs($user)
        ->get(route('admin.plan.home'))
        ->assertSuccessful()
        ->assertSee('الأسئلة المتكررة')
        ->assertSee('هل الباقة المجانية مجانية فعلاً؟')
        ->assertSee('هل يمكنني إلغاء الاشتراك؟');
});
