<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForVerificationSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'verify-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access verification settings', function () {
    $this->getJson('/api/settings/verification')->assertUnauthorized();
    $this->postJson('/api/settings/verification', [])->assertUnauthorized();
});

test('owner can get verification settings', function () {
    [$user] = createUserWithTenantForVerificationSettings();

    $this->actingAs($user)
        ->getJson('/api/settings/verification')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'identity_type',
                'identity_number',
                'country',
                'identity_file',
                'identity_file_url',
                'is_confirmed',
                'confirm_status',
                'types',
                'countries',
            ],
        ]);
});

test('owner can submit verification with identity file', function () {
    [$user, $tenant] = createUserWithTenantForVerificationSettings();

    Storage::fake('spaces');

    $this->actingAs($user)
        ->post('/api/settings/verification', [
            'identity_type' => 'individual',
            'identity_number' => '1234567890',
            'country' => 'AE',
            'file' => UploadedFile::fake()->image('id.png', 200, 200),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.identity_type', 'individual')
        ->assertJsonPath('data.identity_number', '1234567890')
        ->assertJsonPath('data.country', 'AE')
        ->assertJsonPath('data.confirm_status', 'pending')
        ->assertJsonPath('data.is_confirmed', false)
        ->assertJsonStructure(['message']);

    $tenant = $tenant->fresh();

    expect(data_get($tenant->meta, 'identity_file'))->not->toBeEmpty()
        ->and(data_get($tenant->meta, 'country'))->toBe('AE')
        ->and(data_get($tenant->meta, 'confirm_status'))->toBe('pending');
});

test('verification requires file when none exists', function () {
    [$user] = createUserWithTenantForVerificationSettings();

    $this->actingAs($user)
        ->postJson('/api/settings/verification', [
            'identity_type' => 'individual',
            'identity_number' => '1234567890',
            'country' => 'SA',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['file']);
});

test('users without a tenant cannot access verification settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/verification')
        ->assertForbidden();
});
