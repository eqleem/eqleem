<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @return array{0: User, 1: Tenant}
 */
function createUserForUnsplashApi(): array
{
    $user = User::factory()->create([
        'uuid' => (string) Str::uuid(),
    ]);

    $tenant = Tenant::query()->create([
        'uuid' => (string) Str::uuid(),
        'name' => 'متجري',
        'handle' => 'unsplash-'.Str::lower(Str::random(6)),
        'user_id' => $user->id,
        'active' => true,
        'status' => 'active',
    ]);

    $user->update(['current_tenant_id' => $tenant->id]);

    setCurrentTenant($tenant);

    return [$user->fresh(), $tenant->fresh()];
}

test('unsplash photos endpoint requires configuration', function () {
    config(['services.unsplash.access_key' => null]);

    [$user] = createUserForUnsplashApi();

    $this->actingAs($user)
        ->getJson('/api/unsplash/photos')
        ->assertStatus(503);
});

test('owner can list popular unsplash photos', function () {
    config(['services.unsplash.access_key' => 'test-access-key']);

    Http::fake([
        'api.unsplash.com/photos*' => Http::response([
            [
                'id' => 'photo-1',
                'alt_description' => 'Mountain',
                'description' => null,
                'urls' => [
                    'regular' => 'https://images.unsplash.com/photo-1',
                    'small' => 'https://images.unsplash.com/photo-1-small',
                ],
                'user' => [
                    'name' => 'Ada',
                    'username' => 'ada',
                ],
            ],
        ], 200),
    ]);

    [$user] = createUserForUnsplashApi();

    $this->actingAs($user)
        ->getJson('/api/unsplash/photos')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', 'photo-1')
        ->assertJsonPath('data.0.author', 'Ada')
        ->assertJsonPath('data.0.thumb', 'https://images.unsplash.com/photo-1-small');
});

test('owner can search unsplash photos', function () {
    config(['services.unsplash.access_key' => 'test-access-key']);

    Http::fake([
        'api.unsplash.com/search/photos*' => Http::response([
            'results' => [
                [
                    'id' => 'photo-2',
                    'alt_description' => 'Ocean',
                    'urls' => [
                        'regular' => 'https://images.unsplash.com/photo-2',
                        'small' => 'https://images.unsplash.com/photo-2-small',
                    ],
                    'user' => [
                        'name' => 'Grace',
                        'username' => 'grace',
                    ],
                ],
            ],
        ], 200),
    ]);

    [$user] = createUserForUnsplashApi();

    $this->actingAs($user)
        ->getJson('/api/unsplash/photos?query=ocean')
        ->assertSuccessful()
        ->assertJsonPath('data.0.id', 'photo-2')
        ->assertJsonPath('data.0.author', 'Grace');
});

test('owner can select an unsplash photo and trigger download tracking', function () {
    config(['services.unsplash.access_key' => 'test-access-key']);

    Http::fake([
        'api.unsplash.com/photos/photo-3' => Http::response([
            'id' => 'photo-3',
            'alt_description' => 'Forest',
            'urls' => [
                'regular' => 'https://images.unsplash.com/photo-3',
                'small' => 'https://images.unsplash.com/photo-3-small',
            ],
            'user' => [
                'name' => 'Lin',
                'username' => 'lin',
            ],
            'links' => [
                'download_location' => 'https://api.unsplash.com/photos/photo-3/download?ixid=abc',
            ],
        ], 200),
        'api.unsplash.com/photos/photo-3/download*' => Http::response(['url' => 'https://images.unsplash.com/photo-3-dl'], 200),
    ]);

    [$user] = createUserForUnsplashApi();

    $this->actingAs($user)
        ->postJson('/api/unsplash/photos/select', ['id' => 'photo-3'])
        ->assertSuccessful()
        ->assertJsonPath('data.id', 'photo-3')
        ->assertJsonPath('data.author', 'Lin');

    Http::assertSent(function ($request): bool {
        return str_contains($request->url(), 'photos/photo-3/download');
    });
});
