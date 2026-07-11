<?php

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserWithTenantForGeneralInfoSettings(array $tenantAttributes = []): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'info-store-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
        ...$tenantAttributes,
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    return [$user->fresh(), $tenant->fresh()];
}

test('guests cannot access general info settings', function () {
    $this->getJson('/api/settings/general-info')->assertUnauthorized();
    $this->putJson('/api/settings/general-info/basic', [])->assertUnauthorized();
});

test('owner can get general info settings', function () {
    [$user] = createUserWithTenantForGeneralInfoSettings();

    $this->actingAs($user)
        ->getJson('/api/settings/general-info')
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'name',
                'logo',
                'contact',
                'social_links',
                'social_networks',
            ],
        ]);
});

test('owner can update general info basic and contact', function () {
    [$user, $tenant] = createUserWithTenantForGeneralInfoSettings();

    Storage::fake('spaces');

    $this->actingAs($user)
        ->put('/api/settings/general-info/basic', [
            'name' => 'صفحة جديدة',
            'logo' => UploadedFile::fake()->image('logo.png', 100, 100),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'صفحة جديدة')
        ->assertJsonStructure(['message']);

    expect($tenant->fresh()->name)->toBe('صفحة جديدة');

    $this->actingAs($user)
        ->putJson('/api/settings/general-info/contact', [
            'phone' => '0500000000',
            'email' => 'hello@example.com',
            'whatsapp' => '966500000000',
            'country' => 'السعودية',
            'city' => 'الرياض',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.contact.email', 'hello@example.com');
});

test('owner can add and delete social links', function () {
    [$user, $tenant] = createUserWithTenantForGeneralInfoSettings();

    setCurrentTenant($tenant);
    $before = app(TenantProfileService::class)->socialLinks($tenant)->count();

    $response = $this->actingAs($user)
        ->postJson('/api/settings/general-info/social', [
            'network' => 'twitter',
            'url' => 'https://twitter.com/eqleem',
        ])
        ->assertSuccessful();

    $links = $response->json('data.social_links');
    expect($links)->toHaveCount($before + 1);

    $id = collect($links)->firstWhere('url', 'https://twitter.com/eqleem')['id'];

    $this->actingAs($user)
        ->deleteJson('/api/settings/general-info/social/'.$id)
        ->assertSuccessful();

    setCurrentTenant($tenant);
    expect(app(TenantProfileService::class)->socialLinks($tenant->fresh()))->toHaveCount($before);
});

test('general info basic validates name', function () {
    [$user] = createUserWithTenantForGeneralInfoSettings();

    $this->actingAs($user)
        ->putJson('/api/settings/general-info/basic', [
            'name' => 'a',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['name']);
});

test('users without a tenant cannot access general info settings', function () {
    $user = User::factory()->create(['uuid' => (string) Str::uuid()]);

    $this->actingAs($user)
        ->getJson('/api/settings/general-info')
        ->assertForbidden();
});
