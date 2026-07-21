<?php

use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PlanSeeder;
use Database\Seeders\ThemeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithOwnedTenant(string $handle = 'main-page'): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'أحمد',
        'email' => 'owner@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحتي الرئيسية',
        'handle' => $handle,
        'email' => $user->email,
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

beforeEach(function () {
    $this->seed(ThemeSeeder::class);
    $this->seed(PlanSeeder::class);

    Http::fake([
        '*' => Http::response(['data' => ['id' => 'traffic-tenants']], 201),
    ]);
});

it('lists tenants owned by the authenticated user', function () {
    [$user, $tenant] = createUserWithOwnedTenant();

    $other = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحة ثانية',
        'handle' => 'second-page',
        'email' => $user->email,
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $foreignUser = User::factory()->create(['email' => 'other@example.com']);
    Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحة غيري',
        'handle' => 'foreign-page',
        'email' => $foreignUser->email,
        'user_id' => $foreignUser->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->getJson('/api/tenants')
        ->assertSuccessful()
        ->assertJsonCount(2, 'data')
        ->assertJsonFragment(['id' => $tenant->id, 'handle' => 'main-page'])
        ->assertJsonFragment(['id' => $other->id, 'handle' => 'second-page'])
        ->assertJsonMissing(['handle' => 'foreign-page']);
});

it('creates a new tenant from a name and switches current_tenant_id', function () {
    [$user, $current] = createUserWithOwnedTenant();

    $response = $this->actingAs($user)
        ->postJson('/api/tenants', [
            'name' => 'متجر جديد',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'متجر جديد');

    $newId = (int) $response->json('data.id');

    expect($newId)->toBeGreaterThan(0)
        ->and((int) $user->fresh()->current_tenant_id)->toBe($newId)
        ->and($newId)->not->toBe($current->id)
        ->and(Tenant::query()->where('user_id', $user->id)->count())->toBe(2);
});

it('switches current_tenant_id to an owned tenant', function () {
    [$user, $current] = createUserWithOwnedTenant();

    $other = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحة أخرى',
        'handle' => 'other-page',
        'email' => $user->email,
        'user_id' => $user->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->postJson("/api/tenants/{$other->id}/switch")
        ->assertSuccessful()
        ->assertJsonPath('data.id', $other->id);

    expect((int) $user->fresh()->current_tenant_id)->toBe($other->id)
        ->and((int) $user->fresh()->current_tenant_id)->not->toBe($current->id);
});

it('forbids switching to a tenant the user does not own', function () {
    [$user] = createUserWithOwnedTenant();

    $foreignUser = User::factory()->create(['email' => 'foreign@example.com']);
    $foreign = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'صفحة أجنبية',
        'handle' => 'foreign-switch',
        'email' => $foreignUser->email,
        'user_id' => $foreignUser->id,
        'theme_id' => 1,
        'active' => true,
        'status' => 'active',
    ]);

    $this->actingAs($user)
        ->postJson("/api/tenants/{$foreign->id}/switch")
        ->assertForbidden();

    expect((int) $user->fresh()->current_tenant_id)->not->toBe($foreign->id);
});

it('validates tenant name when creating', function () {
    [$user] = createUserWithOwnedTenant();

    $this->actingAs($user)
        ->postJson('/api/tenants', [
            'name' => 'أ',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});
