<?php

use App\Models\Client;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createClientsApiUserWithTenant(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'أحمد الأحمدي',
        'email' => 'clients-owner-'.Str::lower(Str::random(6)).'@example.com',
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'clients-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

function createClientForClientsApi(Tenant $tenant, array $overrides = []): Client
{
    setCurrentTenant($tenant);

    $client = Client::withoutGlobalScope('tenantable')->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'محمد العتيبي',
        'email' => 'client-'.Str::lower(Str::random(6)).'@example.com',
        'phone' => '05'.random_int(10000000, 99999999),
        'tenant_id' => $tenant->id,
        'active' => true,
        'address' => 'شارع الملك فهد',
        'city' => 'الرياض',
        ...$overrides,
    ]);

    $client->tenants()->attach($tenant->id, [
        'active' => true,
        'meta' => [
            'name' => $client->name,
            'email' => $client->email,
            'phone' => $client->phone,
        ],
    ]);

    return $client;
}

test('guests cannot list clients', function () {
    $this->getJson('/api/clients')
        ->assertUnauthorized();
});

test('inactive tenant cannot list clients', function () {
    [$user] = createClientsApiUserWithTenant(['active' => false]);

    $this->actingAs($user)
        ->getJson('/api/clients')
        ->assertForbidden();
});

test('owner lists only current tenant clients with lean payload', function () {
    [$user, $tenant] = createClientsApiUserWithTenant();
    $client = createClientForClientsApi($tenant, [
        'name' => 'سارة القحطاني',
        'phone' => '0551112233',
    ]);

    [$otherUser, $otherTenant] = createClientsApiUserWithTenant();
    createClientForClientsApi($otherTenant, [
        'name' => 'عميل آخر',
        'phone' => '0559998877',
    ]);

    $this->actingAs($user)
        ->getJson('/api/clients')
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $client->id)
        ->assertJsonPath('data.0.uuid', $client->uuid)
        ->assertJsonPath('data.0.name', 'سارة القحطاني')
        ->assertJsonPath('data.0.phone', '0551112233')
        ->assertJsonPath('data.0.active', true)
        ->assertJsonMissingPath('data.0.notes')
        ->assertJsonMissingPath('data.0.meta')
        ->assertJsonMissingPath('data.0.address');
});

test('search filters clients by name email or phone', function () {
    [$user, $tenant] = createClientsApiUserWithTenant();

    createClientForClientsApi($tenant, [
        'name' => 'نورة الدوسري',
        'email' => 'noura@example.com',
        'phone' => '0544455667',
    ]);

    createClientForClientsApi($tenant, [
        'name' => 'فيصل الحربي',
        'email' => 'faisal@example.com',
        'phone' => '0500001111',
    ]);

    $this->actingAs($user)
        ->getJson('/api/clients?'.http_build_query(['search' => 'نورة']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'نورة الدوسري');

    $this->actingAs($user)
        ->getJson('/api/clients?'.http_build_query(['search' => '0500001111']))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'فيصل الحربي');
});

test('owner can create a client with livewire validation rules', function () {
    [$user, $tenant] = createClientsApiUserWithTenant();

    $response = $this->actingAs($user)
        ->postJson('/api/clients', [
            'name' => 'عبدالله الشمري',
            'phone' => '0533211122',
            'email' => 'abdullah@example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'عبدالله الشمري')
        ->assertJsonPath('data.phone', '0533211122')
        ->assertJsonPath('data.email', 'abdullah@example.com');

    $uuid = $response->json('data.uuid');

    expect(Client::withoutGlobalScope('tenantable')->where('uuid', $uuid)->exists())->toBeTrue();

    $client = Client::withoutGlobalScope('tenantable')->where('uuid', $uuid)->firstOrFail();

    expect($client->tenants()->where('tenants.id', $tenant->id)->exists())->toBeTrue();
});

test('owner can create a client with email only', function () {
    [$user] = createClientsApiUserWithTenant();

    $this->actingAs($user)
        ->postJson('/api/clients', [
            'name' => 'نورة الدوسري',
            'email' => 'noura-only@example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'نورة الدوسري')
        ->assertJsonPath('data.email', 'noura-only@example.com')
        ->assertJsonPath('data.phone', null);
});

test('create reuses existing client matched by phone or email', function () {
    [$user, $tenant] = createClientsApiUserWithTenant();
    $existing = createClientForClientsApi($tenant, [
        'name' => 'محمد العتيبي',
        'phone' => '0501234567',
        'email' => 'mohammed@example.com',
    ]);

    $this->actingAs($user)
        ->postJson('/api/clients', [
            'name' => 'محمد محدث',
            'phone' => '0501234567',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $existing->uuid);

    $this->actingAs($user)
        ->postJson('/api/clients', [
            'name' => 'محمد بالإيميل',
            'email' => 'mohammed@example.com',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $existing->uuid);

    expect(
        Client::withoutGlobalScope('tenantable')
            ->where('phone', '0501234567')
            ->orWhere('email', 'mohammed@example.com')
            ->count()
    )->toBe(1);
});

test('create client validates required fields', function () {
    [$user] = createClientsApiUserWithTenant();

    $this->actingAs($user)
        ->postJson('/api/clients', [
            'name' => '',
            'phone' => '',
            'email' => '',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name', 'phone', 'email']);

    $this->actingAs($user)
        ->postJson('/api/clients', [
            'name' => 'عميل',
            'email' => 'not-an-email',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['email']);
});

test('owner can view client detail for their tenant only', function () {
    [$user, $tenant] = createClientsApiUserWithTenant();
    $client = createClientForClientsApi($tenant, [
        'name' => 'محمد العتيبي',
        'address' => 'حي النرجس',
        'city' => 'الرياض',
    ]);

    [$otherUser, $otherTenant] = createClientsApiUserWithTenant();
    $foreign = createClientForClientsApi($otherTenant, [
        'name' => 'عميل أجنبي',
        'phone' => '0599988776',
    ]);

    $this->actingAs($user)
        ->getJson('/api/clients/'.$client->uuid)
        ->assertSuccessful()
        ->assertJsonPath('data.uuid', $client->uuid)
        ->assertJsonPath('data.name', 'محمد العتيبي')
        ->assertJsonPath('data.address', 'حي النرجس')
        ->assertJsonPath('data.city', 'الرياض');

    $this->actingAs($user)
        ->getJson('/api/clients/'.$foreign->uuid)
        ->assertNotFound();
});
